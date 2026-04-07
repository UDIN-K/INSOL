<?php

namespace App\Controllers;

use App\Models\DetailKriteriaModel;
use App\Models\KriteriaModel;

class KriteriaController extends BaseController
{
    private function validateKriteriaInput(?int $excludeId = null): array
    {
        $errors = [];
        $validation = service('validation');
        $validation->setRules([
            'kode' => 'required|regex_match[/^C[0-9]+$/i]|max_length[10]',
            'kriteria' => 'required|min_length[2]|max_length[120]',
            'bobot' => 'required|decimal|greater_than[0]|less_than_equal_to[1]',
            'atribut' => 'required|in_list[benefit,cost]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
        }

        $kode = strtoupper(trim((string) $this->request->getPost('kode')));
        $existingKode = (new KriteriaModel())->where('kode', $kode);
        if ($excludeId !== null) {
            $existingKode = $existingKode->where('id !=', $excludeId);
        }
        if ($existingKode->first() !== null) {
            $errors['kode'] = 'Kode kriteria sudah dipakai.';
        }

        return $errors;
    }

    private function validateDetailInput(): array
    {
        $validation = service('validation');
        $validation->setRules([
            'kriteria_id' => 'required|integer',
            'jenis_kondisi' => 'required|in_list[text,range,eq,gt,gte,lt,lte]',
            'nilai' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[1]',
            'sub_kriteria' => 'permit_empty|max_length[150]',
            'batas_bawah' => 'permit_empty|decimal',
            'batas_atas' => 'permit_empty|decimal',
        ]);

        $errors = [];
        if (! $validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
        }

        $jenis = (string) ($this->request->getPost('jenis_kondisi') ?: 'text');
        $subKriteria = trim((string) $this->request->getPost('sub_kriteria'));
        $batasBawah = trim((string) $this->request->getPost('batas_bawah'));
        $batasAtas = trim((string) $this->request->getPost('batas_atas'));

        if ($jenis === 'text' && $subKriteria === '') {
            $errors['sub_kriteria'] = 'Sub kriteria wajib diisi untuk jenis text.';
        }

        if ($jenis !== 'text' && $batasBawah === '') {
            $errors['batas_bawah'] = 'Batas bawah wajib diisi untuk jenis numerik.';
        }

        if ($jenis === 'range' && $batasAtas === '') {
            $errors['batas_atas'] = 'Batas atas wajib diisi untuk jenis range.';
        }

        if ($jenis === 'range' && $batasBawah !== '' && $batasAtas !== '' && (float) $batasBawah > (float) $batasAtas) {
            $errors['batas_atas'] = 'Batas atas harus lebih besar atau sama dengan batas bawah.';
        }

        return $errors;
    }

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

        $bobotStatus = abs($totalBobot - 1.0) < 0.0001 ? 'ideal' : ($totalBobot < 1 ? 'kurang' : 'lebih');

        return view('kriteria/index', [
            'kriteria' => $kriteria,
            'detailKriteria' => $detailKriteria,
            'totalBobot' => $totalBobot,
            'bobotStatus' => $bobotStatus,
        ]);
    }

    public function getCreate(): string
    {
        return view('kriteria/form', ['title' => 'Tambah Kriteria', 'action' => '/kriteria/store', 'kriteria' => null]);
    }

    public function postStore()
    {
        $errors = $this->validateKriteriaInput();
        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi data kriteria gagal.');
        }

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
        $errors = $this->validateKriteriaInput($id);
        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi data kriteria gagal.');
        }

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
        $errors = $this->validateDetailInput();
        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi detail kriteria gagal.');
        }

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
        $errors = $this->validateDetailInput();
        if (! empty($errors)) {
            return redirect()->back()->withInput()->with('validation_errors', $errors)->with('error', 'Validasi detail kriteria gagal.');
        }

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
