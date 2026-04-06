# Test Data Summary - 5 Mahasiswa Ready for Input

**Status:** ✅ Mahasiswa test data ready, Penilaian diisi saat user input

---

## 📊 Current Data Status

| Tabel           | Jumlah | Status                 |
| --------------- | ------ | ---------------------- |
| Mahasiswa       | 5      | ✅ Siap (seeded)       |
| Penilaian       | 0      | ⏳ Menunggu input user |
| Kriteria        | 4      | ✅ Ada (migration)     |
| Detail Kriteria | 17     | ✅ Ada (migration)     |

---

## �️ Database Table Structure

### Tabel: mahasiswa

```
Columns:
├── id (INT UNSIGNED, PK, AUTO_INCREMENT)
├── nim (VARCHAR, UNIQUE)
├── nama (VARCHAR)
├── ipk (DECIMAL)
├── penghasilan_ortu (INT)
├── jumlah_tanggungan (INT)
├── prestasi (VARCHAR)
├── created_at (DATETIME)
└── updated_at (DATETIME)
```

### Tabel: penilaian

```
Columns:
├── id (INT UNSIGNED, PK, AUTO_INCREMENT)
├── mahasiswa_id (INT UNSIGNED, FK)
├── penilaian_ke (INT, DEFAULT 1)
├── kriteria_id (INT UNSIGNED, FK)
├── nilai (DECIMAL(12,4), DEFAULT 0.0000)
├── created_at (DATETIME)
└── updated_at (DATETIME)

Indexes:
├── PRIMARY KEY (id)
├── FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
├── FOREIGN KEY (kriteria_id) REFERENCES kriteria(id)
├── INDEX (penilaian_ke)
└── UNIQUE (mahasiswa_id, kriteria_id, penilaian_ke)
```

### Tabel: hasil

```
Columns:
├── id (INT UNSIGNED, PK, AUTO_INCREMENT)
├── mahasiswa_id (INT UNSIGNED, FK)
├── penilaian_ke (INT, DEFAULT 1)
├── skor (DECIMAL(12,6), DEFAULT 0.000000)
├── ranking (INT UNSIGNED, DEFAULT 0)
├── status_lolos (VARCHAR(20), DEFAULT 'Tidak Lolos')
├── created_at (DATETIME)
└── updated_at (DATETIME)

Indexes:
├── PRIMARY KEY (id)
├── FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
└── INDEX (penilaian_ke)
```

---

## �👥 5 Test Mahasiswa (Sudah Diinsert)

| ID  | NIM     | Nama              | IPK  | Penghasilan Ortu | Tanggungan | Prestasi    |
| --- | ------- | ----------------- | ---- | ---------------- | ---------- | ----------- |
| 1   | 2020001 | **Budi Santoso**  | 3.75 | 450000           | 4          | Nasional    |
| 2   | 2020002 | **Ani Wijaya**    | 3.50 | 1000000          | 3          | Provinsi    |
| 3   | 2020003 | **Citra Dewi**    | 3.20 | 1500000          | 2          | Universitas |
| 4   | 2020004 | **Deni Hermawan** | 3.65 | 800000           | 5          | Kota        |
| 5   | 2020005 | **Eka Sari**      | 2.95 | 3500000          | 1          | Universitas |

---

## 🔄 Alur Perhitungan SAW (NEW FLOW)

### Step 1: Dashboard Penilaian
```
URL: /penilaian
- Lihat list mahasiswa dengan status penilaian lengkap/belum
- Klik tombol BIRU "Mulai Perhitungan SAW" di kanan atas
```

### Step 2: Form Select Mahasiswa + Konfigurasi
```
URL: /penilaian/form-hitung-saw (GET)
Form:
├── Periode Penilaian: [input number, default 1]
├── Threshold Lolos: [input decimal, default 0.65]
├── Pilih Mahasiswa: [checkbox list dengan SELECT ALL]
└── Button "Mulai Perhitungan SAW" (hijau)
```

### Step 3: Proses dan Tampilkan Hasil

```
URL: /penilaian/hitung-saw (POST)
SAWService Generate:
├── STEP 1: Matriks Keputusan (X) - raw values
├── STEP 2: Matriks Normalisasi (R) - normalized 0-1
├── STEP 3: Perhitungan Bobot (R × W)
├── STEP 4: Nilai Preferensi (P) = Σ(w × r)
└── STEP 5: Ranking Final dengan status lolos/tidak lolos

View: hasil_perhitungan.php
Menampilkan: 5 colored cards dengan tabel detail setiap step
```

### Step 4: Action Buttons Pasca Perhitungan
```
✅ Mulai Perhitungan Baru   → /penilaian/form-hitung-saw
📄 Export ke PDF             → Print to PDF
🔙 Batal                     → /penilaian
📊 Lihat Detail Hasil        → /hasil
```

---

## 🧮 Setelah Input Selesai: Hitung SAW

### Step 1: Access Hitung SAW

```
URL: POST /penilaian/hitung-saw
Parameter:
  - penilaian_ke: 1
  - threshold: 0.65 (optional)
```

### Step 2: View Hasil 5 Step Breakdown

System akan menampilkan:

1. ✅ **STEP 1:** Matriks Keputusan (X)
2. ✅ **STEP 2:** Matriks Normalisasi (R)
3. ✅ **STEP 3:** Perhitungan Bobot (R × W)
4. ✅ **STEP 4:** Nilai Preferensi (P)
5. ✅ **STEP 5:** Ranking Final

### Step 3: Data Disimpan ke hasil table

Setelah semua penilaian per mahasiswa selesai diinput dan SAW dihitung, data tersimpan:

```sql
-- Expected hasil table setelah hitung SAW (periode 1)
INSERT INTO hasil (mahasiswa_id, penilaian_ke, skor, ranking, status_lolos)
VALUES
  (4, 1, 0.775000, 1, 'Lolos'),       -- Deni Hermawan
  (1, 1, 0.762500, 2, 'Lolos'),       -- Budi Santoso
  (2, 1, 0.637500, 3, 'Tidak Lolos'), -- Ani Wijaya
  (3, 1, 0.600000, 4, 'Tidak Lolos'), -- Citra Dewi
  (5, 1, 0.545000, 5, 'Tidak Lolos'); -- Eka Sari
```

**Kolom yang diisi SAWService:**

- `mahasiswa_id`: ID mahasiswa (1-5)
- `penilaian_ke`: Periode penilaian (1)
- `skor`: Hasil perhitungan SAW (DECIMAL 12,6)
- `ranking`: Urutan ranking (1-5)
- `status_lolos`: 'Lolos' atau 'Tidak Lolos' (berdasarkan threshold)

---

## 📝 Expected Mapping Values (Reference)

Saat user input data real, sistem akan auto-mapping:

### IPK (C1 - Benefit)

```
0-2.50    → 0.1000
2.50-3.00 → 0.5000
3.01-3.50 → 0.7500
3.51-4.00 → 1.0000
```

### Penghasilan Ortu (C2 - Cost)

```
0-500000         → 0.2500
500000-1500000   → 0.5000
1500000-3000000  → 0.7500
3000000-10000000 → 1.0000
```

### Jumlah Tanggungan (C3 - Cost)

```
1  → 0.1000
2  → 0.2500
3  → 0.5000
4  → 0.7500
>5 → 1.0000
```

### Prestasi Non Akademik (C4 - Benefit)

```
Tidak berprestasi  → 0.2500
Universitas        → 0.5000
Kota               → 0.5000
Provinsi           → 0.7500
Nasional           → 1.0000
```

---

## 📐 Example Calculation dengan Data Real

Jika user input seperti test data sebelumnya:

**Budi Santoso:**

- IPK 3.75 → 1.0000
- Gaji 450000 → 0.2500
- Tanggungan 4 → 0.7500
- Prestasi Nasional → 1.0000

**Perhitungan:**

```
P = (0.35 × 1.0000) + (0.25 × 0.2500) + (0.20 × 0.7500) + (0.20 × 1.0000)
  = 0.350000 + 0.062500 + 0.150000 + 0.200000
  = 0.762500 → Ranking 2, Status: LOLOS (>0.65)
```

---

## ✨ Ready untuk Production!

Workflow siap:

1. ✅ Mahasiswa test data ada
2. ✅ Kriteria & detail_kriteria sudah dikonfigurasi
3. ✅ User input penilaian → auto-save ke tabel penilaian
4. ✅ User klik "Hitung SAW" → tampilkan 5 step breakdown
5. ✅ Hasil tersimpan di tabel hasil

**Next:** User mulai input penilaian untuk 5 mahasiswa via UI! 🚀
