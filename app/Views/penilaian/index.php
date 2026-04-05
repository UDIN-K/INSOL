<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Input Penilaian</h2>
<p>Jumlah kriteria: <strong><?= esc((string) $jumlahKriteria) ?></strong></p>
<table>
<thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($mahasiswa)): ?><tr><td colspan="5">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($mahasiswa as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td><td><?= esc($item['nim']) ?></td><td><?= esc($item['nama']) ?></td><td><?= $item['penilaian_lengkap'] ? 'Lengkap' : 'Belum' ?></td>
<td><a class="btn" href="/penilaian/input/<?= $item['id'] ?>">Input/Edit</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?= $this->endSection() ?>
