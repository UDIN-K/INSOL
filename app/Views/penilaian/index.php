<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Input Penilaian</h2>
	<div class="dash-breadcrumb">Proses / Penilaian</div>
</div>

<div class="card">
<p>Jumlah kriteria aktif: <strong><?= esc((string) $jumlahKriteria) ?></strong></p>
<table>
<thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($mahasiswa)): ?><tr><td colspan="5">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($mahasiswa as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td><td><?= esc($item['nim']) ?></td><td><?= esc($item['nama']) ?></td><td>
	<?php if ($item['penilaian_lengkap']): ?>
		<span class="status-badge status-badge-success"><i class="pi pi-check-circle"></i> Lengkap</span>
	<?php else: ?>
		<span class="status-badge status-badge-warning"><i class="pi pi-exclamation-circle"></i> Belum</span>
	<?php endif; ?>
</td>
<td><a class="btn" href="/penilaian/input/<?= $item['id'] ?>">Input/Edit</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?= $this->endSection() ?>
