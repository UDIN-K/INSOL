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

    private function buildSawComputation(float $skorMinimum, array $selectedMahasiswaIds = []): array
    {
        $kriteria = (new KriteriaModel())->orderBy('kode', 'ASC')->findAll();
        $allMahasiswa = (new MahasiswaModel())->orderBy('nim', 'ASC')->findAll();
        $penilaian = (new PenilaianModel())->findAll();
        $detailRows = (new DetailKriteriaModel())->findAll();

        // Filter mahasiswa yang dipilih
        $mahasiswa = [];
        if (!empty($selectedMahasiswaIds)) {
            foreach ($allMahasiswa as $m) {
                if (in_array((int) $m['id'], $selectedMahasiswaIds, true)) {
                    $mahasiswa[] = $m;
                }
            }
        } else {
            $mahasiswa = $allMahasiswa;
        }

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
            $score['status_lolos'] = $score['skor'] >= $skorMinimum ? 'Lolos' : 'Tidak Lolos';
        }

        return [
            'kriteria' => $kriteria,
            'mahasiswa' => $mahasiswa,
            'bobotTotal' => $bobotTotal,
            'nilai' => $nilai,
            'normalisasi' => $normalisasi,
            'preferensi' => $preferensi,
            'ranking' => $scores,
            'skorMinimum' => $skorMinimum,
        ];
    }

    public function getIndex(): string
    {
        $search = (string) $this->request->getGet('search');
        $semester = (string) $this->request->getGet('semester');

        // Get all unique semesters for filter
        $db = \Config\Database::connect();
        $semesters = $db->query('SELECT DISTINCT semester FROM mahasiswa WHERE semester IS NOT NULL ORDER BY semester ASC')->getResultArray();

        $mahasiswaModel = new MahasiswaModel();
        $query = $mahasiswaModel;

        // Apply filters
        if ($search !== '') {
            $query = $query->groupStart()
                ->like('nim', $search)
                ->orLike('nama', $search)
                ->groupEnd();
        }

        if ($semester !== '') {
            $query = $query->where('semester', $semester);
        }

        $mahasiswa = $query->orderBy('nim', 'ASC')->findAll();
        $penilaianModel = new PenilaianModel();
        $jumlahKriteria = (new KriteriaModel())->countAllResults();

        foreach ($mahasiswa as &$item) {
            $count = $penilaianModel->where('mahasiswa_id', $item['id'])->countAllResults();
            $item['penilaian_lengkap'] = $jumlahKriteria > 0 && $count === $jumlahKriteria;
        }

        $hasilModel = new HasilModel();
        foreach ($mahasiswa as &$item) {
            $lastResult = $hasilModel->where('mahasiswa_id', $item['id'])
                ->orderBy('penilaian_ke', 'DESC')
                ->first();
            $item['hasil_tersimpan'] = $lastResult !== null;
            $item['status_hasil'] = $lastResult['status_lolos'] ?? null;
        }

        return view('hasil/index', [
            'mahasiswa' => $mahasiswa,
            'semesters' => $semesters,
            'jumlahKriteria' => $jumlahKriteria,
            'search' => $search,
            'semester' => $semester,
        ]);
    }

    public function getHistory(): string
    {
        $hasilModel = new HasilModel();
        
        // Get distinct penilaian_ke values
        $penilaianKeData = $hasilModel
            ->distinct()
            ->select('penilaian_ke')
            ->orderBy('penilaian_ke', 'DESC')
            ->findAll();

        $groupedByPenilaian = [];
        foreach ($penilaianKeData as $penilaianData) {
            $penilaianKe = (int) $penilaianData['penilaian_ke'];
            $rows = (new HasilModel())
                ->select('hasil.*, mahasiswa.nim, mahasiswa.nama')
                ->join('mahasiswa', 'mahasiswa.id = hasil.mahasiswa_id')
                ->where('penilaian_ke', $penilaianKe)
                ->orderBy('ranking', 'ASC')
                ->findAll();
            
            $groupedByPenilaian[$penilaianKe] = $rows;
        }

        return view('hasil/history', ['groupedByPenilaian' => $groupedByPenilaian]);
    }

    public function getCari(): string
    {
        return $this->getIndex();
    }

    public function postProses()
    {
        $skorMinimum = max(0, min(1, (float) $this->request->getPost('skor_minimum')));
        $selectedMahasiswaIds = array_map('intval', (array) $this->request->getPost('mahasiswa_ids') ?? []);

        log_message('debug', 'postProses: skorMinimum=' . $skorMinimum . ', selected=' . count($selectedMahasiswaIds));

        if (empty($selectedMahasiswaIds)) {
            log_message('error', 'postProses: No mahasiswa selected');
            return redirect()->back()->with('error', 'Silakan pilih minimal satu mahasiswa untuk diproses.');
        }

        $computed = $this->buildSawComputation($skorMinimum, $selectedMahasiswaIds);
        
        log_message('debug', 'postProses: computed keys=' . implode(',', array_keys($computed)));
        
        if (isset($computed['error'])) {
            log_message('error', 'postProses: computation error=' . $computed['error']);
            return redirect()->back()->with('error', (string) $computed['error']);
        }

        // Simpan hasil komputasi ke session untuk ditampilkan di preview halaman
        session()->set('saw_computation', $computed);
        log_message('debug', 'postProses: saved to session, redirecting to preview');
        
        return redirect()->to('/hasil/preview');
    }

    public function getPreview(): string
    {
        $computed = session()->get('saw_computation');
        
        if (!$computed) {
            return redirect()->to('/hasil')->with('error', 'Data perhitungan tidak ditemukan. Silakan ulangi proses.');
        }

        return view('hasil/preview', $computed);
    }

    public function postConfirm()
    {
        $computed = session()->get('saw_computation');
        
        if (!$computed) {
            return redirect()->back()->with('error', 'No computation data found.');
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

        // Clear session
        session()->remove('saw_computation');

        return redirect()->to('/hasil')->with('success', 'Perhitungan SAW selesai untuk penilaian ke-' . $penilaianKe . '.');
    }

    public function cancelProses()
    {
        // Clear session dan kembali ke hasil
        session()->remove('saw_computation');
        return redirect()->to('/hasil');
    }
}
