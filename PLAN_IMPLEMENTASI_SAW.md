# Plan Implementasi SAW (Simple Additive Weighting) untuk SPK Beasiswa

## 1. Overview

Implementasi Sistem Pendukung Keputusan (SPK) dengan metode **Simple Additive Weighting (SAW)** untuk seleksi penerima beasiswa mahasiswa. Sistem ini akan memproses kriteria dan nilai-nilai mahasiswa untuk menghasilkan ranking dan keputusan kelayakan.

---

## 2. Teori SAW (dari referensi)

### 2.1 Prinsip Dasar

Metode SAW mencari penjumlahan terbobot dari rating kinerja pada setiap alternatif untuk semua atribut.

**Formula Akhir SAW:**

```
P(i) = Σ(w(j) × r(i,j))

Dimana:
- P(i)  = Nilai preferensi/skor akhir alternatif ke-i
- w(j)  = Bobot kriteria ke-j
- r(i,j) = Nilai normalisasi alternatif i terhadap kriteria j
```

### 2.2 Langkah-Langkah Perhitungan

#### Step 1: Buat Matriks Keputusan (X)

```
Kumpulkan nilai setiap mahasiswa (alternatif) untuk setiap kriteria
Contoh:
              IPK   Penghasilan  Tanggungan  Prestasi
Mahasiswa 1:  3.5      500000        2        diploma
Mahasiswa 2:  3.2     1500000        3       not
Mahasiswa 3:  3.8      200000        4       tier_nsd
```

#### Step 2: Normalisasi Matriks (R)

Formula tergantung jenis atribut:

**Untuk atribut BENEFIT (semakin tinggi semakin baik):**

```
r(i,j) = x(i,j) / max(x(j))
```

**Untuk atribut COST (semakin rendah semakin baik):**

```
r(i,j) = min(x(j)) / x(i,j)
```

Hasil normalisasi: nilai antara 0 dan 1

#### Step 3: Hitung Nilai Preferensi (P)

```
P(i) = w(1)×r(i,1) + w(2)×r(i,2) + w(3)×r(i,3) + ...
```

Kalikan setiap nilai normalisasi dengan bobot, lalu jumlahkan semuanya.

#### Step 4: Ranking

Urutkan dari nilai P(i) terbesar ke terkecil. Mahasiswa dengan P terbesar menang.

---

## 3. Database Structure (Existing)

### 3.1 Tabel Kriteria

```sql
CREATE TABLE kriteria (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kode VARCHAR(10) UNIQUE,
  kriteria VARCHAR(100),
  bobot DECIMAL(8,4),              -- Bobot kriteria (contoh: 0.25, 0.30)
  atribut ENUM('benefit', 'cost')  -- Jenis atribut
);
```

**Data Seed:**
| id | kode | kriteria | bobot | atribut |
|----|------|----------|-------|---------|
| 1 | C1 | IPK | 0.25 | benefit |
| 2 | C2 | Penghasilan Orang Tua | 0.25 | cost |
| 3 | C3 | Jumlah Tanggungan | 0.25 | benefit |
| 4 | C4 | Prestasi Non Akademik | 0.25 | benefit |

### 3.2 Tabel Detail Kriteria (untuk mapping nilai)

```sql
CREATE TABLE detail_kriteria (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kriteria_id INT,
  sub_kriteria VARCHAR(150),
  nilai DECIMAL(8,4)  -- Nilai yang dipetakan untuk SAW
);
```

**Contoh Data:**

- IPK 3.51-4.00 → nilai 1.0000
- IPK 3.01-3.50 → nilai 0.7500
- Penghasilan 0-500000 → nilai 0.2500
- Tanggungan 4+ → nilai 0.7500

### 3.3 Tabel Mahasiswa

```sql
CREATE TABLE mahasiswa (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(100),
  ipk DECIMAL(4,2),
  penghasilan_ortu BIGINT,
  jumlah_tanggungan INT,
  prestasi_non_akademik ENUM('...','...','...')
);
```

### 3.4 Tabel Penilaian

```sql
CREATE TABLE penilaian (
  id INT PRIMARY KEY AUTO_INCREMENT,
  mahasiswa_id INT,
  kriteria_id INT,
  nilai DECIMAL(8,4),  -- Nilai yang sudah dinormalisasi dari sub_kriteria
  created_at DATETIME
);
```

### 3.5 Tabel Hasil

```sql
CREATE TABLE hasil (
  id INT PRIMARY KEY AUTO_INCREMENT,
  mahasiswa_id INT,
  penilaian_ke INT,  -- Batch/periode penilaian
  skor DECIMAL(10,4), -- Hasil akhir SAW
  ranking INT,        -- Urutan ranking
  status_lolos ENUM('lolos', 'tidak_lolos'),
  created_at DATETIME
);
```

---

## 4. Architecture & File Structure

### 4.1 File yang akan dibuat/dimodifikasi

```
app/
├── Services/
│   └── SAWService.php              [BARU] - Core SAW calculation
├── Controllers/
│   ├── PenilaianController.php      [UPDATE] - tambah method hitung SAW
│   └── HasilController.php          [BARU/UPDATE] - tampilkan hasil SAW
├── Models/
│   ├── KriteriaModel.php            [EXISTING]
│   ├── DetailKriteriaModel.php      [EXISTING]
│   ├── PenilaianModel.php           [EXISTING]
│   ├── HasilModel.php               [EXISTING]
│   └── MahasiswaModel.php           [EXISTING]
└── Helpers/
    └── SAWHelper.php                [BARU] - helper functions
```

---

## 5. Implementation Plan

### 5.1 Phase 1: Create SAWService.php

**Location:** `app/Services/SAWService.php`

**Functionality:**

```php
class SAWService {

  // 1. getData()
  //    - Ambil data kriteria dan bobot
  //    - Ambil data penilaian mahasiswa
  //    - Ambil data detail kriteria untuk mapping

  // 2. normalizationMatrix()
  //    - Hitung max/min per kriteria
  //    - Normalisasi berdasarkan atribut (benefit/cost)
  //    - Return matriks R

  // 3. calculatePreference()
  //    - Kalikan R × bobot
  //    - Jumlahkan untuk setiap mahasiswa
  //    - Return array P(i)

  // 4. ranking()
  //    - Urutkan dari nilai terbesar
  //    - Return ranking array

  // 5. process()
  //    - Orchestrate: getData → normalize → calculate → rank
  //    - Save hasil ke database
  //    - Return report

  // 6. getMappedValue()
  //    - Helper: map nilai mahasiswa ke nilai SAW via detail_kriteria
}
```

### 5.2 Phase 2: Create SAWHelper.php

**Location:** `app/Helpers/SAWHelper.php`

**Utility Functions:**

```php
// Fungsi bantuan:
- formatNumber(num, decimal)   // Format output
- getAtributType(kriteria_id)  // Ambil tipe atribut
- calculateStatistics(data)    // Hitung min/max/avg
```

### 5.3 Phase 3: Update PenilaianController.php

**Add Method:**

```php
public function hitungSAW(penilaian_ke) {
  // 1. Validate penilaian_ke exists
  // 2. Call SAWService->process(penilaian_ke)
  // 3. Return success/error message
}
```

### 5.4 Phase 4: Update/Create HasilController.php

**Add Methods:**

```php
public function index() {
  // Tampilkan semua hasil SAW dengan ranking
}

public function detail(penilaian_ke) {
  // Tampilkan detail hasil per penilaian batch
}
```

---

## 6. Data Flow

### 6.1 Input Data

```
Sumber Data:
1. Tabel KRITERIA: kode, kriteria, bobot, atribut
2. Tabel DETAIL_KRITERIA: mapping nilai ke sub_kriteria
3. Tabel MAHASISWA: data pribadi mahasiswa
4. Tabel PENILAIAN: nilai dari setiap mahasiswa per kriteria

Contoh Flow:
- Mahasiswa 1: IPK 3.7
  → Cari di detail_kriteria IPK: "3.51-4.00" → nilai: 1.0000
  → Insert ke penilaian: mahasiswa_id=1, kriteria_id=1, nilai=1.0000
```

### 6.2 Processing

```
1. SAWService->process(penilaian_ke=1)

2. getData():
   - Query kriteria: [C1: 0.25/benefit, C2: 0.25/cost, ...]
   - Query penilaian where penilaian_ke=1:
     [mahasiswa_1: [1.00, 0.25, 0.75, 0.50], ...]

3. normalizationMatrix():
   - Cari max per kriteria: [max_C1=1.0, max_C2=0.5, ...]
   - Cari min per kriteria: [min_C1=0.5, min_C2=0.25, ...]

   - Normalisasi benefit: r(i,j) = x(i,j) / max
   - Normalisasi cost: r(i,j) = min / x(i,j)

   Hasil R matriks:
```

              C1     C2     C3     C4

Mhs1: 1.0000 1.0000 1.0000 1.0000
Mhs2: 0.9000 0.8000 0.7500 0.5000
mhs3: 0.7000 0.6000 0.5000 0.2500

```

4. calculatePreference():
P(1) = 0.25×1.00 + 0.25×1.00 + 0.25×1.00 + 0.25×1.00 = 1.00
P(2) = 0.25×0.90 + 0.25×0.80 + 0.25×0.75 + 0.25×0.50 = 0.7375
P(3) = 0.25×0.70 + 0.25×0.60 + 0.25×0.50 + 0.25×0.25 = 0.5125

5. ranking():
Ranking 1: Mahasiswa 1 (Skor: 1.0000)
Ranking 2: Mahasiswa 2 (Skor: 0.7375)
Ranking 3: Mahasiswa 3 (Skor: 0.5125)

6. Simpan ke tabel HASIL:
INSERT hasil:
- mahasiswa_id=1, penilaian_ke=1, skor=1.0000, ranking=1
- mahasiswa_id=2, penilaian_ke=1, skor=0.7375, ranking=2
- mahasiswa_id=3, penilaian_ke=1, skor=0.5125, ranking=3
```

### 6.3 Output

```
Hasil SAW per penilaian_ke:
- Ranking mahasiswa berdasarkan skor akhir
- Status lolos/tidak lolos (bisa berdasarkan threshold atau top N)
- Detail breakdown nilai normalisasi per kriteria
```

---

## 7. Implementation Steps (Detailed)

### Step 1: Create SAWService

- [ ] Create file: `app/Services/SAWService.php`
- [ ] Implement method: `getData(penilaian_ke)`
- [ ] Implement method: `normalizationMatrix(X, kriteria)`
- [ ] Implement method: `calculatePreference(R, weights)`
- [ ] Implement method: `ranking(P)`
- [ ] Implement method: `process(penilaian_ke, threshold=NULL)`

### Step 2: Create SAWHelper

- [ ] Create file: `app/Helpers/SAWHelper.php`
- [ ] Create helper functions for utilities

### Step 3: Update Controllers

- [ ] Update `PenilaianController::hitungSAW()`
- [ ] Update/Create `HasilController::index()` & `detail()`

### Step 4: Testing

- [ ] Unit test SAW calculation
- [ ] Integration test with database
- [ ] Manual test dengan sample data

---

## 8. Code Examples (Preview)

### 8.1 How to Use SAWService

```php
// Di Controller
$sawService = new SAWService();

// Hitung SAW untuk penilaian_ke = 1
$result = $sawService->process(penilaian_ke: 1, threshold: 0.65);

// Result:
[
  'success' => true,
  'message' => 'SAW calculation completed',
  'ranking' => [
    [
      'mahasiswa_id' => 1,
      'nama' => 'Budi',
      'skor' => 0.98,
      'ranking' => 1,
      'status_lolos' => 'lolos'
    ],
    [
      'mahasiswa_id' => 2,
      'nama' => 'Ani',
      'skor' => 0.75,
      'ranking' => 2,
      'status_lolos' => 'lolos'
    ],
    ...
  ]
]
```

---

## 9. Key Features

✅ Normalisasi otomatis benefit & cost
✅ Bobot fleksibel per kriteria
✅ Mapping nilai via detail_kriteria
✅ Batching per penilaian_ke
✅ Auto-ranking dan status lolos
✅ Database persistence
✅ Error handling & validation

---

## 10. Next Steps

Setelah plan ini di-approve, saya akan:

1. **Create SAWService.php** - Core business logic
2. **Create SAWHelper.php** - Utility functions
3. **Update PenilaianController** - Integration point
4. **Create routes** - API endpoints jika needed
5. **Testing** - Unit & integration tests
6. **Documentation** - Function docs

---

## 11. Questions to Clarify

1. **Threshold untuk lolos:** Berapa nilai minimum untuk dianggap "lolos"?
   - Fixed value (e.g., 0.65)?
   - Top N mahasiswa?
   - Dynamic based on kuota?

2. **Data validation:**
   - Apakah semua mahasiswa sudah harus punya nilai semua kriteria?
   - Atau ada default value untuk missing data?

3. **Hasil simpan kemana:**
   - Langsung ke tabel `hasil`?
   - Atau generate report dulu?

4. **Prioritas:**
   - Yang mana yang ingin dikerjakan duluan?
   - Testing dengan data dummy atau langsung real data?

---

**Status:** Ready for implementation ✅
