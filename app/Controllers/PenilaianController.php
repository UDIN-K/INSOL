<?php

namespace App\Controllers;

use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;
use App\Models\PenilaianModel;
use App\Services\SAWService;

class PenilaianController extends BaseController
{
    public function getIndex(): string
    {
        $mahasiswaModel = new MahasiswaModel();
        $penilaianModel = new PenilaianModel();
        $kriteriaModel = new KriteriaModel();

        // Get all mahasiswa
        $mahasiswa = $mahasiswaModel->orderBy('semester', 'ASC')->orderBy('nim', 'ASC')->findAll();

        // Add penilaian status to each mahasiswa
        $jumlahKriteria = $kriteriaModel->countAllResults();
        foreach ($mahasiswa as &$item) {
            $count = $penilaianModel
                ->where('mahasiswa_id', $item['id'])
                ->where('penilaian_ke', 1)
                ->countAllResults();
            $item['penilaian_lengkap'] = $jumlahKriteria > 0 && $count === $jumlahKriteria;
        }

        return view('penilaian/index', ['mahasiswa' => $mahasiswa, 'jumlahKriteria' => $jumlahKriteria]);
    }

    public function getInput(int $id): string
    {
        $mahasiswa = (new MahasiswaModel())->find($id);
        $kriteria = (new KriteriaModel())->orderBy('kode', 'ASC')->findAll();
        $penilaian = (new PenilaianModel())->where('mahasiswa_id', $id)->findAll();

        $nilaiByKriteria = [];
        foreach ($penilaian as $item) {
            $nilaiByKriteria[(int) $item['kriteria_id']] = (float) $item['nilai'];
        }

        return view('penilaian/form', compact('mahasiswa', 'kriteria', 'nilaiByKriteria'));
    }

    public function postSave(int $id)
    {
        $kriteria = (new KriteriaModel())->findAll();
        $nilaiInput = (array) $this->request->getPost('nilai');
        $model = new PenilaianModel();

        foreach ($kriteria as $item) {
            $kriteriaId = (int) $item['id'];
            $nilai = (float) ($nilaiInput[$kriteriaId] ?? 0);

            $existing = $model->where('mahasiswa_id', $id)->where('kriteria_id', $kriteriaId)->first();
            if ($existing) {
                $model->update($existing['id'], ['nilai' => $nilai]);
            } else {
                $model->insert(['mahasiswa_id' => $id, 'kriteria_id' => $kriteriaId, 'nilai' => $nilai]);
            }
        }

        return redirect()->to('/penilaian')->with('success', 'Penilaian disimpan.');
    }

    /**
     * Cek/preview penilaian sebelum menghitung SAW
     * Menampilkan nilai mahasiswa untuk setiap kriteria (di-calculate real-time)
     */
    public function cekPenilaian()
    {
        $selectedMahasiswa = $this->request->getPost('mahasiswa') ?? [];

        if (empty($selectedMahasiswa)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mahasiswa untuk dilanjutkan.');
        }

        $mahasiswaModel = new MahasiswaModel();
        $kriteriaModel = new KriteriaModel();
        $detailKriteriaModel = new \App\Models\DetailKriteriaModel();

        // Get selected mahasiswa data
        $mahasiswa = $mahasiswaModel->whereIn('id', $selectedMahasiswa)
            ->orderBy('semester', 'ASC')
            ->orderBy('nim', 'ASC')
            ->findAll();

        // Get kriteria
        $kriteria = $kriteriaModel->orderBy('kode', 'ASC')->findAll();

        // Calculate nilai real-time untuk setiap mahasiswa × kriteria
        // Structure: $nilaiTable[$mahasiswaId][$kriteriaId] = nilai
        $nilaiTable = [];
        
        foreach ($mahasiswa as $m) {
            $nilaiTable[$m['id']] = [];
            
            // Calculate untuk setiap kriteria
            foreach ($kriteria as $k) {
                $nilai = $this->calculateNilaiKriteria(
                    $k['id'],
                    (float) $m['ipk'],
                    (int) $m['penghasilan_ortu'],
                    (int) $m['jumlah_tanggungan'],
                    $m['prestasi_non_akademik'],
                    $detailKriteriaModel
                );
                
                $nilaiTable[$m['id']][$k['id']] = $nilai;
            }
        }

        return view('penilaian/cek_penilaian', [
            'mahasiswa' => $mahasiswa,
            'kriteria' => $kriteria,
            'nilaiTable' => $nilaiTable,
            'selectedMahasiswa' => $selectedMahasiswa,
        ]);
    }

    /**
     * Form untuk select mahasiswa sebelum hitung SAW
     */
    public function formHitungSAW()
    {
        $mahasiswaModel = new MahasiswaModel();
        $penilaianModel = new PenilaianModel();
        $jumlahKriteria = (new KriteriaModel())->countAllResults();

        // Ambil mahasiswa yang sudah punya penilaian lengkap
        $allMahasiswa = $mahasiswaModel->orderBy('nim', 'ASC')->findAll();
        $mahasiswaLengkap = [];

        foreach ($allMahasiswa as $m) {
            $count = $penilaianModel->where('mahasiswa_id', $m['id'])->countAllResults();
            if ($jumlahKriteria > 0 && $count === $jumlahKriteria) {
                $mahasiswaLengkap[] = $m;
            }
        }

        return view('penilaian/form_hitung_saw', [
            'mahasiswa' => $mahasiswaLengkap,
            'totalMahasiswa' => count($mahasiswaLengkap),
        ]);
    }

    /**
     * Hitung SAW dengan step-by-step breakdown
     */
    public function hitungSAW()
    {
        $penilaianKe = (int) ($this->request->getPost('penilaian_ke') ?? 1);
        $threshold = (float) ($this->request->getPost('threshold') ?? 0.65);
        $selectedMahasiswa = $this->request->getPost('mahasiswa') ?? [];

        // Validasi minimal 1 mahasiswa dipilih
        if (empty($selectedMahasiswa)) {
            return redirect()->to('/penilaian')
                ->with('error', 'Pilih minimal 1 mahasiswa untuk dihitung.');
        }

        try {
            $sawService = new SAWService();
            $result = $sawService->process((int) $penilaianKe, (float) $threshold, $selectedMahasiswa);

            if (!$result['success']) {
                return redirect()->back()
                    ->with('error', $result['message'] ?? 'Terjadi kesalahan saat menghitung SAW.');
            }

            // Return dengan step-by-step breakdown
            return view('penilaian/hasil_perhitungan', [
                'result' => $result,
                'penilaian_ke' => $penilaianKe,
                'threshold' => $threshold,
                'selectedMahasiswa' => $selectedMahasiswa,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk return JSON (untuk AJAX)
     */
    public function hitungSAWAPI()
    {
        $penilaianKe = $this->request->getPost('penilaian_ke') ?? 1;
        $threshold = $this->request->getPost('threshold') ?? 0.65;

        $sawService = new SAWService();
        $result = $sawService->process((int) $penilaianKe, (float) $threshold);

        return $this->response->setJSON($result);
    }

    /**
     * Calculate nilai kriteria berdasarkan data mahasiswa dan detail_kriteria
     */
    private function calculateNilaiKriteria(
        int $kriteriaId,
        float $ipk,
        int $penghasilan,
        int $tanggungan,
        string $prestasi,
        $detailModel
    ): float {
        $details = $detailModel->where('kriteria_id', $kriteriaId)->findAll();

        if (empty($details)) {
            return 0;
        }

        // Calculate berdasarkan kriteria ID (1=IPK, 2=Penghasilan, 3=Tanggungan, 4=Prestasi)
        if ($kriteriaId == 1) {
            // IPK (Benefit) - range
            return $this->findNilaiByCriteria($details, $ipk, 'range');
        } elseif ($kriteriaId == 2) {
            // Penghasilan (Cost) - range
            return $this->findNilaiByCriteria($details, $penghasilan, 'range');
        } elseif ($kriteriaId == 3) {
            // Tanggungan (Cost) - exact match
            return $this->findNilaiByCriteria($details, $tanggungan, 'exact');
        } elseif ($kriteriaId == 4) {
            // Prestasi (Benefit) - text match
            return $this->findNilaiByPrestasi($details, $prestasi);
        }

        return 0;
    }

    /**
     * Find nilai by range atau exact match
     */
    private function findNilaiByCriteria(array $details, float|int $value, string $type): float
    {
        foreach ($details as $detail) {
            if ($type === 'range') {
                $batasBawah = (float) $detail['batas_bawah'];
                $batasAtas = (float) $detail['batas_atas'];
                if ($value >= $batasBawah && $value <= $batasAtas) {
                    return (float) $detail['nilai'];
                }
            } elseif ($type === 'exact') {
                $batasan = (int) $detail['batas_bawah'];
                $jenis = trim($detail['jenis_kondisi'] ?? '');

                if ($jenis === 'gt' && (int) $value > $batasan) {
                    return (float) $detail['nilai'];
                } elseif ($jenis === 'eq' && (int) $value === $batasan) {
                    return (float) $detail['nilai'];
                }
            }
        }

        return 0;
    }

    /**
     * Find nilai by prestasi keyword
     */
    private function findNilaiByPrestasi(array $details, string $prestasi): float
    {
        $prestasi = strtolower(trim($prestasi));

        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), $prestasi) !== false) {
                return (float) $detail['nilai'];
            }
        }

        // Fallback ke tidak berprestasi
        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), 'tidak') !== false) {
                return (float) $detail['nilai'];
            }
        }

        return 0;
    }
}
