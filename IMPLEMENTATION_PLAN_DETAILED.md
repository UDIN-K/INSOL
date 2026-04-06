# Implementation Plan SAW - Step by Step

**Status Database:** ✅ Siap untuk implementasi

---

## Phase 1: Create SAWService Class

### File: `app/Services/SAWService.php`

**Tujuan:** Core business logic untuk perhitungan SAW

```php
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
     * Main process: orchestrate full SAW calculation
     *
     * @param int $penilaianKe - Periode/batch penilaian
     * @param string $threshold - Nilai minimum untuk lolos (default: 0.65)
     * @return array - Result dengan ranking, skor, dan status
     */
    public function process(int $penilaianKe, $threshold = null)
    {
        try {
            // Step 1: Ambil data kriteria dan penilaian
            $kriteria = $this->getData($penilaianKe);
            if (empty($kriteria['mahasiswa'])) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data penilaian untuk periode ini',
                ];
            }

            // Step 2: Normalisasi matriks
            $normalized = $this->normalizeMatrix($kriteria);

            // Step 3: Hitung preferensi (nilai akhir SAW)
            $preferences = $this->calculatePreferences($normalized);

            // Step 4: Ranking & determine lolos status
            $ranked = $this->rankAndFilter($preferences, $threshold);

            // Step 5: Simpan ke database
            $this->saveResults($penilaianKe, $ranked);

            return [
                'success' => true,
                'message' => 'SAW calculation completed successfully',
                'penilaian_ke' => $penilaianKe,
                'total_mahasiswa' => count($ranked),
                'ranking' => $ranked,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Step 1: Ambil data kriteria, bobot, dan nilai penilaian
     *
     * @param int $penilaianKe
     * @return array
     */
    private function getData(int $penilaianKe): array
    {
        // Ambil kriteria dengan bobot
        $kriteria = $this->kriteriaModel
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($kriteria)) {
            return [];
        }

        // Ambil data penilaian untuk periode ini
        $penilaian = $this->db->table('penilaian')
            ->select('mahasiswa_id, kriteria_id, nilai')
            ->where('penilaian_ke', $penilaianKe)
            ->get()
            ->getResultArray();

        if (empty($penilaian)) {
            return [];
        }

        // Format: $data['mahasiswa'][$mahasiswa_id][$kriteria_id] = nilai
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
     *
     * Rumus:
     * - Benefit: r(i,j) = x(i,j) / max(x(j))
     * - Cost: r(i,j) = min(x(j)) / x(i,j)
     *
     * @param array $data
     * @return array
     */
    private function normalizeMatrix(array $data): array
    {
        $kriteria = $data['kriteria'];
        $mahasiswa = $data['mahasiswa'];

        // Step 2.1: Hitung min/max per kriteria
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

        // Step 2.2: Normalisasi per mahasiswa & kriteria
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
                    // Benefit: semakin tinggi semakin baik
                    $rij = ($max > 0) ? $nilai / $max : 0;
                } else {
                    // Cost: semakin rendah semakin baik
                    $rij = ($nilai > 0) ? $min / $nilai : 0;
                }

                $normalized['mahasiswa'][$mId][$kId] = round($rij, 6);
            }
        }

        return $normalized;
    }

    /**
     * Step 3: Hitung nilai preferensi (skor SAW)
     *
     * Formula: P(i) = Σ(w(j) × r(i,j))
     *
     * @param array $normalized
     * @return array
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

                // Kalikan normalisasi dengan bobot
                $kontribusi = $bobot * $rij;
                $skor += $kontribusi;

                $details[$kId] = [
                    'kriteria' => $k['kriteria'],
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
     *
     * @param array $preferences
     * @param float|null $threshold
     * @return array
     */
    private function rankAndFilter(array $preferences, $threshold = null): array
    {
        // Urutkan dari skor tertinggi
        usort($preferences, function ($a, $b) {
            return $b['skor'] <=> $a['skor'];
        });

        // Set default threshold
        if ($threshold === null) {
            $threshold = 0.65;
        } else {
            $threshold = (float) $threshold;
        }

        // Assign ranking dan status
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
     * Step 5: Simpan hasil ke tabel hasil
     *
     * @param int $penilaianKe
     * @param array $ranked
     * @return void
     */
    private function saveResults(int $penilaianKe, array $ranked): void
    {
        // Bersihkan hasil lama untuk periode ini
        $this->hasilModel
            ->where('penilaian_ke', $penilaianKe)
            ->delete();

        // Insert hasil baru
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
```

---

## Phase 2: Create SAWHelper

### File: `app/Helpers/SAWHelper.php`

```php
<?php

if (!function_exists('formatSAWScore')) {
    /**
     * Format skor SAW ke 4 desimal
     */
    function formatSAWScore($score, $decimal = 4)
    {
        return number_format((float) $score, $decimal, '.', '');
    }
}

if (!function_exists('getAtributType')) {
    /**
     * Ambil tipe atribut dari kriteria
     */
    function getAtributType($kriteriaId)
    {
        $kriteriaModel = new \App\Models\KriteriaModel();
        $data = $kriteriaModel->find($kriteriaId);
        return $data['atribut'] ?? 'benefit';
    }
}

if (!function_exists('getMappedValue')) {
    /**
     * Mapping nilai raw ke nilai SAW berdasarkan detail_kriteria
     */
    function getMappedValue($kriteriaId, $rawValue)
    {
        $db = db_connect();
        $result = $db->table('detail_kriteria')
            ->select('nilai')
            ->where('kriteria_id', $kriteriaId)
            ->where('batas_bawah <=', $rawValue)
            ->where('batas_atas >=', $rawValue)
            ->first();

        return $result ? (float) $result['nilai'] : 0;
    }
}
```

---

## Phase 3: Update Models

### Ensure Models Have Proper Configuration

**Models needed:**

- ✅ KriteriaModel
- ✅ DetailKriteriaModel
- ✅ PenilaianModel
- ✅ HasilModel
- ✅ MahasiswaModel

---

## Phase 4: Create PenilaianController Method

### Update: `app/Controllers/PenilaianController.php`

**Add method:**

```php
public function hitungSAW()
{
    $penilaianKe = $this->request->getPost('penilaian_ke') ?? 1;
    $threshold = $this->request->getPost('threshold') ?? 0.65;

    $sawService = new \App\Services\SAWService();
    $result = $sawService->process((int) $penilaianKe, (float) $threshold);

    return $this->response->setJSON($result);
}
```

---

## Phase 5: Create HasilController

### File: `app/Controllers/HasilController.php`

```php
<?php

namespace App\Controllers;

use App\Models\HasilModel;
use App\Models\MahasiswaModel;

class HasilController extends BaseController
{
    protected $hasilModel;
    protected $mahasiswaModel;

    public function __construct()
    {
        $this->hasilModel = new HasilModel();
        $this->mahasiswaModel = new MahasiswaModel();
    }

    public function index()
    {
        $penilaianKe = $this->request->getGet('penilaian_ke') ?? 1;

        $hasil = $this->hasilModel
            ->select('hasil.*, mahasiswa.nama')
            ->join('mahasiswa', 'hasil.mahasiswa_id = mahasiswa.id')
            ->where('hasil.penilaian_ke', $penilaianKe)
            ->orderBy('hasil.ranking', 'ASC')
            ->findAll();

        return view('hasil/index', [
            'hasil' => $hasil,
            'penilaian_ke' => $penilaianKe,
        ]);
    }

    public function detail($hasilId)
    {
        $hasil = $this->hasilModel->find($hasilId);
        if (!$hasil) {
            throw \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $mahasiswa = $this->mahasiswaModel->find($hasil['mahasiswa_id']);

        return view('hasil/detail', [
            'hasil' => $hasil,
            'mahasiswa' => $mahasiswa,
        ]);
    }
}
```

---

## Phase 6: Create Routes

### Update: `app/Config/Routes.php`

```php
// SAW / Hasil routes
$routes->group('hasil', static function ($routes) {
    $routes->get('/', 'HasilController::index');
    $routes->get('detail/(:num)', 'HasilController::detail/$1');
});

$routes->post('penilaian/hitung-saw', 'PenilaianController::hitungSAW');
```

---

## Phase 7: Create Views

### File: `app/Views/hasil/index.php`

```php
<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container mt-4">
    <h2>Hasil Penilaian SAW - Periode <?= $penilaian_ke ?></h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Ranking</th>
                <th>Nama Mahasiswa</th>
                <th>Skor</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil as $row): ?>
            <tr>
                <td><?= $row['ranking'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= number_format($row['skor'], 6) ?></td>
                <td>
                    <span class="badge <?= $row['status_lolos'] === 'Lolos' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $row['status_lolos'] ?>
                    </span>
                </td>
                <td>
                    <a href="/hasil/detail/<?= $row['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
```

---

## Testing Plan

### 1. Unit Test SAW Calculation

```php
// tests/unit/SAWServiceTest.php
public function testNormalization()
{
    $service = new SAWService();
    // Test normalize matrix dengan benefit & cost
}

public function testPreferenceCalculation()
{
    // Test perhitungan P(i) = Σ(w × r)
}

public function testRanking()
{
    // Test ranking & status lolos
}
```

### 2. Integration Test

```php
// tests/integration/SAWIntegrationTest.php
public function testFullSAWProcess()
{
    // Insert test data
    // Run SAW process
    // Verify hasil di database
}
```

### 3. Manual Testing Checklist

- [ ] Database sudah punya data penilaian
- [ ] Call `SAWService->process(1)` berhasil
- [ ] Hasil tersimpan di tabel hasil
- [ ] Ranking urut dari skor tertinggi
- [ ] Status lolos sesuai threshold

---

## Execution Order

1. ✅ **Create Migration** - Add penilaian_ke to penilaian table
2. ⏳ **Create SAWService.php** - Core logic
3. ⏳ **Create SAWHelper.php** - Utilities
4. ⏳ **Update PenilaianController** - Add hitungSAW method
5. ⏳ **Create HasilController** - View results
6. ⏳ **Create Routes** - URL endpoints
7. ⏳ **Create Views** - UI untuk hasil
8. ⏳ **Testing** - Unit & integration tests
9. ⏳ **Dokumentasi** - Function docs

---

## Sample Data untuk Testing

```sql
-- Insert test mahasiswa
INSERT INTO mahasiswa (nim, nama, ipk, penghasilan_ortu, jumlah_tanggungan, prestasi_non_akademik) VALUES
('2020001', 'Budi Santoso', 3.75, 450000, 4, 'nasional'),
('2020002', 'Ani Wijaya', 3.50, 1000000, 3, 'provinsi'),
('2020003', 'Citra Dewi', 3.20, 1500000, 2, 'universitas');

-- Insert penilaian untuk period 1
INSERT INTO penilaian (mahasiswa_id, kriteria_id, penilaian_ke, nilai) VALUES
-- Budi: IPK 3.75 → 1.0, Gaji 450K → 0.25, Tanggungan 4 → 0.75, Prestasi Nasional → 1.0
(1, 1, 1, 1.0000),
(1, 2, 1, 0.2500),
(1, 3, 1, 0.7500),
(1, 4, 1, 1.0000),
-- Ani: IPK 3.50 → 0.75, Gaji 1M → 0.5, Tanggungan 3 → 0.5, Prestasi Provinsi → 0.75
(2, 1, 1, 0.7500),
(2, 2, 1, 0.5000),
(2, 3, 1, 0.5000),
(2, 4, 1, 0.7500),
-- Citra: IPK 3.20 → 0.5, Gaji 1.5M → 0.75, Tanggungan 2 → 0.25, Prestasi Univ → 0.5
(3, 1, 1, 0.5000),
(3, 2, 1, 0.7500),
(3, 3, 1, 0.2500),
(3, 4, 1, 0.5000);

-- Expected hasil:
-- Budi:  P = 0.35×1.0 + 0.25×0.25 + 0.20×0.75 + 0.20×1.0 = 0.35 + 0.0625 + 0.15 + 0.20 = 0.7625 → Ranking 1
-- Ani:   P = 0.35×0.75 + 0.25×0.5 + 0.20×0.5 + 0.20×0.75 = 0.2625 + 0.125 + 0.10 + 0.15 = 0.6375 → Ranking 2
-- Citra: P = 0.35×0.5 + 0.25×0.75 + 0.20×0.25 + 0.20×0.5 = 0.175 + 0.1875 + 0.05 + 0.10 = 0.5125 → Ranking 3
```

---

## Next Actions

Mau saya langsung buat:

1. SAWService.php?
2. Atau step-by-step sesuai urutan di atas?

Siap execute! 🚀
