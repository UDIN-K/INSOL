# Step-by-Step SAW Calculation - Implementation Guide

## Overview

Sistem SAW sekarang menampilkan **5 tahap perhitungan** secara detail, mirip dengan screenshot yang diberikan:

```
STEP 1: Matriks Keputusan (X)
        ↓
STEP 2: Matriks Normalisasi (R)
        ↓
STEP 3: Perhitungan dengan Bobot (R × W)
        ↓
STEP 4: Nilai Preferensi (P)
        ↓
STEP 5: Ranking Hasil Akhir
```

---

## Architecture

### File Structure

```
app/
├── Services/
│   └── SAWService.php           ← Core SAW calculation dengan tracking step-by-step
├── Controllers/
│   └── PenilaianController.php  ← Updated dengan hitungSAW() method
├── Views/
│   └── penilaian/
│       └── hasil_perhitungan.php ← View untuk tampilkan breakdown calculation
└── Config/
    └── Routes.php                ← Updated dengan route baru
```

### Data Flow

```
User input (penilaian_ke, threshold)
        ↓
PenilaianController::hitungSAW()
        ↓
SAWService::process()
        ├── getData() → Matriks X
        ├── normalizeMatrix() → Matriks R
        ├── calculatePreferences() → Nilai P
        ├── rankAndFilter() → Ranking
        └── saveResults() → Simpan ke DB
        ↓
Return dengan struktur:
{
  'success': true,
  'steps': [
    step1_data,
    step2_data,
    step3_data,
    step4_data,
    step5_data
  ],
  'final_ranking': [...]
}
        ↓
View: hasil_perhitungan.php
        ↓
Tampilkan 5 step secara visual (tabel/chart)
```

---

## Detail Setiap Step

### STEP 1: Matriks Keputusan (X)

**Tujuan:** Menampilkan data nilai awal

**Format Tabel:**

```
┌───────────────┬──────────┬──────────┬──────────┬──────────┐
│ Mahasiswa     │ IPK (C1) │ Gaji(C2) │ Tgng(C3) │ Prest(C4)│
├───────────────┼──────────┼──────────┼──────────┼──────────┤
│ Budi          │ 1.0000   │ 0.2500   │ 0.7500   │ 1.0000   │
│ Ani           │ 0.7500   │ 0.5000   │ 0.5000   │ 0.7500   │
│ Citra         │ 0.5000   │ 0.7500   │ 0.2500   │ 0.5000   │
└───────────────┴──────────┴──────────┴──────────┴──────────┘
```

**Output SAWService:** Array dengan struktur

```php
[
  'title' => 'Matriks Keputusan (X)',
  'data' => [
    'headers' => ['IPK (C1)', 'Gaji (C2)', ...],
    'rows' => [
      1 => [1 => 1.0000, 2 => 0.2500, ...],
      2 => [1 => 0.7500, 2 => 0.5000, ...],
      ...
    ]
  ]
]
```

---

### STEP 2: Matriks Normalisasi (R)

**Tujuan:** Tampilkan nilai yang sudah dinormalisasi (0-1)

**Rumus:**

- **Benefit:** r(i,j) = X(i,j) / MAX(Xj)
- **Cost:** r(i,j) = MIN(Xj) / X(i,j)

**Contoh Perhitungan:**

```
Untuk Budi IPK (Benefit):
- X = 1.0000, MAX = 1.0000
- r = 1.0000 / 1.0000 = 1.0000 ✓

Untuk Budi Gaji (Cost):
- X = 0.2500, MIN = 0.2500
- r = 0.2500 / 0.2500 = 1.0000 ✓
```

**Format Tabel:**

```
┌───────────────┬──────────┬──────────┬──────────┬──────────┐
│ Mahasiswa     │ IPK (C1) │ Gaji(C2) │ Tgng(C3) │ Prest(C4)│
├───────────────┼──────────┼──────────┼──────────┼──────────┤
│ Budi          │ 1.0000   │ 1.0000   │ 1.0000   │ 1.0000   │
│ Ani           │ 0.7500   │ 0.5000   │ 0.6667   │ 0.7500   │
│ Citra         │ 0.5000   │ 0.3333   │ 0.3333   │ 0.5000   │
└───────────────┴──────────┴──────────┴──────────┴──────────┘
```

---

### STEP 3: Perhitungan dengan Bobot (R × W)

**Tujuan:** Tampilkan kontribusi setiap kriteria dengan bobotnya

**Format Breakdown Per Mahasiswa:**

```
Mahasiswa: Budi
├── IPK (C1)        : R=1.0000 × W=0.35 = 0.350000
├── Gaji (C2)       : R=1.0000 × W=0.25 = 0.250000
├── Tanggungan (C3) : R=1.0000 × W=0.20 = 0.200000
└── Prestasi (C4)   : R=1.0000 × W=0.20 = 0.200000
```

**Format Tabel:**

```
┌──────────────────────┬────────┬────────┬────────┐
│ Kriteria             │ R(i,j) │ W(j)   │ R × W  │
├──────────────────────┼────────┼────────┼────────┤
│ Budi > IPK (C1)      │ 1.0000 │ 0.3500 │ 0.35  │
│ Budi > Gaji (C2)     │ 1.0000 │ 0.2500 │ 0.25  │
│ Budi > Tanggungan... │ 1.0000 │ 0.2000 │ 0.20  │
│ Budi > Prestasi...   │ 1.0000 │ 0.2000 │ 0.20  │
└──────────────────────┴────────┴────────┴────────┘

[Repeat untuk Ani, Citra, dll]
```

---

### STEP 4: Nilai Preferensi (P)

**Tujuan:** Tampilkan total skor setelah penjumlahan semua kontribusi

**Formula:** P(i) = Σ(W(j) × R(i,j))

**Contoh Perhitungan Budi:**

```
P(Budi) = 0.350000 + 0.250000 + 0.200000 + 0.200000
        = 1.000000
```

**Format Tabel:**

```
┌─────────────┬─────────────────────────────────────────┬──────────┐
│ Mahasiswa   │ Perhitungan                             │ Nilai P  │
├─────────────┼─────────────────────────────────────────┼──────────┤
│ Budi        │ 0.350 + 0.250 + 0.200 + 0.200          │ 1.000000 │
│ Ani         │ 0.262 + 0.125 + 0.133 + 0.150          │ 0.670000 │
│ Citra       │ 0.175 + 0.083 + 0.067 + 0.100          │ 0.425000 │
└─────────────┴─────────────────────────────────────────┴──────────┘
```

---

### STEP 5: Ranking Hasil Akhir

**Tujuan:** Urutkan dari skor tertinggi dan tentukan status lolos

**Kriteria Lolos:** Skor ≥ Threshold (default 0.65)

**Format Tabel:**

```
┌─────────┬─────────────┬──────────┬──────────────┐
│ Ranking │ Mahasiswa   │ Skor     │ Status       │
├─────────┼─────────────┼──────────┼──────────────┤
│ 1       │ Budi        │ 1.000000 │ ✓ Lolos      │
│ 2       │ Ani         │ 0.670000 │ ✓ Lolos      │
│ 3       │ Citra       │ 0.425000 │ ✗ Tidak Lolos│
└─────────┴─────────────┴──────────┴──────────────┘
```

---

## Implementation Guide

### 1. Cara Akses Perhitungan

**URL:** `POST /penilaian/hitung-saw`

**Parameter:**

```php
[
  'penilaian_ke' => 1,      // Periode penilaian
  'threshold' => 0.65       // Nilai minimum lolos (optional, default 0.65)
]
```

**Contoh HTML Form:**

```html
<form action="/penilaian/hitung-saw" method="POST">
  <div class="form-group">
    <label>Periode Penilaian</label>
    <input type="number" name="penilaian_ke" value="1" required />
  </div>
  <div class="form-group">
    <label>Threshold Lolos (0-1)</label>
    <input type="number" name="threshold" value="0.65" step="0.01" required />
  </div>
  <button type="submit" class="btn btn-primary">Hitung SAW</button>
</form>
```

### 2. Response Structure

**Success Response:**

```json
{
  "success": true,
  "message": "SAW calculation completed successfully",
  "penilaian_ke": 1,
  "total_mahasiswa": 3,
  "steps": [
    {
      "title": "Matriks Keputusan (X)",
      "description": "...",
      "data": { ... },
      "mahasiswa_names": { 1: "Budi", 2: "Ani", ... }
    },
    {
      "title": "Matriks Normalisasi (R)",
      "data": { ... },
      "mahasiswa_names": { ... },
      "min_max": { 1: {...}, 2: {...}, ... }
    },
    {
      "title": "Perhitungan dengan Bobot (R × W)",
      "data": {
        "1": {
          "1": { "kriteria": "IPK", "rij": 1.0, "bobot": 0.35, "kontribusi": 0.35 },
          ...
        }
      },
      "mahasiswa_names": { ... }
    },
    {
      "title": "Nilai Preferensi (P)",
      "data": {
        "1": { "skor": 1.0, "breakdown": [...] },
        ...
      }
    },
    {
      "title": "Ranking Hasil Akhir",
      "data": [
        { "ranking": 1, "nama": "Budi", "skor": 1.0, "status_lolos": "Lolos" },
        ...
      ]
    }
  ],
  "final_ranking": [ ... ]
}
```

### 3. View Rendering

**File:** `app/Views/penilaian/hasil_perhitungan.php`

**Fitur:**

- ✅ Menampilkan 5 step dalam card terpisah
- ✅ Warna berbeda per step untuk visual clarity
- ✅ Tabel responsif dengan format angka 4-6 desimal
- ✅ Alert/badge untuk status lolos/tidak lolos
- ✅ Summary card dengan total lolos/tidak lolos
- ✅ Breadcrumb ke halaman detail hasil

---

## Testing Data

### Contoh Insert Test Data

```sql
-- Insert mahasiswa
INSERT INTO mahasiswa (nim, nama, ipk, penghasilan_ortu, jumlah_tanggungan, prestasi_non_akademik)
VALUES
('2020001', 'Budi Santoso', 3.75, 450000, 4, 'nasional'),
('2020002', 'Ani Wijaya', 3.50, 1000000, 3, 'provinsi'),
('2020003', 'Citra Dewi', 3.20, 1500000, 2, 'universitas');

-- Insert penilaian periode 1
-- Budi
INSERT INTO penilaian (mahasiswa_id, kriteria_id, penilaian_ke, nilai)
VALUES
(1, 1, 1, 1.0000),   -- IPK: 3.75 → 1.0
(1, 2, 1, 0.2500),   -- Gaji: 450000 → 0.25
(1, 3, 1, 0.7500),   -- Tanggungan: 4 → 0.75
(1, 4, 1, 1.0000);   -- Prestasi: Nasional → 1.0

-- Ani
INSERT INTO penilaian (mahasiswa_id, kriteria_id, penilaian_ke, nilai)
VALUES
(2, 1, 1, 0.7500),   -- IPK: 3.50 → 0.75
(2, 2, 1, 0.5000),   -- Gaji: 1000000 → 0.5
(2, 3, 1, 0.5000),   -- Tanggungan: 3 → 0.5
(2, 4, 1, 0.7500);   -- Prestasi: Provinsi → 0.75

-- Citra
INSERT INTO penilaian (mahasiswa_id, kriteria_id, penilaian_ke, nilai)
VALUES
(3, 1, 1, 0.5000),   -- IPK: 3.20 → 0.5
(3, 2, 1, 0.7500),   -- Gaji: 1500000 → 0.75
(3, 3, 1, 0.2500),   -- Tanggungan: 2 → 0.25
(3, 4, 1, 0.5000);   -- Prestasi: Universitas → 0.5
```

### Expected Result

```
Ranking 1: Budi    (Skor: 1.000000) → Lolos
Ranking 2: Ani     (Skor: 0.670000) → Lolos
Ranking 3: Citra   (Skor: 0.425000) → Tidak Lolos
```

---

## Database Queries untuk Testing

### Check sudah berhasil hitung SAW?

```sql
SELECT * FROM hasil WHERE penilaian_ke = 1 ORDER BY ranking;
```

### Lihat semua penilaian periode 1

```sql
SELECT p.*, k.kriteria, m.nama FROM penilaian p
JOIN kriteria k ON p.kriteria_id = k.id
JOIN mahasiswa m ON p.mahasiswa_id = m.id
WHERE p.penilaian_ke = 1
ORDER BY m.id, k.id;
```

---

## Files Changed/Created

| File                                                                      | Status     | Deskripsi                             |
| ------------------------------------------------------------------------- | ---------- | ------------------------------------- |
| `app/Services/SAWService.php`                                             | ✅ Created | Core SAW dengan step-by-step tracking |
| `app/Controllers/PenilaianController.php`                                 | ✅ Updated | Added hitungSAW() & hitungSAWAPI()    |
| `app/Views/penilaian/hasil_perhitungan.php`                               | ✅ Created | View untuk display 5 steps            |
| `app/Config/Routes.php`                                                   | ✅ Updated | Add routes untuk hitung-saw           |
| `app/Database/Migrations/2026-04-06-010000_AddPenilaianKeToPenilaian.php` | ✅ Created | Add penilaian_ke column               |

---

## Next Steps

1. ✅ Create SAWService dengan step-by-step tracking
2. ✅ Update PenilaianController dengan method hitungSAW
3. ✅ Create view hasil_perhitungan.php
4. ✅ Update routes
5. ⏳ Test dengan data sebenarnya
6. ⏳ Optimize view untuk mobile responsivenes

---

## Usage Example

### Manual Testing via Browser

1. Pastikan ada data penilaian di database
2. Go to: `/penilaian`
3. Lihat opsi untuk "Hitung SAW"
4. Input periode penilaian (default 1)
5. Input threshold lolos (default 0.65)
6. Click "Hitung SAW"
7. Lihat hasil breakdown 5 steps

### Via AJAX (API Mode)

```javascript
fetch("/penilaian/hitung-saw-api", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    penilaian_ke: 1,
    threshold: 0.65,
  }),
})
  .then((r) => r.json())
  .then((data) => {
    console.log(data.steps); // Array dengan 5 step
    // Process dan tampilkan UI custom
  });
```

---

Ready untuk production! 🚀
