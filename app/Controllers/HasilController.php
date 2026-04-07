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

    private function buildSawComputation(int $kuota): array
    {
        $kriteria = (new KriteriaModel())->orderBy('kode', 'ASC')->findAll();
        $mahasiswa = (new MahasiswaModel())->orderBy('nim', 'ASC')->findAll();
        $penilaian = (new PenilaianModel())->findAll();
        $detailRows = (new DetailKriteriaModel())->findAll();

        if (empty($kriteria) || empty($mahasiswa)) {
            return ['error' => 'Data kriteria atau mahasiswa belum tersedia.'];
        }

        $bobotTotal = array_sum(array_map(static fn ($k) => (float) $k['bobot'], $kriteria));
        if ($bobotTotal <= 0) {
            return ['error' => 'Total bobot harus lebih dari 0.'];
        }

        $detailByKriteria = [];
        foreach ($detailRows as $detail) {
            $detailByKriteria[(int) $detail['kriteria_id']][] = $detail;
        }

        $nilai = [];
        foreach ($penilaian as $p) {
            $nilai[(int) $p['mahasiswa_id']][(int) $p['kriteria_id']] = (float) $p['nilai'];
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
                    continue;
                }

                $nilai[$mid][$kid] = $this->resolveTextDetailScore((string) $source, $details);
            }
        }

        $max = [];
        $min = [];
        foreach ($kriteria as $k) {
            $kid = (int) $k['id'];
            $arr = [];
            foreach ($mahasiswa as $m) {
                $arr[] = (float) ($nilai[(int) $m['id']][$kid] ?? 0);
            }
            $max[$kid] = max($arr);
            $min[$kid] = min($arr);
        }

        $normalisasi = [];
        $preferensi = [];
        $scores = [];

        foreach ($mahasiswa as $m) {
            $mid = (int) $m['id'];
            $pref = 0.0;
            $detailKomponen = [];

            foreach ($kriteria as $k) {
                $kid = (int) $k['id'];
                $v = (float) ($nilai[$mid][$kid] ?? 0);
                $w = ((float) $k['bobot']) / $bobotTotal;
                $n = (($k['atribut'] ?? 'benefit') === 'cost')
                    ? ($v > 0 ? $min[$kid] / $v : 0)
                    : ($max[$kid] > 0 ? $v / $max[$kid] : 0);

                $normalisasi[$mid][$kid] = $n;
                $nilaiTerbobot = $n * $w;
                $pref += $nilaiTerbobot;

                $detailKomponen[] = [
                    'kriteria_id' => $kid,
                    'kode' => (string) $k['kode'],
                    'kriteria' => (string) $k['kriteria'],
                    'raw' => $v,
                    'normalisasi' => $n,
                    'bobot' => $w,
                    'terbobot' => $nilaiTerbobot,
                    'atribut' => (string) $k['atribut'],
                ];
            }

            $skorAkhir = round($pref, 6);
            $scores[] = [
                'mahasiswa_id' => $mid,
                'nim' => (string) $m['nim'],
                'nama' => (string) $m['nama'],
                'skor' => $skorAkhir,
            ];

            $preferensi[$mid] = [
                'skor' => $skorAkhir,
                'komponen' => $detailKomponen,
            ];
        }

        usort($scores, static fn ($a, $b) => $b['skor'] <=> $a['skor']);
        foreach ($scores as $i => &$score) {
            $score['ranking'] = $i + 1;
            $score['status_lolos'] = ($i + 1) <= $kuota ? 'Lolos' : 'Tidak Lolos';
        }

        return [
            'kriteria' => $kriteria,
            'mahasiswa' => $mahasiswa,
            'bobotTotal' => $bobotTotal,
            'nilai' => $nilai,
            'normalisasi' => $normalisasi,
            'preferensi' => $preferensi,
            'ranking' => $scores,
            'kuota' => $kuota,
        ];
    }

    public function getIndex(): string
    {
        $rows = (new HasilModel())
            ->select('hasil.*, mahasiswa.nim, mahasiswa.nama')
            ->join('mahasiswa', 'mahasiswa.id = hasil.mahasiswa_id')
            ->orderBy('penilaian_ke', 'DESC')
            ->orderBy('ranking', 'ASC')
            ->findAll();

        $kuotaPreview = max(1, (int) ($this->request->getGet('kuota_preview') ?? 3));
        $preview = $this->buildSawComputation($kuotaPreview);

        return view('hasil/index', [
            'rows' => $rows,
            'preview' => $preview,
            'kuotaPreview' => $kuotaPreview,
        ]);
    }

    public function postProses()
    {
        $kuota = max(1, (int) $this->request->getPost('kuota'));
        $computed = $this->buildSawComputation($kuota);
        if (isset($computed['error'])) {
            return redirect()->to('/hasil')->with('error', (string) $computed['error']);
        }

        $scores = $computed['ranking'];
        $model = new HasilModel();
        $last = $model->selectMax('penilaian_ke')->first();
        $penilaianKe = (int) ($last['penilaian_ke'] ?? 0) + 1;

        foreach ($scores as $item) {
            $model->insert([
                'mahasiswa_id' => $item['mahasiswa_id'],
                'penilaian_ke' => $penilaianKe,
                'skor' => $item['skor'],
                'ranking' => $item['ranking'],
                'status_lolos' => $item['status_lolos'],
            ]);
        }

        return redirect()->to('/hasil')->with('success', 'Perhitungan SAW selesai untuk penilaian ke-' . $penilaianKe . '.');
    }
}
