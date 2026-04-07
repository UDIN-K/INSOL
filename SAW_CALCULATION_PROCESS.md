# Dokumentasi Proses dan Alur Perhitungan SAW

## 📋 Daftar Isi

1. [Pengenalan SAW](#pengenalan-saw)
2. [Alur Umum Aplikasi](#alur-umum-aplikasi)
3. [Proses Input & Seleksi Mahasiswa](#proses-input--seleksi-mahasiswa)
4. [Proses Perhitungan SAW](#proses-perhitungan-saw)
5. [Proses Penyimpanan Hasil](#proses-penyimpanan-hasil)
6. [Data & Model](#data--model)
7. [Contoh Perhitungan](#contoh-perhitungan)

---

## Pengenalan SAW

### Apa itu SAW (Simple Additive Weighting)?

**Simple Additive Weighting (SAW)** adalah metode pengambilan keputusan multi-kriteria yang memberikan alternatif solusi terbaik dari beberapa pilihan berdasarkan kriteria dan bobot yang telah ditentukan.

### Karakteristik SAW:

- **Multi-kriteria**: Mempertimbangkan banyak faktor/kriteria
- **Bobot**: Setiap kriteria memiliki tingkat kepentingan berbeda
- **Normalisasi**: Nilai dikonversi ke skala 0-1 untuk perbandingan yang adil
- **Agregasi**: Hasil akhir adalah penjumlahan dari nilai yang telah dibobot

### Rumus Dasar SAW:

$$V(a_i) = \sum_{j=1}^{n} w_j r_{ij}$$

Dimana:

- **V(ai)** = Nilai preferensi alternatif ke-i
- **wj** = Bobot kriteria ke-j
- **rij** = Nilai rating ternormalisasi alternatif ke-i pada kriteria ke-j
- **n** = Jumlah kriteria

---

## Alur Umum Aplikasi

```
┌─────────────────────┐
│  Halaman /hasil     │ ◄─── User masuk ke halaman utama
└──────────┬──────────┘
           │
           ▼
┌─────────────────────────────────┐
│ 1. Tampilkan List Mahasiswa     │
│ - Filter by NIM/Nama            │
│ - Filter by Semester            │
│ - Select multiple mahasiswa     │
│ - Set skor_minimum              │
└──────────┬──────────────────────┘
           │ Klik "Proses SAW & Simpan"
           ▼
┌─────────────────────────────────┐
│ 2. postProses()                 │
│    POST /hasil/proses           │
│ - Validasi: ada mahasiswa?      │
│ - Jalankan buildSawComputation  │
│ - Simpan hasil ke session       │
└──────────┬──────────────────────┘
           │ Redirect
           ▼
┌─────────────────────────────────┐
│ 3. Halaman /hasil/preview       │
│ - Tampilkan ranking hasil       │
│ - Tampilkan detail perhitungan  │
│ - Tombol Confirm/Cancel         │
└──────────┬──────────────────────┘
           │
      ┌────┴─────┐
      │           │
      ▼           ▼
  CONFIRM    CANCEL
      │           │
      │           └──► Kembali ke /hasil
      │
      ▼
┌─────────────────────────────────┐
│ 4. postConfirm()                │
│    POST /hasil/confirm          │
│ - Ambil data dari session       │
│ - Tentukan penilaian_ke         │
│ - Simpan ke tabel hasil         │
│ - Clear session                 │
└──────────┬──────────────────────┘
           │ Redirect
           ▼
┌─────────────────────────────────┐
│ 5. Kembali ke /hasil            │
│    Dengan success message       │
└─────────────────────────────────┘
```

---

## Proses Input & Seleksi Mahasiswa

### Halaman `/hasil` (getIndex)

**Lokasi**: `app/Controllers/HasilController.php` - `getIndex()`
**View**: `app/Views/hasil/index.php`

#### Fitur:

1. **Filter Pencarian**
   - Search by NIM atau Nama (GET parameter)
   - Filter by Semester (GET parameter)
   - Reset filters button

2. **Tabel Mahasiswa**
   - Checkbox untuk multi-select
   - Info: NIM, Nama, Semester, IPK, Tanggungan Ortu, Penghasilan Ortu, Prestasi

3. **Input Skor Minimum**
   - Minimal lolos score (0-1)
   - Default: 0.5
   - Required field

4. **Tombol Proses**
   - Tetap disabled sampai ada minimal 1 mahasiswa dipilih
   - Klik = submit POST form ke `/hasil/proses`

#### Data yang Dikirim:

```
POST /hasil/proses
{
  csrf_test_name: "token...",
  skor_minimum: "0.5",
  mahasiswa_ids[]: ["11", "12", "13", "14", "15"]
}
```

---

## Proses Perhitungan SAW

### Step 1: Ambil & Validasi Data (postProses)

**Lokasi**: `app/Controllers/HasilController.php` - `postProses()`

```php
$skorMinimum = (float) $this->request->getPost('skor_minimum');
$selectedMahasiswaIds = array_map('intval', (array) $this->request->getPost('mahasiswa_ids') ?? []);
```

**Validasi:**

- Mahasiswa harus ada minimal 1
- Jika tidak ada, return error dengan redirect back

**Action:**

- Jalankan `buildSawComputation($skorMinimum, $selectedMahasiswaIds)`
- Simpan hasil ke session: `session()->set('saw_computation', $computed)`
- Redirect ke `/hasil/preview`

---

### Step 2: Perhitungan SAW (buildSawComputation)

**Lokasi**: `app/Controllers/HasilController.php` - `buildSawComputation()`

Fungsi ini melakukan perhitungan SAW secara lengkap. Berikut langkah-langkahnya:

#### A. Ambil Data Master

```php
$kriteria = KriteriaModel()->orderBy('kode', 'ASC')->findAll();
$allMahasiswa = MahasiswaModel()->orderBy('nim', 'ASC')->findAll();
$penilaian = PenilaianModel()->findAll();
$detailRows = DetailKriteriaModel()->findAll();
```

**Data Kriteria:**

- id, kode, kriteria, bobot, atribut (benefit/cost)
- Bobot = tingkat kepentingan kriteria
- Atribut = jenis kriteria (benefit atau cost)

**Data Detail Kriteria:**

- sub_kriteria, nilai, jenis_kondisi, batas_atas, batas_bawah
- Digunakan untuk scoring otomatis based on kondisi

#### B. Filter Mahasiswa yang Dipilih

```php
if (!empty($selectedMahasiswaIds)) {
    // Ambil hanya mahasiswa yang dipilih user
    foreach ($allMahasiswa as $m) {
        if (in_array((int) $m['id'], $selectedMahasiswaIds)) {
            $mahasiswa[] = $m;
        }
    }
}
```

#### C. Generate Nilai Penilaian

Untuk setiap kombinasi mahasiswa + kriteria:

1. **Jika nilai sudah ada di tabel penilaian**: Gunakan sebagaimana adanya
2. **Jika nilai belum ada**: Generate otomatis berdasarkan data mahasiswa

```php
// Mapping data mahasiswa ke kriteria
private function mapMahasiswaValue(array $mahasiswa, array $kriteria)
{
    $name = normalizeText($kriteria['kriteria']);

    if (str_contains($name, 'ipk'))
        return $mahasiswa['ipk'];

    if (str_contains($name, 'penghasilan'))
        return $mahasiswa['penghasilan_ortu'];

    if (str_contains($name, 'tanggungan'))
        return $mahasiswa['jumlah_tanggungan'];

    if (str_contains($name, 'prestasi'))
        return $mahasiswa['prestasi_non_akademik'];
}
```

3. **Scoring Otomatis:**

**Untuk nilai Numerik:**

```php
// Regex patterns untuk parse kondisi dari sub_kriteria:
// "3.5-4.0"        → type: range
// ">3.5"           → type: gt
// ">=3.5"          → type: gte
// "<3.5"           → type: lt
// "<=3.5"          → type: lte
// "3.5"            → type: eq

// Bandingkan nilai mahasiswa dengan kondisi
// Jika match → ambil nilai dari detail_kriteria
```

**Untuk nilai Text:**

```php
// Normalisasi text (lowercase, trim spasi)
// Cek apakah source value mengandung target dari sub_kriteria
// Jika match → ambil nilai dari detail_kriteria
```

#### D. Normalisasi Matriks Keputusan

Setelah semua nilai terkumpul, lakukan normalisasi:

```php
// Cari min & max untuk setiap kriteria
$max[$kid] = max(semua nilai untuk kriteria k)
$min[$kid] = min(semua nilai untuk kriteria k)

// Normalisasi setiap nilai (rij)
if atribut == 'benefit':
    rij = nilai / max    // Semakin tinggi semakin baik
else (atribut == 'cost'):
    rij = min / nilai    // Semakin rendah semakin baik
```

**Hasil:** Semua nilai sekarang dalam range 0-1

#### E. Perhitungan Preferensi (Nilai Akhir)

Untuk setiap mahasiswa:

```php
// Normalisasi bobot (agar total = 1)
w = bobot_kriteria / total_bobot

// Untuk setiap kriteria, hitung nilai terbobot
nilai_terbobot = rij × w

// Jumlahkan semua nilai terbobot
P(i) = Σ (rij × w)  untuk semua j
```

**Detail Perhitungan Disimpan:**

```php
detailKomponen[] = [
    'kriteria_id' => $kid,
    'kode' => kode kriteria,
    'kriteria' => nama kriteria,
    'raw' => nilai asli,
    'normalisasi' => rij,
    'bobot' => w,
    'terbobot' => rij × w,
    'atribut' => benefit/cost,
];
```

#### F. Ranking & Status Lolos

```php
// Sort berdasarkan P(i) descending (tertinggi ke terendah)
usort($scores, fn($a, $b) => $b['skor'] <=> $a['skor']);

// Tentukan ranking & status lolos
foreach ($scores as ranking, item) {
    item['ranking'] = ranking + 1;
    item['status_lolos'] = item['skor'] >= skorMinimum ? 'Lolos' : 'Tidak Lolos';
}
```

#### G. Output buildSawComputation

```php
[
    'kriteria'      => array of kriteria,
    'mahasiswa'     => array of mahasiswa terpilih,
    'bobotTotal'    => sum of all bobot,
    'nilai'         => matrix nilai mentah [mahasiswa_id][kriteria_id],
    'normalisasi'   => matrix nilai normalisasi [mahasiswa_id][kriteria_id],
    'preferensi'    => [mahasiswa_id] => ['skor' => P(i), 'komponen' => detail],
    'ranking'       => array disorting by score dengan ranking & status lolos,
    'skorMinimum'   => threshold yang diset user,
]
```

---

## Proses Penyimpanan Hasil

### Step 3: Preview Hasil (getPreview)

**Lokasi**: `app/Controllers/HasilController.php` - `getPreview()`
**View**: `app/Views/hasil/preview.php`

**Action:**

1. Ambil data dari session: `session()->get('saw_computation')`
2. Pass ke view untuk ditampilkan
3. User bisa lihat ranking detail & breakdown perhitungan

**Tombol:**

- Confirm → POST `/hasil/confirm` (postConfirm)
- Cancel → POST `/hasil/cancel` (cancelProses)

---

### Step 4: Simpan ke Database (postConfirm)

**Lokasi**: `app/Controllers/HasilController.php` - `postConfirm()`

```php
// 1. Ambil data computed dari session
$computed = session()->get('saw_computation');

// 2. Tentukan penilaian_ke (periode/batch)
$last = HasilModel()->selectMax('penilaian_ke')->first();
$penilaianKe = ($last['penilaian_ke'] ?? 0) + 1;

// 3. Insert setiap score ke tabel hasil
foreach ($computed['ranking'] as $item) {
    HasilModel()->insert([
        'mahasiswa_id'   => $item['mahasiswa_id'],
        'penilaian_ke'   => $penilaianKe,          // Batch/periode
        'skor'           => $item['skor'],         // P(i)
        'ranking'        => $item['ranking'],      // 1, 2, 3, ...
        'status_lolos'   => $item['status_lolos'], // Lolos/Tidak Lolos
    ]);
}

// 4. Clear session
session()->remove('saw_computation');

// 5. Redirect ke hasil dengan success message
redirect()->to('/hasil')->with('success', "Perhitungan SAW selesai untuk penilaian ke-$penilaianKe.");
```

**Hasil di Database:**

Tabel `hasil`:
| mahasiswa_id | penilaian_ke | skor | ranking | status_lolos |
|---|---|---|---|---|
| 11 | 1 | 0.78125 | 1 | Lolos |
| 12 | 1 | 0.65250 | 2 | Lolos |
| 13 | 1 | 0.45000 | 3 | Tidak Lolos |

---

## Data & Model

### 1. Tabel Kriteria

```
id | kode | kriteria              | bobot | atribut
---|------|-----------------------|-------|--------
1  | C1   | IPK                   | 25    | benefit
2  | C2   | Penghasilan Ortu       | 30    | cost
3  | C3   | Jumlah Tanggungan      | 20    | cost
4  | C4   | Prestasi Non-Akademik | 25    | benefit
```

- **kode**: Identifier unik (C1, C2, dll)
- **bobot**: Nilai kepentingan (akan dinormalisasi menjadi w)
- **atribut**:
  - `benefit` = semakin tinggi semakin baik
  - `cost` = semakin rendah semakin baik

### 2. Tabel Detail Kriteria

```
id | kriteria_id | sub_kriteria | nilai | jenis_kondisi | batas_bawah | batas_atas
---|---|---|---|---|---|---
1  | 1 | 3.5-4.0      | 100 | range | 3.5 | 4.0
2  | 1 | 3.0-3.49     | 80  | range | 3.0 | 3.49
3  | 2 | >5000000     | 20  | gt    | 5000000 |
4  | 3 | 1-2          | 100 | range | 1 | 2
5  | 4 | Juara 1      | 100 | text  | - | -
```

- **jenis_kondisi**: Tipe kondisi (range, gt, gte, lt, lte, eq, text)
- **Scoring otomatis**: Program match kondisi dengan nilai mahasiswa

### 3. Tabel Mahasiswa

```
id | nim    | nama  | semester | ipk  | penghasilan_ortu | jumlah_tanggungan | prestasi_non_akademik
---|--------|-------|----------|------|-----------------|-------------------|----------------------
11 | 001    | Budi  | 6        | 3.8  | 2500000         | 2                 | Juara 1
12 | 002    | Siti  | 6        | 3.5  | 5000000         | 3                 | Juara 2
13 | 003    | Ahmad | 6        | 3.2  | 7000000         | 5                 | Peserta
```

### 4. Tabel Penilaian

```
id | mahasiswa_id | kriteria_id | nilai
---|---|---|---
1  | 11 | 1 | 100
2  | 11 | 2 | 80
3  | 12 | 1 | 80
4  | 12 | 2 | 60
```

- **Opsional:** Jika empty, scoring dilakukan otomatis
- **Jika ada**: Nilai ini yang digunakan (override scoring otomatis)

### 5. Tabel Hasil

```
id | mahasiswa_id | penilaian_ke | skor    | ranking | status_lolos | created_at
---|---|---|---------|---|-------------|---
1  | 11 | 1 | 0.78125 | 1 | Lolos       | 2025-01-15
2  | 12 | 1 | 0.65250 | 2 | Lolos       | 2025-01-15
3  | 13 | 1 | 0.45000 | 3 | Tidak Lolos | 2025-01-15
```

- **penilaian_ke**: Batch/periode perhitungan
- Akumulatif: bisa ada multiple periode untuk tracking riwayat

---

## Contoh Perhitungan

### Skenario:

- 3 Mahasiswa: Budi, Siti, Ahmad
- 4 Kriteria: IPK (25%), Penghasilan (30%), Tanggungan (20%), Prestasi (25%)
- Skor minimum lolos: 0.5

### Data Awal:

| Mahasiswa | IPK | Penghasilan | Tanggungan | Prestasi |
| --------- | --- | ----------- | ---------- | -------- |
| Budi      | 3.8 | 2.5M        | 2          | Juara 1  |
| Siti      | 3.5 | 5M          | 3          | Juara 2  |
| Ahmad     | 3.2 | 7M          | 5          | Peserta  |

### Step 1: Mapping Kriteria

**IPK (benefit):**

- 3.8 → 100 (range 3.5-4.0)
- 3.5 → 100 (range 3.5-4.0)
- 3.2 → 80 (range 3.0-3.49)

**Penghasilan (cost):**

- 2.5M → 20 (> 5M? no, let's say < 3M = 100, 3-5M = 80, > 5M = 20)
- 2.5M → 100
- 5M → 20
- 7M → 20

**Tanggungan (cost):**

- 2 → 100 (range 1-2)
- 3 → 80 (range 3-4)
- 5 → 60 (range 5-6)

**Prestasi (benefit):**

- Juara 1 → 100
- Juara 2 → 80
- Peserta → 50

### Step 2: Matriks Nilai (X)

```
       | C1(IPK) | C2(Penghasilan) | C3(Tanggungan) | C4(Prestasi)
-------|---------|-----------------|---|---
Budi   | 100     | 100             | 100 | 100
Siti   | 100     | 20              | 80  | 80
Ahmad  | 80      | 20              | 60  | 50
```

### Step 3: Normalisasi (R)

**C1 (Benefit):**

- Max = 100
- Budi: 100/100 = 1.00
- Siti: 100/100 = 1.00
- Ahmad: 80/100 = 0.80

**C2 (Cost):**

- Min = 20, Max = 100
- Budi: 20/100 = 0.20
- Siti: 20/20 = 1.00
- Ahmad: 20/20 = 1.00

**C3 (Cost):**

- Min = 60, Max = 100
- Budi: 60/100 = 0.60
- Siti: 60/80 = 0.75
- Ahmad: 60/60 = 1.00

**C4 (Benefit):**

- Max = 100
- Budi: 100/100 = 1.00
- Siti: 80/100 = 0.80
- Ahmad: 50/100 = 0.50

### Step 4: Normalisasi Bobot (W)

Total bobot = 25 + 30 + 20 + 25 = 100

- W1 = 25/100 = 0.25
- W2 = 30/100 = 0.30
- W3 = 20/100 = 0.20
- W4 = 25/100 = 0.25

### Step 5: Perhitungan Preferensi (P)

**Budi:** P = (1.00 × 0.25) + (0.20 × 0.30) + (0.60 × 0.20) + (1.00 × 0.25)

```
     = 0.25 + 0.06 + 0.12 + 0.25
     = 0.68
```

**Siti:** P = (1.00 × 0.25) + (1.00 × 0.30) + (0.75 × 0.20) + (0.80 × 0.25)

```
     = 0.25 + 0.30 + 0.15 + 0.20
     = 0.90
```

**Ahmad:** P = (0.80 × 0.25) + (1.00 × 0.30) + (1.00 × 0.20) + (0.50 × 0.25)

```
     = 0.20 + 0.30 + 0.20 + 0.125
     = 0.825
```

### Step 6: Ranking & Status Lolos

| Ranking | Mahasiswa | Skor  | Status |
| ------- | --------- | ----- | ------ |
| 1       | Siti      | 0.90  | Lolos  |
| 2       | Ahmad     | 0.825 | Lolos  |
| 3       | Budi      | 0.68  | Lolos  |

(Semua lolos karena skor > 0.5)

### Step 7: Simpan ke Database

```php
// penilaian_ke = 1
INSERT INTO hasil VALUES
(NULL, 12, 1, 0.90, 1, 'Lolos', NOW()),  // Siti
(NULL, 13, 1, 0.825, 2, 'Lolos', NOW()),  // Ahmad
(NULL, 11, 1, 0.68, 3, 'Lolos', NOW());   // Budi
```

---

## Flow Diagram Lengkap

```
Halaman /hasil
  ↓
[User memilih mahasiswa + set skor minimum]
  ↓
Klik "Proses SAW & Simpan"
  ↓
POST /hasil/proses → postProses()
  ↓
  ├─ Validasi: mahasiswa_ids not empty
  ├─ Ambil skorMinimum ✓
  ├─ Call buildSawComputation()
  │   ├─ Ambil kriteria, mahasiswa, penilaian, detail
  │   ├─ Generate nilai otomatis (jika belum ada)
  │   ├─ Normalisasi per kriteria
  │   ├─ Hitung preferensi (P = Σ w×r)
  │   ├─ Sort by score descending
  │   └─ Output: array computation
  ├─ Cek error
  ├─ Set session: saw_computation
  └─ Redirect → /hasil/preview
      ↓
Halaman /hasil/preview (getPreview)
  ├─ Ambil data dari session
  ├─ Tampilkan ranking & detail perhitungan
  ├─ Tombol Confirm / Cancel
  │
  ├─ [User klik CONFIRM]
  │   ↓
  │   POST /hasil/confirm → postConfirm()
  │   ├─ Ambil computed dari session
  │   ├─ Tentukan penilaian_ke (increment)
  │   ├─ Loop ranking, INSERT ke tabel hasil
  │   ├─ Clear session
  │   └─ Redirect → /hasil dengan success message
  │       ↓
  │   Kembali ke /hasil
  │
  └─ [User klik CANCEL]
      ↓
      POST /hasil/cancel → cancelProses()
      ├─ Clear session
      └─ Redirect → /hasil
```

---

## Ringkasan

| Tahap      | Fungsi                               | Input                   | Output                      |
| ---------- | ------------------------------------ | ----------------------- | --------------------------- |
| 1. Seleksi | getIndex()                           | User pilih mahasiswa    | Form data                   |
| 2. Proses  | postProses() + buildSawComputation() | Mahasiswa IDs, Skor Min | Computation array (session) |
| 3. Preview | getPreview()                         | Session data            | Ranking preview             |
| 4. Confirm | postConfirm()                        | Session data            | Saved to DB                 |

**Key Points:**

- ✅ Bobot normalisasi: Σ bobot kriteria
- ✅ Normalisasi nilai: Max untuk benefit, Min untuk cost
- ✅ Preferensi: Σ(w × r) untuk setiap mahasiswa
- ✅ Ranking: Dari skor tertinggi → terendah
- ✅ Status: Lolos jika skor ≥ skor_minimum
- ✅ Riwayat: Setiap compute = penilaian_ke baru
