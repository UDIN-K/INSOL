<?php

namespace App\Controllers;

use App\Models\DetailKriteriaModel;
use App\Models\HasilModel;
use App\Models\KriteriaModel;
use App\Models\MahasiswaModel;
use App\Models\PenilaianModel;

class HasilController extends BaseController
{
    private function normalizeText(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
    }

    private function toFloat(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }

    private function parseNumericPattern(string $label): ?array
    {
        $clean = str_replace(' ', '', $label);

        if (preg_match('/^(-?\d+(?:[\.,]\d+)?)-(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['type' => 'range', 'min' => $this->toFloat($m[1]), 'max' => $this->toFloat($m[2])];
        }

        if (preg_match('/^>(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['type' => 'gt', 'value' => $this->toFloat($m[1])];
        }

        if (preg_match('/^>=(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['type' => 'gte', 'value' => $this->toFloat($m[1])];
        }

        if (preg_match('/^<(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['type' => 'lt', 'value' => $this->toFloat($m[1])];
        }

        if (preg_match('/^<=(-?\d+(?:[\.,]\d+)?)$/', $clean, $m) === 1) {
            return ['type' => 'lte', 'value' => $this->toFloat($m[1])];
        }

        if (preg_match('/^-?\d+(?:[\.,]\d+)?$/', $clean) === 1) {
            return ['type' => 'eq', 'value' => $this->toFloat($clean)];
        }

        return null;
    }

    private function mapMahasiswaValue(array $mahasiswa, array $kriteria)
    {
        $name = $this->normalizeText((string) ($kriteria['kriteria'] ?? ''));

        if (str_contains($name, 'ipk')) {
            return $mahasiswa['ipk'] ?? null;
        }

        if (str_contains($name, 'penghasilan')) {
            return $mahasiswa['penghasilan_ortu'] ?? null;
        }

        if (str_contains($name, 'tanggungan')) {
            return $mahasiswa['jumlah_tanggungan'] ?? null;
        }

        if (str_contains($name, 'prestasi')) {
            return $mahasiswa['prestasi_non_akademik'] ?? null;
        }

        return null;
    }

    private function resolveNumericDetailScore(float $sourceValue, array $details): float
    {
        foreach ($details as $detail) {
            $jenis = (string) ($detail['jenis_kondisi'] ?? '');
            $bb = isset($detail['batas_bawah']) ? (float) $detail['batas_bawah'] : null;
            $ba = isset($detail['batas_atas']) ? (float) $detail['batas_atas'] : null;

            $ok = false;
            switch ($jenis) {
                case 'range':
                    if ($bb !== null && $ba !== null) {
                        $ok = $sourceValue >= $bb && $sourceValue <= $ba;
                    }
                    break;
                case 'gt':
                    if ($bb !== null) {
                        $ok = $sourceValue > $bb;
                    }
                    break;
                case 'gte':
                    if ($bb !== null) {
                        $ok = $sourceValue >= $bb;
                    }
                    break;
                case 'lt':
                    if ($bb !== null) {
                        $ok = $sourceValue < $bb;
                    }
                    break;
                case 'lte':
                    if ($bb !== null) {
                        $ok = $sourceValue <= $bb;
                    }
                    break;
                case 'eq':
                    if ($bb !== null) {
                        $ok = abs($sourceValue - $bb) < 0.000001;
                    }
                    break;
                default:
                    $pattern = $this->parseNumericPattern((string) ($detail['sub_kriteria'] ?? ''));
                    if ($pattern === null) {
                        $ok = false;
                        break;
                    }

                    switch ($pattern['type']) {
                        case 'range':
                            $ok = $sourceValue >= $pattern['min'] && $sourceValue <= $pattern['max'];
                            break;
                        case 'gt':
                            $ok = $sourceValue > $pattern['value'];
                            break;
                        case 'gte':
                            $ok = $sourceValue >= $pattern['value'];
                            break;
                        case 'lt':
                            $ok = $sourceValue < $pattern['value'];
                            break;
                        case 'lte':
                            $ok = $sourceValue <= $pattern['value'];
                            break;
                        case 'eq':
                            $ok = abs($sourceValue - $pattern['value']) < 0.000001;
                            break;
                    }
                    break;
            }

            if ($ok) {
                return (float) ($detail['nilai'] ?? 0);
            }
        }

        return 0.0;
    }

    private function resolveTextDetailScore(string $sourceValue, array $details): float
    {
        $source = $this->normalizeText($sourceValue);
        foreach ($details as $detail) {
            $target = $this->normalizeText((string) ($detail['sub_kriteria'] ?? ''));
            if ($target === '') {
                continue;
            }

            if (str_contains($target, $source) || str_contains($source, $target)) {
                return (float) ($detail['nilai'] ?? 0);
            }
        }

        return 0.0;
    }

    public function getIndex(): string
    {
        $rows = (new HasilModel())
            ->select('hasil.*, mahasiswa.nim, mahasiswa.nama')
            ->join('mahasiswa', 'mahasiswa.id = hasil.mahasiswa_id')
            ->orderBy('penilaian_ke', 'DESC')
            ->orderBy('ranking', 'ASC')
            ->findAll();

        return view('hasil/index', ['rows' => $rows]);
    }

    public function postProses()
    {
        $kriteria = (new KriteriaModel())->findAll();
        $mahasiswa = (new MahasiswaModel())->findAll();
        $penilaian = (new PenilaianModel())->findAll();
        $detailRows = (new DetailKriteriaModel())->findAll();
        $detailByKriteria = [];
        foreach ($detailRows as $detail) {
            $detailByKriteria[(int) $detail['kriteria_id']][] = $detail;
        }

        if (empty($kriteria) || empty($mahasiswa)) {
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

        foreach ($mahasiswa as $m) {
            foreach ($kriteria as $k) {
                $mid = (int) $m['id'];
                $kid = (int) $k['id'];

                if (isset($nilai[$mid][$kid])) {
                    continue;
                }

                $source = $this->mapMahasiswaValue($m, $k);
                if ($source === null || $source === '') {
                    $nilai[$mid][$kid] = 0;
                    continue;
                }

                $details = $detailByKriteria[$kid] ?? [];
                if (is_numeric($source)) {
                    $nilai[$mid][$kid] = $this->resolveNumericDetailScore((float) $source, $details);
                } else {
                    $nilai[$mid][$kid] = $this->resolveTextDetailScore((string) $source, $details);
                }
            }
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
                $n = (($k['atribut'] ?? 'benefit') === 'cost')
                    ? ($v > 0 ? $min[$kid] / $v : 0)
                    : ($max[$kid] > 0 ? $v / $max[$kid] : 0);
                $pref += $n * $w;
            }
            $scores[] = ['mahasiswa_id' => $m['id'], 'skor' => round($pref, 6)];
        }

        usort($scores, static fn ($a, $b) => $b['skor'] <=> $a['skor']);

        $kuota = max(1, (int) $this->request->getPost('kuota'));
        $model = new HasilModel();
        $last = $model->selectMax('penilaian_ke')->first();
        $penilaianKe = (int) ($last['penilaian_ke'] ?? 0) + 1;

        foreach ($scores as $i => $item) {
            $rank = $i + 1;
            $model->insert([
                'mahasiswa_id' => $item['mahasiswa_id'],
                'penilaian_ke' => $penilaianKe,
                'skor' => $item['skor'],
                'ranking' => $rank,
                'status_lolos' => $rank <= $kuota ? 'Lolos' : 'Tidak Lolos',
            ]);
        }

        return redirect()->to('/hasil')->with('success', 'Perhitungan SAW selesai untuk penilaian ke-' . $penilaianKe . '.');
    }
}
