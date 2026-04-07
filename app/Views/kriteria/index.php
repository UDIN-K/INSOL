<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Data Kriteria</h2>
	<div class="dash-breadcrumb">Master / Kriteria</div>
</div>

<div class="card" style="margin-bottom:12px;">
<p><a href="/kriteria/create" class="btn"><i class="pi pi-plus"></i> Tambah Kriteria</a></p>
<p>Total Bobot: <strong><?= number_format((float) $totalBobot, 4) ?></strong></p>
<p>
<?php if (($bobotStatus ?? '') === 'ideal'): ?>
	<span class="status-badge status-badge-success"><i class="pi pi-check-circle"></i> Total bobot ideal (1.0000)</span>
<?php elseif (($bobotStatus ?? '') === 'kurang'): ?>
	<span class="status-badge status-badge-warning"><i class="pi pi-exclamation-circle"></i> Total bobot kurang dari 1, sesuaikan agar normalisasi optimal.</span>
<?php else: ?>
	<span class="status-badge status-badge-danger"><i class="pi pi-times-circle"></i> Total bobot melebihi 1, kurangi bobot kriteria.</span>
<?php endif; ?>
</p>
<table>
<thead><tr><th>No</th><th>Nomor</th><th>Kriteria</th><th>Bobot</th><th>Jenis</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($kriteria)): ?><tr><td colspan="6">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($kriteria as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td><td><?= esc($item['kode']) ?></td><td><?= esc($item['kriteria']) ?></td><td><?= number_format((float) $item['bobot'], 4) ?></td><td><?= esc(ucfirst((string) $item['atribut'])) ?></td>
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
</div>

<div class="card">
<h2 style="margin-top:0">Detail Kriteria</h2>
<p><a href="/kriteria/detail/create" class="btn"><i class="pi pi-plus"></i> Tambah Detail</a></p>
<table>
<thead><tr><th>No</th><th>Nomor</th><th>Kriteria</th><th>Sub Kriteria</th><th>Nilai</th><th>Aksi</th></tr></thead>
<tbody>
<?php if (empty($detailKriteria)): ?><tr><td colspan="6">Belum ada data.</td></tr><?php endif; ?>
<?php foreach ($detailKriteria as $i => $item): ?>
<tr>
<td><?= $i + 1 ?></td>
<td><?= esc($item['kode']) ?></td>
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
</div>
<?= $this->endSection() ?>
