<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Input Nilai - <?= esc($mahasiswa['nama'] ?? '') ?> (<?= esc($mahasiswa['nim'] ?? '') ?>)</h2>
<form action="/penilaian/save/<?= esc((string) ($mahasiswa['id'] ?? 0)) ?>" method="post">
<?= csrf_field() ?>
<table>
<thead><tr><th>No</th><th>Kriteria</th><th>Nilai</th></tr></thead>
<tbody>
<?php foreach ($kriteria as $index => $item): ?>
<tr>
<td><?= $index + 1 ?></td>
<td><?= esc($item['kriteria']) ?></td>
<td><input type="number" step="0.0001" min="0" name="nilai[<?= $item['id'] ?>]" value="<?= esc((string) old('nilai.' . $item['id'], $nilaiByKriteria[$item['id']] ?? '')) ?>" required></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p style="margin-top:12px"><button class="btn" type="submit">Simpan</button> <a class="btn btn-secondary" href="/penilaian">Kembali</a></p>
</form>
<?= $this->endSection() ?>
