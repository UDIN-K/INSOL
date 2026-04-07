<?php

namespace App\Controllers;

use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;
use App\Models\PenilaianModel;

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

        $validationErrors = [];
        foreach ($kriteria as $item) {
            $kriteriaId = (int) $item['id'];
            $valueRaw = (string) ($nilaiInput[$kriteriaId] ?? '');

            if ($valueRaw === '' || ! is_numeric($valueRaw)) {
                $validationErrors[] = 'Nilai untuk kriteria ' . (string) $item['kode'] . ' wajib angka.';
                continue;
            }

            $parsed = (float) $valueRaw;
            if ($parsed < 0 || $parsed > 1) {
                $validationErrors[] = 'Nilai untuk kriteria ' . (string) $item['kode'] . ' harus antara 0 sampai 1.';
            }
        }

        if (! empty($validationErrors)) {
            return redirect()->back()->withInput()->with('validation_errors', $validationErrors)->with('error', 'Validasi penilaian gagal.');
        }

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
}
