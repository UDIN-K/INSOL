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
        $mahasiswa = (new MahasiswaModel())->orderBy('nim', 'ASC')->findAll();
        $jumlahKriteria = (new KriteriaModel())->countAllResults();
        $penilaianModel = new PenilaianModel();

        foreach ($mahasiswa as &$item) {
            $count = $penilaianModel->where('mahasiswa_id', $item['id'])->countAllResults();
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
     * Hitung SAW dan tampilkan step-by-step calculation
     */
    public function hitungSAW()
    {
        $penilaianKe = $this->request->getPost('penilaian_ke') ?? 1;
        $threshold = $this->request->getPost('threshold') ?? 0.65;
        $selectedMahasiswa = $this->request->getPost('mahasiswa') ?? [];

        // Validasi minimal 1 mahasiswa dipilih
        if (empty($selectedMahasiswa)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mahasiswa untuk dihitung.');
        }

        $sawService = new SAWService();
        $result = $sawService->process((int) $penilaianKe, (float) $threshold, $selectedMahasiswa);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message'] ?? 'Terjadi kesalahan saat menghitung SAW.');
        }

        // Return dengan step-by-step breakdown
        return view('penilaian/hasil_perhitungan', [
            'result' => $result,
            'penilaian_ke' => $penilaianKe,
            'threshold' => $threshold,
            'selectedMahasiswa' => $selectedMahasiswa,
        ]);
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
}
