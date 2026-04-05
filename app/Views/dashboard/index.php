<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Dashboard</h2>
<p>Selamat datang, <?= esc((string) session()->get('nama')) ?>.</p>
<div class="grid">
    <div class="card"><h3>Total Mahasiswa</h3><p><strong><?= esc((string) $totalMahasiswa) ?></strong></p></div>
    <div class="card"><h3>Total Kriteria</h3><p><strong><?= esc((string) $totalKriteria) ?></strong></p></div>
    <div class="card"><h3>Total Lolos</h3><p><strong><?= esc((string) $totalLolos) ?></strong></p></div>
</div>
<?= $this->endSection() ?>
