<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;

class MahasiswaController extends BaseController
{
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
        (new MahasiswaModel())->update($id, $this->mahasiswaPayload());

        return redirect()->to('/mahasiswa')->with('success', 'Data diperbarui.');
    }

    public function postDelete(int $id)
    {
        (new MahasiswaModel())->delete($id);
        return redirect()->to('/mahasiswa')->with('success', 'Data dihapus.');
    }
}
