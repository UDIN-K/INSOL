<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;

class MahasiswaController extends BaseController
{
    private function mahasiswaRules(): array
    {
        return [
            'nim' => 'required|min_length[5]|max_length[30]',
            'nama' => 'required|min_length[3]|max_length[120]',
            'email' => 'permit_empty|valid_email|max_length[120]',
            'semester' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[14]',
            'tahun_masuk' => 'permit_empty|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            'ipk' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[4]',
            'penghasilan_ortu' => 'permit_empty|integer|greater_than_equal_to[0]',
            'jumlah_tanggungan' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[20]',
            'prestasi_non_akademik' => 'permit_empty|in_list[universitas,kota,provinsi,nasional,internasional]',
        ];
    }

    private function validateMahasiswaInput(): ?array
    {
        $validation = service('validation');
        $validation->setRules($this->mahasiswaRules());

        if (! $validation->withRequest($this->request)->run()) {
            return $validation->getErrors();
        }

        return null;
    }

    private function validateUniqueNim(?int $excludeId = null): ?string
    {
        $nim = trim((string) $this->request->getPost('nim'));
        $model = new MahasiswaModel();
        $query = $model->where('nim', $nim);

        if ($excludeId !== null) {
            $query = $query->where('id !=', $excludeId);
        }

        $existing = $query->first();
        if ($existing !== null) {
            return 'NIM sudah digunakan mahasiswa lain.';
        }

        return null;
    }
//model db
    private function mahasiswaPayload(): array
    {
        return [
            'nim' => $this->request->getPost('nim'),
            'nama' => $this->request->getPost('nama'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tempat_lahir' => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'alamat' => $this->request->getPost('alamat'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'prodi' => $this->request->getPost('prodi'),
            'semester' => $this->request->getPost('semester'),
            'tahun_masuk' => $this->request->getPost('tahun_masuk'),
            'nama_ibu' => $this->request->getPost('nama_ibu'),
            'nama_bapak' => $this->request->getPost('nama_bapak'),
            'ipk' => $this->request->getPost('ipk'),
            'penghasilan_ortu' => $this->request->getPost('penghasilan_ortu'),
            'jumlah_tanggungan' => $this->request->getPost('jumlah_tanggungan'),
            'prestasi_non_akademik' => $this->request->getPost('prestasi_non_akademik'),
        ];
    }

    public function getIndex(): string
    {
        return view('mahasiswa/index', [
            'mahasiswa' => (new MahasiswaModel())->orderBy('nim', 'ASC')->findAll(),
        ]);
    }

    public function getCreate(): string
    {
        return view('mahasiswa/form', ['title' => 'Tambah Mahasiswa', 'action' => '/mahasiswa/store', 'mahasiswa' => null]);
    }

    public function postStore()
    {
        $errors = $this->validateMahasiswaInput() ?? [];
        $nimError = $this->validateUniqueNim();
        if ($nimError !== null) {
            $errors['nim'] = $nimError;
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi data mahasiswa gagal.');
        }

        (new MahasiswaModel())->insert($this->mahasiswaPayload());

        return redirect()->to('/mahasiswa')->with('success', 'Data tersimpan.');
    }

    public function getEdit(int $id): string
    {
        return view('mahasiswa/form', [
            'title' => 'Edit Mahasiswa',
            'action' => '/mahasiswa/update/' . $id,
            'mahasiswa' => (new MahasiswaModel())->find($id),
        ]);
    }

    public function postUpdate(int $id)
    {
        $errors = $this->validateMahasiswaInput() ?? [];
        $nimError = $this->validateUniqueNim($id);
        if ($nimError !== null) {
            $errors['nim'] = $nimError;
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi data mahasiswa gagal.');
        }

        (new MahasiswaModel())->update($id, $this->mahasiswaPayload());

        return redirect()->to('/mahasiswa')->with('success', 'Data diperbarui.');
    }

    public function postDelete(int $id)
    {
        (new MahasiswaModel())->delete($id);
        return redirect()->to('/mahasiswa')->with('success', 'Data dihapus.');
    }

    /**
     * Form input data mahasiswa untuk penilaian (IPK, penghasilan, tanggungan, prestasi)
     * Halaman ini akan auto-calculate nilai per kriteria
     */
    public function getInputData(): string
    {
        return view('mahasiswa/input_data', []);
    }

    /**
     * Simpan data mahasiswa dan auto-calculate penilaian per kriteria
     * Workflow:
     * 1. Insert/update mahasiswa dengan data IPK, penghasilan, tanggungan, prestasi
     * 2. Auto-calculate nilai 0-1 untuk setiap kriteria berdasarkan detail_kriteria
     * 3. Insert nilai tersebut ke tabel penilaian
     */
    public function postSimpan()
    {
        $mahasiswaModel = new \App\Models\MahasiswaModel();
        $penilaianModel = new \App\Models\PenilaianModel();
        $kriteriaModel = new \App\Models\KriteriaModel();
        $detailKriteriaModel = new \App\Models\DetailKriteriaModel();

        try {
            // Validasi input
            $nim = $this->request->getPost('nim');
            $nama = $this->request->getPost('nama');
            $semester = $this->request->getPost('semester');
            $tahun_masuk = $this->request->getPost('angkatan'); // Map to tahun_masuk
            $ipk = (float) $this->request->getPost('ipk');
            $penghasilan = (int) $this->request->getPost('penghasilan_ortu');
            $tanggungan = (int) $this->request->getPost('jumlah_tanggungan');
            $prestasi = $this->request->getPost('prestasi'); // Will map to prestasi_non_akademik

            if (!$nim || !$nama || !$semester || !$tahun_masuk || !$ipk || !$penghasilan || !$tanggungan || !$prestasi) {
                return redirect()->back()->with('error', 'Semua field harus diisi!');
            }

            // Map prestasi form value to enum
            $prestasiEnum = $this->mapPrestasiToEnum($prestasi);

            // Check if mahasiswa exists
            $existingMahasiswa = $mahasiswaModel->where('nim', $nim)->first();
            $mahasiswaId = null;

            if ($existingMahasiswa) {
                // Update existing
                $mahasiswaModel->update($existingMahasiswa['id'], [
                    'nama' => $nama,
                    'semester' => $semester,
                    'tahun_masuk' => $tahun_masuk,
                    'ipk' => $ipk,
                    'penghasilan_ortu' => $penghasilan,
                    'jumlah_tanggungan' => $tanggungan,
                    'prestasi_non_akademik' => $prestasiEnum,
                ]);
                $mahasiswaId = $existingMahasiswa['id'];
            } else {
                // Insert new
                $mahasiswaModel->insert([
                    'nim' => $nim,
                    'nama' => $nama,
                    'semester' => $semester,
                    'tahun_masuk' => $tahun_masuk,
                    'ipk' => $ipk,
                    'penghasilan_ortu' => $penghasilan,
                    'jumlah_tanggungan' => $tanggungan,
                    'prestasi_non_akademik' => $prestasiEnum,
                ]);
                $mahasiswaId = $mahasiswaModel->getInsertID();
            }

            // Auto-calculate nilai per kriteria based on detail_kriteria
            $kriteria = $kriteriaModel->findAll();
            $penilaianKe = 1; // Default periode 1

            foreach ($kriteria as $k) {
                $nilai = $this->calculateNilaiKriteria(
                    $k['id'],
                    $k['atribut'],
                    $ipk,
                    $penghasilan,
                    $tanggungan,
                    $prestasi,
                    $detailKriteriaModel
                );

                // Check if penilaian exists
                $existingPenilaian = $penilaianModel
                    ->where('mahasiswa_id', $mahasiswaId)
                    ->where('kriteria_id', $k['id'])
                    ->where('penilaian_ke', $penilaianKe)
                    ->first();

                if ($existingPenilaian) {
                    $penilaianModel->update($existingPenilaian['id'], [
                        'nilai' => $nilai,
                    ]);
                } else {
                    $penilaianModel->insert([
                        'mahasiswa_id' => $mahasiswaId,
                        'kriteria_id' => $k['id'],
                        'penilaian_ke' => $penilaianKe,
                        'nilai' => $nilai,
                    ]);
                }
            }

            return redirect()->to('/penilaian')
                ->with('success', "Data mahasiswa '{$nama}' berhasil disimpan. Nilai per kriteria sudah dihitung otomatis.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate nilai kriteria berdasarkan atribut dan detail_kriteria
     * 
     * @param int $kriteriaId - ID kriteria
     * @param string $atribut - 'benefit' atau 'cost'
     * @param float $ipk - Nilai IPK mahasiswa
     * @param int $penghasilan - Penghasilan orang tua
     * @param int $tanggungan - Jumlah tanggungan
     * @param string $prestasi - Prestasi non-akademik
     * @param DetailKriteriaModel $detailModel
     * @return float - Nilai 0-1
     */
    private function calculateNilaiKriteria(
        int $kriteriaId,
        string $atribut,
        float $ipk,
        int $penghasilan,
        int $tanggungan,
        string $prestasi,
        $detailModel
    ): float {
        // Get detail kriteria
        $details = $detailModel->where('kriteria_id', $kriteriaId)->findAll();

        if (empty($details)) {
            return 0;
        }

        // Get nilai based on kriteria ID (1=IPK, 2=Penghasilan, 3=Tanggungan, 4=Prestasi)
        $nilai = 0;

        if ($kriteriaId == 1) {
            // IPK (Benefit)
            $nilai = $this->findNilaiByCriteria($details, $ipk, 'range');
        } elseif ($kriteriaId == 2) {
            // Penghasilan (Cost)
            $nilai = $this->findNilaiByCriteria($details, $penghasilan, 'range');
        } elseif ($kriteriaId == 3) {
            // Tanggungan (Cost)
            $nilai = $this->findNilaiByCriteria($details, $tanggungan, 'exact');
        } elseif ($kriteriaId == 4) {
            // Prestasi (Benefit)
            $nilai = $this->findNilaiByPrestasi($details, $prestasi);
        }

        return (float) $nilai;
    }

    /**
     * Find nilai in detail_kriteria by range or exact match
     * Untuk range: cek batas_bawah <= value <= batas_atas
     * Untuk exact: cek batas_bawah == value atau gunakan jenis_kondisi
     */
    private function findNilaiByCriteria(array $details, float|int $value, string $type): float
    {
        foreach ($details as $detail) {
            if ($type === 'range') {
                // Untuk range (IPK, Penghasilan)
                $batasBawah = (float) $detail['batas_bawah'];
                $batasAtas = (float) $detail['batas_atas'];
                if ($value >= $batasBawah && $value <= $batasAtas) {
                    return (float) $detail['nilai'];
                }
            } elseif ($type === 'exact') {
                // Untuk exact match (Tanggungan)
                // Cek batas_bawah sebagai nilai yang dicari (1, 2, 3, 4, atau >5)
                $batasan = (int) $detail['batas_bawah'];
                $jenis = trim($detail['jenis_kondisi'] ?? '');
                
                if ($jenis === 'gt' && (int) $value > $batasan) {
                    // Greater than (>5)
                    return (float) $detail['nilai'];
                } elseif ($jenis === 'eq' && (int) $value === $batasan) {
                    // Equal (1, 2, 3, 4)
                    return (float) $detail['nilai'];
                }
            }
        }

        return 0;
    }

    /**
     * Find nilai by prestasi name/value
     * Cari nilai berdasarkan enum prestasi di database
     * Mapping: universitas, kota, provinsi, nasional → sub_kriteria
     */
    private function findNilaiByPrestasi(array $details, string $prestasi): float
    {
        $prestasi = strtolower(trim($prestasi));
        
        // Mapping prestasi enum ke sub_kriteria pattern
        $mapping = [
            'universitas' => 'universitas',
            'kota' => 'kota',
            'provinsi' => 'provinsi',
            'nasional' => 'nasional',
            'internasional' => 'internasional',
        ];

        $targetKeyword = $mapping[$prestasi] ?? $prestasi;

        // Cari sub_kriteria yang mengandung keyword
        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), $targetKeyword) !== false) {
                return (float) $detail['nilai'];
            }
        }

        // Fallback ke default (tidak berprestasi)
        foreach ($details as $detail) {
            if (strpos(strtolower($detail['sub_kriteria']), 'tidak') !== false) {
                return (float) $detail['nilai'];
            }
        }

        return 0;
    }

    /**
     * Map form prestasi value to database enum value
     * Database enum: universitas, kota, provinsi, nasional, internasional
     */
    private function mapPrestasiToEnum(string $prestasi): string
    {
        $map = [
            'Tidak berprestasi' => 'universitas', // Fallback to universitas
            'Universitas' => 'universitas',
            'Kota' => 'kota',
            'Provinsi' => 'provinsi',
            'Nasional' => 'nasional',
        ];

        return $map[$prestasi] ?? 'universitas';
    }
}
