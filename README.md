# SPK Beasiswa INSOL

Sistem pendukung keputusan beasiswa berbasis CodeIgniter 4.
Project ini dipakai untuk kelola data Mahasiswa, Kriteria, Penilaian, dan Hasil seleksi (metode SAW).

## Fitur Utama

- Dashboard ringkasan data
- Manajemen Mahasiswa
- Manajemen Kriteria
- Input Penilaian
- Hasil ranking / kelulusan
- Login popup dan proteksi halaman

## Stack

- PHP 8.2+
- CodeIgniter 4
- MariaDB / MySQL
- Bootstrap Icons

## Menjalankan Project

1. Copy env ke .env lalu isi baseURL + database.
2. Install dependency:

	composer install

3. Jalankan server lokal:

	php spark serve

4. Buka:

	http://localhost:8080

## Struktur Singkat

- app/Controllers -> alur halaman
- app/Models -> query data
- app/Views -> tampilan
- public/css -> styling

## Bebek Badak Joget Berkacamata (Auto Update)

Section ini diupdate otomatis oleh workflow GitHub Actions di .github/workflows/duck-dance.yml.

<!-- DUCK_DANCE_START -->
```text
            .-====-.
           /  _  _  \
          |  (o)(o)  |
          |    __    |  <- kacamata 😎
       ___|   /__\   |___
     /    |    /\    |    \
    /  🦆 |   /  \   | 🦏  \
   |      |  /____\  |      |
    \      \_/ || \_/      /
     \_________||_________/
         _/\_      _/\_
        /_/\_\    /_/\_\
        ♪     ♪       ♫

mode joget jumbo aktif: 3/4 😎🦆🦏
```
<!-- DUCK_DANCE_END -->

## Catatan

Repo ini sudah disesuaikan dari starter CodeIgniter ke kebutuhan SPK Beasiswa.
Kalau UI terlihat terlalu serius, cek bebek di atas dulu biar waras.
