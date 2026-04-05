<?php

namespace App\Controllers;

use App\Models\HasilModel;
use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;
use App\Models\PenilaianModel;

class HasilController extends BaseController
{
    private function isCost(string $kriteria): bool
    {
        return str_contains(strtolower($kriteria), 'penghasilan');
    }

    public function getIndex(): string
    {
        $rows = (new HasilModel())
            ->select('hasil.*, mahasiswa.nim, mahasiswa.nama')
            ->join('mahasiswa', 'mahasiswa.id = hasil.mahasiswa_id')
            ->orderBy('ranking', 'ASC')
            ->findAll();

        return view('hasil/index', ['rows' => $rows]);
    }

    public function postProses()
    {
        $kriteria = (new KriteriaModel())->findAll();
        $mahasiswa = (new MahasiswaModel())->findAll();
        $penilaian = (new PenilaianModel())->findAll();

        if (empty($kriteria) || empty($mahasiswa) || empty($penilaian)) {
            return redirect()->to('/hasil')->with('error', 'Data belum lengkap untuk proses SAW.');
        }

        $bobotTotal = array_sum(array_map(static fn ($k) => (float) $k['bobot'], $kriteria));
        if ($bobotTotal <= 0) {
            return redirect()->to('/hasil')->with('error', 'Total bobot harus lebih dari 0.');
        }

        $nilai = [];
        foreach ($penilaian as $p) {
            $nilai[$p['mahasiswa_id']][$p['kriteria_id']] = (float) $p['nilai'];
        }

        $max = [];
        $min = [];
        foreach ($kriteria as $k) {
            $kid = (int) $k['id'];
            $arr = [];
            foreach ($mahasiswa as $m) {
                $arr[] = $nilai[$m['id']][$kid] ?? 0;
            }
            $max[$kid] = max($arr);
            $min[$kid] = min($arr);
        }

        $scores = [];
        foreach ($mahasiswa as $m) {
            $pref = 0.0;
            foreach ($kriteria as $k) {
                $kid = (int) $k['id'];
                $v = (float) ($nilai[$m['id']][$kid] ?? 0);
                $w = ((float) $k['bobot']) / $bobotTotal;
                $n = $this->isCost((string) ($k['kriteria'] ?? ''))
                    ? ($v > 0 ? $min[$kid] / $v : 0)
                    : ($max[$kid] > 0 ? $v / $max[$kid] : 0);
                $pref += $n * $w;
            }
            $scores[] = ['mahasiswa_id' => $m['id'], 'skor' => round($pref, 6)];
        }

        usort($scores, static fn ($a, $b) => $b['skor'] <=> $a['skor']);

        $kuota = max(1, (int) $this->request->getPost('kuota'));
        $model = new HasilModel();
        $model->truncate();

        foreach ($scores as $i => $item) {
            $rank = $i + 1;
            $model->insert([
                'mahasiswa_id' => $item['mahasiswa_id'],
                'skor' => $item['skor'],
                'ranking' => $rank,
                'status_lolos' => $rank <= $kuota ? 'Lolos' : 'Tidak Lolos',
            ]);
        }

        return redirect()->to('/hasil')->with('success', 'Perhitungan SAW selesai.');
    }
}
