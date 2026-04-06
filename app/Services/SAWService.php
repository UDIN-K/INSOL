<?php

namespace App\Services;

use App\Models\KriteriaModel;
use App\Models\DetailKriteriaModel;
use App\Models\PenilaianModel;
use App\Models\HasilModel;
use App\Models\MahasiswaModel;

class SAWService
{
    protected $kriteriaModel;
    protected $detailKriteriaModel;
    protected $penilaianModel;
    protected $hasilModel;
    protected $mahasiswaModel;
    protected $db;

    public function __construct()
    {
        $this->kriteriaModel = new KriteriaModel();
        $this->detailKriteriaModel = new DetailKriteriaModel();
        $this->penilaianModel = new PenilaianModel();
        $this->hasilModel = new HasilModel();
        $this->mahasiswaModel = new MahasiswaModel();
        $this->db = db_connect();
    }

    /**
     * Main process: orchestrate full SAW calculation dengan step-by-step tracking
     * 
     * @param int $penilaianKe - Periode/batch penilaian
     * @param float $threshold - Nilai minimum untuk lolos
     * @param array $selectedMahasiswa - List mahasiswa ID yang dipilih (optional)
     * @return array - Complete calculation breakdown
     */
    public function process(int $penilaianKe, float $threshold = null, array $selectedMahasiswa = [])
    {
        try {
            // Step 1: Ambil data kriteria dan penilaian
            $data = $this->getData($penilaianKe, $selectedMahasiswa);
            if (empty($data['mahasiswa'])) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data penilaian untuk periode ini',
                ];
            }

            // Ambil nama mahasiswa untuk display
            $mahasiswaNames = $this->getMahasiswaNames(array_keys($data['mahasiswa']));

            // Step 2: Matriks Keputusan (X)
            $step1 = [
                'title' => 'Matriks Keputusan (X)',
                'description' => 'Data nilai penilaian setiap mahasiswa per kriteria',
                'data' => $this->buildDecisionMatrix($data),
                'mahasiswa_names' => $mahasiswaNames,
            ];

            // Step 3: Normalisasi matriks
            $normalized = $this->normalizeMatrix($data);
            
            // Step 2: Matriks Normalisasi (R)
            $step2 = [
                'title' => 'Matriks Normalisasi (R)',
                'description' => 'Nilai ternormalisasi (0-1) berdasarkan benefit/cost',
                'data' => $this->buildNormalizedMatrix($normalized),
                'mahasiswa_names' => $mahasiswaNames,
                'min_max' => $normalized['minMax'],
            ];

            // Step 3: Detail Perhitungan dengan Bobot
            $step3 = [
                'title' => 'Perhitungan dengan Bobot (R × W)',
                'description' => 'Nilai normalisasi dikalikan dengan bobot masing-masing kriteria',
                'data' => $this->buildWeightedCalculation($normalized),
                'mahasiswa_names' => $mahasiswaNames,
            ];

            // Step 4: Hitung preferensi (nilai akhir SAW)
            $preferences = $this->calculatePreferences($normalized);
            
            $step4 = [
                'title' => 'Nilai Preferensi (P)',
                'description' => 'Total penjumlahan: P(i) = Σ(w(j) × r(i,j))',
                'data' => $this->buildPreferenceData($preferences),
                'mahasiswa_names' => $mahasiswaNames,
            ];

            // Step 5: Ranking & determine lolos status
            $ranked = $this->rankAndFilter($preferences, $threshold);
            
            $step5 = [
                'title' => 'Ranking Hasil Akhir',
                'description' => 'Urutan dari skor tertinggi ke terendah dengan status lolos',
                'data' => $this->buildRankingTable($ranked, $mahasiswaNames),
            ];

            // Step 6: Simpan ke database
            $this->saveResults($penilaianKe, $ranked);

            return [
                'success' => true,
                'message' => 'SAW calculation completed successfully',
                'penilaian_ke' => $penilaianKe,
                'total_mahasiswa' => count($ranked),
                'steps' => [
                    $step1,
                    $step2,
                    $step3,
                    $step4,
                    $step5,
                ],
                'final_ranking' => $ranked,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil nama mahasiswa untuk display
     */
    private function getMahasiswaNames(array $mahasiswaIds): array
    {
        $mahasiswa = $this->mahasiswaModel->whereIn('id', $mahasiswaIds)->findAll();
        $names = [];
        foreach ($mahasiswa as $m) {
            $names[$m['id']] = $m['nama'];
        }
        return $names;
    }

    /**
     * Bangun tabel Matriks Keputusan (X)
     */
    private function buildDecisionMatrix(array $data): array
    {
        $kriteria = $data['kriteria'];
        $mahasiswa = $data['mahasiswa'];

        return [
            'headers' => array_map(fn($k) => $k['kriteria'] . ' (' . $k['kode'] . ')', $kriteria),
            'rows' => $mahasiswa,
        ];
    }

    /**
     * Bangun tabel Matriks Normalisasi (R)
     */
    private function buildNormalizedMatrix(array $normalized): array
    {
        $kriteria = $normalized['kriteria'];
        $mahasiswa = $normalized['mahasiswa'];

        return [
            'headers' => array_map(fn($k) => $k['kriteria'] . ' (' . $k['kode'] . ')', $kriteria),
            'rows' => $mahasiswa,
        ];
    }

    /**
     * Bangun detail perhitungan bobot (r × w)
     */
    private function buildWeightedCalculation(array $normalized): array
    {
        $kriteria = $normalized['kriteria'];
        $mahasiswa = $normalized['mahasiswa'];

        $result = [];
        foreach ($mahasiswa as $mId => $mnilai) {
            $result[$mId] = [];
            foreach ($kriteria as $k) {
                $kId = $k['id'];
                $rij = $mnilai[$kId] ?? 0;
                $bobot = (float) $k['bobot'];
                $kontribusi = $bobot * $rij;

                $result[$mId][$kId] = [
                    'kriteria' => $k['kriteria'],
                    'rij' => round($rij, 4),
                    'bobot' => round($bobot, 4),
                    'kontribusi' => round($kontribusi, 6),
                ];
            }
        }

        return $result;
    }

    /**
     * Bangun data nilai preferensi dengan breakdown
     */
    private function buildPreferenceData(array $preferences): array
    {
        $result = [];
        foreach ($preferences as $mId => $pref) {
            $result[$mId] = [
                'skor' => $pref['skor'],
                'breakdown' => $pref['details'],
            ];
        }
        return $result;
    }

    /**
     * Bangun tabel ranking final
     */
    private function buildRankingTable(array $ranked, array $mahasiswaNames): array
    {
        $result = [];
        foreach ($ranked as $row) {
            $result[] = [
                'ranking' => $row['ranking'],
                'nama' => $mahasiswaNames[$row['mahasiswa_id']] ?? 'Unknown',
                'skor' => round($row['skor'], 6),
                'status_lolos' => $row['status_lolos'],
            ];
        }
        return $result;
    }

    /**
     * Step 1: Ambil data kriteria, bobot, dan nilai penilaian
     */
    private function getData(int $penilaianKe, array $selectedMahasiswa = []): array
    {
        $kriteria = $this->kriteriaModel
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($kriteria)) {
            return [];
        }

        $query = $this->db->table('penilaian')
            ->select('mahasiswa_id, kriteria_id, nilai')
            ->where('penilaian_ke', $penilaianKe);

        // Filter by selected mahasiswa jika ada
        if (!empty($selectedMahasiswa)) {
            $query->whereIn('mahasiswa_id', $selectedMahasiswa);
        }

        $penilaian = $query->get()->getResultArray();

        if (empty($penilaian)) {
            return [];
        }

        $data['kriteria'] = $kriteria;
        $data['mahasiswa'] = [];

        foreach ($penilaian as $row) {
            $mahasiswa_id = $row['mahasiswa_id'];
            $kriteria_id = $row['kriteria_id'];
            $nilai = (float) $row['nilai'];

            if (!isset($data['mahasiswa'][$mahasiswa_id])) {
                $data['mahasiswa'][$mahasiswa_id] = [];
            }

            $data['mahasiswa'][$mahasiswa_id][$kriteria_id] = $nilai;
        }

        return $data;
    }

    /**
     * Step 2: Normalisasi matriks keputusan
     */
    private function normalizeMatrix(array $data): array
    {
        $kriteria = $data['kriteria'];
        $mahasiswa = $data['mahasiswa'];

        // Hitung min/max per kriteria
        $minMax = [];
        foreach ($kriteria as $k) {
            $kId = $k['id'];
            $minMax[$kId] = ['min' => PHP_FLOAT_MAX, 'max' => -PHP_FLOAT_MAX];

            foreach ($mahasiswa as $mId => $mnilai) {
                if (isset($mnilai[$kId])) {
                    $nilai = $mnilai[$kId];
                    $minMax[$kId]['min'] = min($minMax[$kId]['min'], $nilai);
                    $minMax[$kId]['max'] = max($minMax[$kId]['max'], $nilai);
                }
            }
        }

        // Normalisasi per mahasiswa & kriteria
        $normalized['kriteria'] = $kriteria;
        $normalized['minMax'] = $minMax;
        $normalized['mahasiswa'] = [];

        foreach ($mahasiswa as $mId => $mnilai) {
            $normalized['mahasiswa'][$mId] = [];

            foreach ($kriteria as $k) {
                $kId = $k['id'];
                $atribut = $k['atribut'];

                if (!isset($mnilai[$kId])) {
                    $normalized['mahasiswa'][$mId][$kId] = 0;
                    continue;
                }

                $nilai = $mnilai[$kId];
                $min = $minMax[$kId]['min'];
                $max = $minMax[$kId]['max'];

                // Hindari divide by zero
                if ($atribut === 'benefit') {
                    $rij = ($max > 0) ? $nilai / $max : 0;
                } else {
                    $rij = ($nilai > 0) ? $min / $nilai : 0;
                }

                $normalized['mahasiswa'][$mId][$kId] = round($rij, 6);
            }
        }

        return $normalized;
    }

    /**
     * Step 3: Hitung nilai preferensi
     */
    private function calculatePreferences(array $normalized): array
    {
        $kriteria = $normalized['kriteria'];
        $mahasiswa = $normalized['mahasiswa'];

        $preferences = [];

        foreach ($mahasiswa as $mId => $mnilai) {
            $skor = 0;
            $details = [];

            foreach ($kriteria as $k) {
                $kId = $k['id'];
                $bobot = (float) $k['bobot'];
                $rij = $mnilai[$kId] ?? 0;

                $kontribusi = $bobot * $rij;
                $skor += $kontribusi;

                $details[$kId] = [
                    'kriteria' => $k['kriteria'],
                    'kode' => $k['kode'],
                    'bobot' => $bobot,
                    'nilai_normalisasi' => $rij,
                    'kontribusi' => round($kontribusi, 6),
                ];
            }

            $preferences[$mId] = [
                'mahasiswa_id' => $mId,
                'skor' => round($skor, 6),
                'details' => $details,
            ];
        }

        return $preferences;
    }

    /**
     * Step 4: Ranking dan tentukan status lolos
     */
    private function rankAndFilter(array $preferences, $threshold = null): array
    {
        usort($preferences, function ($a, $b) {
            return $b['skor'] <=> $a['skor'];
        });

        if ($threshold === null) {
            $threshold = 0.65;
        } else {
            $threshold = (float) $threshold;
        }

        $ranked = [];
        foreach ($preferences as $idx => $pref) {
            $ranking = $idx + 1;
            $status = ($pref['skor'] >= $threshold) ? 'Lolos' : 'Tidak Lolos';

            $ranked[] = [
                'mahasiswa_id' => $pref['mahasiswa_id'],
                'skor' => $pref['skor'],
                'ranking' => $ranking,
                'status_lolos' => $status,
                'details' => $pref['details'],
            ];
        }

        return $ranked;
    }

    /**
     * Step 5: Simpan hasil ke database
     */
    private function saveResults(int $penilaianKe, array $ranked): void
    {
        $this->hasilModel
            ->where('penilaian_ke', $penilaianKe)
            ->delete();

        foreach ($ranked as $row) {
            $this->hasilModel->insert([
                'mahasiswa_id' => $row['mahasiswa_id'],
                'penilaian_ke' => $penilaianKe,
                'skor' => $row['skor'],
                'ranking' => $row['ranking'],
                'status_lolos' => $row['status_lolos'],
            ]);
        }
    }
}
