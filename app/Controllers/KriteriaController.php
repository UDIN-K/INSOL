<?php

namespace App\Controllers;

use App\Models\DetailKriteriaModel;
use App\Models\KriteriaModel;

class KriteriaController extends BaseController
{
    private function buildSubKriteriaLabel(string $jenis, ?string $subKriteria, ?string $batasBawah, ?string $batasAtas): string
    {
        $label = trim((string) $subKriteria);
        if ($jenis === 'text') {
            return $label;
        }

        $bb = trim((string) $batasBawah);
        $ba = trim((string) $batasAtas);

        return match ($jenis) {
            'range' => $bb . '-' . $ba,
            'eq' => $bb,
            'gt' => '>' . $bb,
            'gte' => '>=' . $bb,
            'lt' => '<' . $bb,
            'lte' => '<=' . $bb,
            default => $label,
        };
    }

    public function getIndex(): string
    {
        $kriteria = (new KriteriaModel())->orderBy('kode', 'ASC')->findAll();
        $detailKriteria = (new DetailKriteriaModel())
            ->select('detail_kriteria.*, kriteria.kode, kriteria.kriteria')
            ->join('kriteria', 'kriteria.id = detail_kriteria.kriteria_id')
            ->orderBy('kriteria.kode', 'ASC')
            ->orderBy('detail_kriteria.nilai', 'ASC')
            ->orderBy('detail_kriteria.id', 'ASC')
            ->findAll();
        $totalBobot = 0.0;
        foreach ($kriteria as $item) {
            $totalBobot += (float) $item['bobot'];
        }

        return view('kriteria/index', [
            'kriteria' => $kriteria,
            'detailKriteria' => $detailKriteria,
            'totalBobot' => $totalBobot,
        ]);
    }

    public function getCreate(): string
    {
        return view('kriteria/form', ['title' => 'Tambah Kriteria', 'action' => '/kriteria/store', 'kriteria' => null]);
    }

    public function postStore()
    {
        (new KriteriaModel())->insert([
            'kode' => strtoupper((string) $this->request->getPost('kode')),
            'kriteria' => $this->request->getPost('kriteria'),
            'bobot' => $this->request->getPost('bobot'),
            'atribut' => $this->request->getPost('atribut'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Data tersimpan.');
    }

    public function getEdit(int $id): string
    {
        return view('kriteria/form', [
            'title' => 'Edit Kriteria',
            'action' => '/kriteria/update/' . $id,
            'kriteria' => (new KriteriaModel())->find($id),
        ]);
    }

    public function postUpdate(int $id)
    {
        (new KriteriaModel())->update($id, [
            'kode' => strtoupper((string) $this->request->getPost('kode')),
            'kriteria' => $this->request->getPost('kriteria'),
            'bobot' => $this->request->getPost('bobot'),
            'atribut' => $this->request->getPost('atribut'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Data diperbarui.');
    }

    public function postDelete(int $id)
    {
        (new KriteriaModel())->delete($id);
        return redirect()->to('/kriteria')->with('success', 'Data dihapus.');
    }

    public function getDetailCreate(): string
    {
        return view('kriteria/detail_form', [
            'title' => 'Tambah Detail Kriteria',
            'action' => '/kriteria/detail/store',
            'detail' => null,
            'kriteria' => (new KriteriaModel())->orderBy('kode', 'ASC')->findAll(),
        ]);
    }

    public function postDetailStore()
    {
        $jenis = (string) ($this->request->getPost('jenis_kondisi') ?: 'text');
        $batasBawah = $this->request->getPost('batas_bawah');
        $batasAtas = $this->request->getPost('batas_atas');

        (new DetailKriteriaModel())->insert([
            'kriteria_id' => $this->request->getPost('kriteria_id'),
            'sub_kriteria' => $this->buildSubKriteriaLabel($jenis, (string) $this->request->getPost('sub_kriteria'), (string) $batasBawah, (string) $batasAtas),
            'jenis_kondisi' => $jenis,
            'batas_bawah' => $batasBawah !== '' ? $batasBawah : null,
            'batas_atas' => $batasAtas !== '' ? $batasAtas : null,
            'nilai' => $this->request->getPost('nilai'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Detail kriteria tersimpan.');
    }

    public function getDetailEdit(int $id): string
    {
        return view('kriteria/detail_form', [
            'title' => 'Edit Detail Kriteria',
            'action' => '/kriteria/detail/update/' . $id,
            'detail' => (new DetailKriteriaModel())->find($id),
            'kriteria' => (new KriteriaModel())->orderBy('kode', 'ASC')->findAll(),
        ]);
    }

    public function postDetailUpdate(int $id)
    {
        $jenis = (string) ($this->request->getPost('jenis_kondisi') ?: 'text');
        $batasBawah = $this->request->getPost('batas_bawah');
        $batasAtas = $this->request->getPost('batas_atas');

        (new DetailKriteriaModel())->update($id, [
            'kriteria_id' => $this->request->getPost('kriteria_id'),
            'sub_kriteria' => $this->buildSubKriteriaLabel($jenis, (string) $this->request->getPost('sub_kriteria'), (string) $batasBawah, (string) $batasAtas),
            'jenis_kondisi' => $jenis,
            'batas_bawah' => $batasBawah !== '' ? $batasBawah : null,
            'batas_atas' => $batasAtas !== '' ? $batasAtas : null,
            'nilai' => $this->request->getPost('nilai'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Detail kriteria diperbarui.');
    }

    public function postDetailDelete(int $id)
    {
        (new DetailKriteriaModel())->delete($id);
        return redirect()->to('/kriteria')->with('success', 'Detail kriteria dihapus.');
    }
}
