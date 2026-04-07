<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Riwayat Hasil Ranking SAW</h2>
	<div class="toolbar-actions no-print">
		<button type="button" class="btn btn-ghost" onclick="window.print()"><i class="pi pi-print"></i> Cetak Laporan</button>
		<div class="dash-breadcrumb">Hasil / Riwayat Ranking</div>
	</div>
</div>

<div style="margin-bottom: 20px;">
	<a href="/hasil" class="btn btn-secondary">← Kembali ke Proses SAW</a>
</div>

<?php if (empty($groupedByPenilaian)): ?>
	<div class="card" style="text-align: center; padding: 40px; color: #999;">
		<p><i class="pi pi-inbox" style="font-size: 48px;"></i></p>
		<p>Belum ada riwayat hasil ranking</p>
	</div>
<?php else: ?>
	<?php foreach ($groupedByPenilaian as $penilaianKe => $rows): ?>
		<div class="card" style="margin-bottom: 20px;">
			<h3 class="panel-title" style="margin-bottom: 15px;">
				<i class="pi pi-history"></i> Penilaian Ke-<?= esc((string) $penilaianKe) ?>
			</h3>
			
			<table>
				<thead>
					<tr>
						<th>Ranking</th>
						<th>NIM</th>
						<th>Nama</th>
						<th>Skor</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $item): ?>
						<tr>
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
	<?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
