<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Hasil Ranking SAW</h2>
<form action="/hasil/proses" method="post" style="max-width:220px">
<?= csrf_field() ?>
<div class="form-group"><label>Kuota Lolos</label><input type="number" name="kuota" min="1" value="3" required></div>
<button class="btn btn-success" type="submit">Proses SAW</button>
</form>
<table>
<thead><tr><th>Ranking</th><th>NIM</th><th>Nama</th><th>Skor</th><th>Status</th></tr></thead>
<tbody>
<?php if (empty($rows)): ?><tr><td colspan="5">Belum ada hasil.</td></tr><?php endif; ?>
<?php foreach ($rows as $item): ?>
<tr>
<td><?= esc((string) $item['ranking']) ?></td><td><?= esc($item['nim']) ?></td><td><?= esc($item['nama']) ?></td><td><?= number_format((float) $item['skor'], 6) ?></td><td><strong><?= esc($item['status_lolos']) ?></strong></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?= $this->endSection() ?>
