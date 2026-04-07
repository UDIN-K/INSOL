<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Hasil Ranking SAW</h2>
	<div class="toolbar-actions no-print">
		<button type="button" class="btn btn-ghost" onclick="window.print()"><i class="pi pi-print"></i> Cetak Laporan</button>
		<div class="dash-breadcrumb">Hasil / Ranking Seleksi</div>
	</div>
</div>

<div class="card" style="margin-bottom:12px;">
	<h3 class="panel-title"><i class="pi pi-cog"></i> Proses Perhitungan</h3>
	<form action="/hasil/proses" method="post" style="max-width:260px; margin-top:10px;">
		<?= csrf_field() ?>
		<div class="form-group">
			<label>Kuota Lolos</label>
			<input type="number" name="kuota" min="1" value="<?= esc((string) ($kuotaPreview ?? 3)) ?>" required>
		</div>
		<button class="btn btn-success" type="submit"><i class="pi pi-play"></i> Proses SAW & Simpan</button>
	</form>
</div>

<div class="card" style="margin-bottom:12px;">
	<h3 class="panel-title"><i class="pi pi-table"></i> Hasil Ranking Tersimpan</h3>
	<table>
		<thead><tr><th>Penilaian Ke</th><th>Ranking</th><th>NIM</th><th>Nama</th><th>Skor</th><th>Status</th></tr></thead>
		<tbody>
		<?php if (empty($rows)): ?><tr><td colspan="6">Belum ada hasil.</td></tr><?php endif; ?>
		<?php foreach ($rows as $item): ?>
		<tr>
			<td><?= esc((string) $item['penilaian_ke']) ?></td>
			<td>
				<span class="rank-chip <?= ((int) $item['ranking'] <= 3) ? 'rank-chip-top' : '' ?>">#<?= esc((string) $item['ranking']) ?></span>
			</td>
			<td><?= esc($item['nim']) ?></td>
			<td><?= esc($item['nama']) ?></td>
			<td><?= number_format((float) $item['skor'], 6) ?></td>
			<td>
				<?php if ((string) $item['status_lolos'] === 'Lolos'): ?>
					<span class="status-badge status-badge-success"><i class="pi pi-check"></i> Lolos</span>
				<?php else: ?>
					<span class="status-badge status-badge-danger"><i class="pi pi-times"></i> Tidak Lolos</span>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php if (isset($preview['error'])): ?>
	<div class="alert alert-error"><?= esc((string) $preview['error']) ?></div>
<?php else: ?>
	<div class="card" style="margin-bottom:12px;">
		<h3 class="panel-title"><i class="pi pi-list"></i> Matriks Keputusan (Nilai Awal)</h3>
		<table>
			<thead>
			<tr>
				<th>NIM</th>
				<th>Nama</th>
				<?php foreach (($preview['kriteria'] ?? []) as $k): ?>
					<th><?= esc((string) $k['kode']) ?></th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach (($preview['mahasiswa'] ?? []) as $m): ?>
				<tr>
					<td><?= esc((string) $m['nim']) ?></td>
					<td><?= esc((string) $m['nama']) ?></td>
					<?php foreach (($preview['kriteria'] ?? []) as $k): ?>
						<?php $mid = (int) $m['id']; $kid = (int) $k['id']; ?>
						<td><?= number_format((float) (($preview['nilai'][$mid][$kid] ?? 0)), 4) ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="card" style="margin-bottom:12px;">
		<h3 class="panel-title"><i class="pi pi-calculator"></i> Normalisasi Matriks</h3>
		<table>
			<thead>
			<tr>
				<th>NIM</th>
				<th>Nama</th>
				<?php foreach (($preview['kriteria'] ?? []) as $k): ?>
					<th><?= esc((string) $k['kode']) ?> (<?= esc((string) ucfirst((string) $k['atribut'])) ?>)</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach (($preview['mahasiswa'] ?? []) as $m): ?>
				<tr>
					<td><?= esc((string) $m['nim']) ?></td>
					<td><?= esc((string) $m['nama']) ?></td>
					<?php foreach (($preview['kriteria'] ?? []) as $k): ?>
						<?php $mid = (int) $m['id']; $kid = (int) $k['id']; ?>
						<td><?= number_format((float) (($preview['normalisasi'][$mid][$kid] ?? 0)), 6) ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="card">
		<h3 class="panel-title"><i class="pi pi-sort-amount-down"></i> Nilai Preferensi & Ranking Saat Ini</h3>
		<table>
			<thead><tr><th>Ranking</th><th>NIM</th><th>Nama</th><th>Skor Preferensi</th><th>Status</th></tr></thead>
			<tbody>
			<?php foreach (($preview['ranking'] ?? []) as $r): ?>
				<tr>
					<td><span class="rank-chip <?= ((int) $r['ranking'] <= 3) ? 'rank-chip-top' : '' ?>">#<?= esc((string) $r['ranking']) ?></span></td>
					<td><?= esc((string) $r['nim']) ?></td>
					<td><?= esc((string) $r['nama']) ?></td>
					<td><?= number_format((float) $r['skor'], 6) ?></td>
					<td>
						<?php if ((string) $r['status_lolos'] === 'Lolos'): ?>
							<span class="status-badge status-badge-success"><i class="pi pi-check"></i> Lolos</span>
						<?php else: ?>
							<span class="status-badge status-badge-danger"><i class="pi pi-times"></i> Tidak Lolos</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
<?= $this->endSection() ?>
