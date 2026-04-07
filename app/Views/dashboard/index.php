<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
    <h2>Dashboard</h2>
    <div class="dash-breadcrumb">Dashboard / Home</div>
</div>

<div class="card dashboard-hero" style="margin-bottom:12px;">
    <h3 class="panel-title"><i class="pi pi-wave-pulse"></i> Selamat Datang di SPK Beasiswa</h3>
    <p>Gunakan menu di sisi kiri untuk mengelola data mahasiswa, kriteria, penilaian, lalu jalankan perhitungan SAW untuk menentukan ranking penerima beasiswa.</p>
</div>

<div class="dash-metric-grid">
    <div class="card metric-card">
        <div class="metric-head"><span class="metric-icon"><i class="pi pi-users"></i></span><div class="metric-title">Total Mahasiswa</div></div>
        <div class="metric-value"><?= esc((string) $totalMahasiswa) ?></div>
        <div class="metric-sub">Data mahasiswa terdaftar</div>
        <div class="sparkline"></div>
    </div>
    <div class="card metric-card">
        <div class="metric-head"><span class="metric-icon"><i class="pi pi-sliders-h"></i></span><div class="metric-title">Total Kriteria</div></div>
        <div class="metric-value"><?= esc((string) $totalKriteria) ?></div>
        <div class="metric-sub">Kriteria aktif seleksi</div>
        <div class="sparkline"></div>
    </div>
    <div class="card metric-card">
        <div class="metric-head"><span class="metric-icon"><i class="pi pi-star-fill"></i></span><div class="metric-title">Total Lolos</div></div>
        <div class="metric-value"><?= esc((string) $totalLolos) ?></div>
        <div class="metric-sub">Akumulasi hasil lolos</div>
        <div class="sparkline"></div>
    </div>
    <div class="card metric-card">
        <div class="metric-head"><span class="metric-icon"><i class="pi pi-database"></i></span><div class="metric-title">Sesi Penilaian</div></div>
        <div class="metric-value"><?= esc((string) $totalPenilaian) ?></div>
        <div class="metric-sub">Penilaian ke- terakhir</div>
        <div class="sparkline"></div>
    </div>
</div>

<div class="dash-panel-grid">
    <div class="card">
        <h3 class="panel-title"><i class="pi pi-chart-bar"></i> Ringkasan Sistem</h3>
        <p>SPK beasiswa berjalan dengan metode SAW dan menggunakan bobot kriteria dari modul Kriteria.</p>
        <div class="mini-list">
            <div class="mini-row"><span>Mahasiswa</span><strong><?= esc((string) $totalMahasiswa) ?></strong></div>
            <div class="mini-row"><span>Kriteria</span><strong><?= esc((string) $totalKriteria) ?></strong></div>
            <div class="mini-row"><span>Lolos</span><strong><?= esc((string) $totalLolos) ?></strong></div>
            <div class="mini-row"><span>Sesi</span><strong><?= esc((string) $totalPenilaian) ?></strong></div>
        </div>
    </div>

    <div class="card">
        <h3 class="panel-title"><i class="pi pi-bolt"></i> Quick Actions</h3>
        <div class="quick-actions">
            <a class="btn" href="/mahasiswa"><i class="pi pi-users"></i> Kelola Mahasiswa</a>
            <a class="btn btn-secondary" href="/kriteria"><i class="pi pi-sliders-h"></i> Kelola Kriteria</a>
            <a class="btn btn-success" href="/penilaian"><i class="pi pi-pencil"></i> Input Penilaian</a>
            <a class="btn" href="/hasil"><i class="pi pi-trophy"></i> Lihat Hasil</a>
        </div>
        <div class="mini-list" style="margin-top:14px;">
            <div class="mini-row"><span>Langkah 1</span><strong>Isi Data Mahasiswa</strong></div>
            <div class="mini-row"><span>Langkah 2</span><strong>Atur Bobot Kriteria</strong></div>
            <div class="mini-row"><span>Langkah 3</span><strong>Input Penilaian</strong></div>
            <div class="mini-row"><span>Langkah 4</span><strong>Proses & Ranking SAW</strong></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
