<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Data Mahasiswa</h2>
<p><a href="/mahasiswa/create" class="btn">+ Tambah Mahasiswa</a></p>
<table>
<thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Prodi</th><th>Semester</th><th>IPK</th><th>Penghasilan Ortu</th><th>Jumlah Tanggungan</th><th>Prestasi Non Akademik</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($mahasiswa)): ?><tr><td colspan="10">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($mahasiswa as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td>
<td><?= esc($item['nim']) ?></td>
<td><?= esc($item['nama']) ?></td>
<td><?= esc((string) ($item['prodi'] ?? '-')) ?></td>
<td><?= esc((string) ($item['semester'] ?? '-')) ?></td>
<td><?= esc((string) ($item['ipk'] ?? '-')) ?></td>
<td><?= esc((string) ($item['penghasilan_ortu'] ?? '-')) ?></td>
<td><?= esc((string) ($item['jumlah_tanggungan'] ?? '-')) ?></td>
<td><?= esc((string) ($item['prestasi_non_akademik'] ?? '-')) ?></td>
<td>
<a class="btn btn-secondary" href="/mahasiswa/edit/<?= $item['id'] ?>">Edit</a>
<form action="/mahasiswa/delete/<?= $item['id'] ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus data?')">
<?= csrf_field() ?><button class="btn btn-danger" type="submit">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?= $this->endSection() ?>
