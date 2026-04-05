<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Data Kriteria</h2>
<p><a href="/kriteria/create" class="btn">+ Tambah Kriteria</a></p>
<p>Total Bobot: <strong><?= number_format((float) $totalBobot, 4) ?></strong></p>
<table>
<thead><tr><th>No</th><th>Kriteria</th><th>Bobot</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($kriteria)): ?><tr><td colspan="4">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($kriteria as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td><td><?= esc($item['kriteria']) ?></td><td><?= number_format((float) $item['bobot'], 4) ?></td>
<td>
<a class="btn btn-secondary" href="/kriteria/edit/<?= $item['id'] ?>">Edit</a>
<form action="/kriteria/delete/<?= $item['id'] ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus data?')">
<?= csrf_field() ?><button class="btn btn-danger" type="submit">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2 style="margin-top:20px">Detail Kriteria</h2>
<p><a href="/kriteria/detail/create" class="btn">+ Tambah Detail</a></p>
<table>
<thead><tr><th>No</th><th>Kriteria</th><th>Sub Kriteria</th><th>Nilai</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($detailKriteria)): ?><tr><td colspan="5">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($detailKriteria as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td>
<td><?= esc($item['kriteria']) ?></td>
<td><?= esc($item['sub_kriteria']) ?></td>
<td><?= number_format((float) $item['nilai'], 4) ?></td>
<td>
<a class="btn btn-secondary" href="/kriteria/detail/edit/<?= $item['id'] ?>">Edit</a>
<form action="/kriteria/detail/delete/<?= $item['id'] ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus detail?')">
<?= csrf_field() ?><button class="btn btn-danger" type="submit">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?= $this->endSection() ?>
