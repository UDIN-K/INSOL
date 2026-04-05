<?php

namespace App\Controllers;

use App\Models\DetailKriteriaModel;
use App\Models\KriteriaModel;

class KriteriaController extends BaseController
{
    public function getIndex(): string
    {
        $kriteria = (new KriteriaModel())->orderBy('id', 'ASC')->findAll();
        $detailKriteria = (new DetailKriteriaModel())
            ->select('detail_kriteria.*, kriteria.kriteria')
            ->join('kriteria', 'kriteria.id = detail_kriteria.kriteria_id')
            ->orderBy('kriteria.id', 'ASC')
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
            'kriteria' => $this->request->getPost('kriteria'),
            'bobot' => $this->request->getPost('bobot'),
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
            'kriteria' => $this->request->getPost('kriteria'),
            'bobot' => $this->request->getPost('bobot'),
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
            'kriteria' => (new KriteriaModel())->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    public function postDetailStore()
    {
        (new DetailKriteriaModel())->insert([
            'kriteria_id' => $this->request->getPost('kriteria_id'),
            'sub_kriteria' => $this->request->getPost('sub_kriteria'),
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
            'kriteria' => (new KriteriaModel())->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    public function postDetailUpdate(int $id)
    {
        (new DetailKriteriaModel())->update($id, [
            'kriteria_id' => $this->request->getPost('kriteria_id'),
            'sub_kriteria' => $this->request->getPost('sub_kriteria'),
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
