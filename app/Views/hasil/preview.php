<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Proses Perhitungan SAW</h2>
	<div class="toolbar-actions no-print">
		<button type="button" class="btn btn-ghost" onclick="window.print()"><i class="pi pi-print"></i> Cetak Laporan</button>
		<div class="dash-breadcrumb">Hasil / Proses SAW</div>
	</div>
</div>

<div class="card" style="margin-bottom: 20px;">
	<!-- Nama Kriteria dan Skor Minimum -->
	<div style="margin-bottom: 15px; padding: 12px; background-color: #f0f0f0; border-radius: 4px;">
		<div style="margin-bottom: 8px;">
			<strong>Nama Kriteria:</strong>
			<?php foreach ($kriteria as $k): ?>
				<?= esc((string) $k['kode']) . ' : ' . esc((string) $k['kriteria']) ?>;
			<?php endforeach; ?>
		</div>
		<div>
			<strong>Skor Minimum Lolos:</strong> <?= number_format((float) ($skorMinimum ?? 0.5), 2) ?>
		</div>
	</div>

	<!-- TABLE 1: Data Mentah Mahasiswa per Kriteria -->
	<h3 style="margin-top: 20px; margin-bottom: 10px;"><i class="pi pi-table"></i> Tabel 1: Data Mahasiswa dengan Nilai Mentah</h3>
	<div style="overflow-x: auto; margin-bottom: 20px;">
		<table style="width: 100%; border-collapse: collapse;">
			<thead style="background-color: #ffd700;">
				<tr>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">No.</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">NIM</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Nama</th>
					<?php foreach ($kriteria as $k): ?>
						<th style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $k['kode']) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($mahasiswa as $i => $mhs): ?>
					<tr>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= $i + 1 ?></td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $mhs['nim']) ?></td>
						<td style="border: 1px solid #999; padding: 8px;"><?= esc((string) $mhs['nama']) ?></td>
						<?php foreach ($kriteria as $k): ?>
							<td style="border: 1px solid #999; padding: 8px; text-align: center;">
								<?= number_format((float) ($nilai[(int) $mhs['id']][(int) $k['id']] ?? 0), 2) ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- TABLE 2: Nilai Normalisasi Matriks -->
	<h3 style="margin-top: 20px; margin-bottom: 10px;"><i class="pi pi-table"></i> Tabel 2: Hasil Normalisasi Matriks</h3>
	<div style="overflow-x: auto; margin-bottom: 20px;">
		<table style="width: 100%; border-collapse: collapse;">
			<thead style="background-color: #ffff99;">
				<tr>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">No.</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">NIM</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Nama</th>
					<?php foreach ($kriteria as $k): ?>
						<th style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $k['kode']) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($mahasiswa as $i => $mhs): ?>
					<tr>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= $i + 1 ?></td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $mhs['nim']) ?></td>
						<td style="border: 1px solid #999; padding: 8px;"><?= esc((string) $mhs['nama']) ?></td>
						<?php foreach ($kriteria as $k): ?>
							<td style="border: 1px solid #999; padding: 8px; text-align: center;">
								<?= number_format((float) ($normalisasi[(int) $mhs['id']][(int) $k['id']] ?? 0), 6) ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- TABLE 3: Hasil Perkalian Bobot dan Normalisasi -->
	<h3 style="margin-top: 20px; margin-bottom: 10px;"><i class="pi pi-table"></i> Tabel 3: Hasil Perkalian Matriks dan Bobot</h3>
	<div style="overflow-x: auto; margin-bottom: 20px;">
		<table style="width: 100%; border-collapse: collapse;">
			<thead style="background-color: #90ee90;">
				<tr>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">No.</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">NIM</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Nama</th>
					<?php foreach ($kriteria as $k): ?>
						<th style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $k['kode']) ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($mahasiswa as $i => $mhs): ?>
					<tr>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= $i + 1 ?></td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $mhs['nim']) ?></td>
						<td style="border: 1px solid #999; padding: 8px;"><?= esc((string) $mhs['nama']) ?></td>
						<?php foreach ($kriteria as $k): ?>
							<td style="border: 1px solid #999; padding: 8px; text-align: center;">
								<?php 
									$kid = (int) $k['id'];
									$mid = (int) $mhs['id'];
									$terbobot = (float) ($normalisasi[$mid][$kid] ?? 0) * ((float) $k['bobot'] / $bobotTotal);
									echo number_format($terbobot, 6);
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- TABLE 4: Ranking Hasil Penilaian -->
	<h3 style="margin-top: 20px; margin-bottom: 10px;"><i class="pi pi-table"></i> Tabel 4: Ranking Hasil Penilaian</h3>
	<div style="overflow-x: auto; margin-bottom: 20px;">
		<table style="width: 100%; border-collapse: collapse;">
			<thead style="background-color: #ffb6c1;">
				<tr>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Ranking</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">NIM</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Nama</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Skor</th>
					<th style="border: 1px solid #999; padding: 8px; text-align: center;">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($ranking as $item): ?>
					<tr>
						<td style="border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold;">
							<?php if ($item['ranking'] <= 3): ?>
								<span style="background-color: #ffd700; padding: 4px 8px; border-radius: 4px;">
									<?= $item['ranking'] ?>
								</span>
							<?php else: ?>
								<?= $item['ranking'] ?>
							<?php endif; ?>
						</td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= esc((string) $item['nim']) ?></td>
						<td style="border: 1px solid #999; padding: 8px;"><?= esc((string) $item['nama']) ?></td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;"><?= number_format((float) $item['skor'], 6) ?></td>
						<td style="border: 1px solid #999; padding: 8px; text-align: center;">
							<?php if ($item['status_lolos'] === 'Lolos'): ?>
								<span style="background-color: #4caf50; color: white; padding: 4px 8px; border-radius: 4px;">
									<?= esc((string) $item['status_lolos']) ?>
								</span>
							<?php else: ?>
								<span style="background-color: #f44336; color: white; padding: 4px 8px; border-radius: 4px;">
									<?= esc((string) $item['status_lolos']) ?>
								</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- Action Buttons -->
	<div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
		<form method="post" action="/hasil/confirm" style="display: inline;">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyimpan hasil perhitungan ini?')">
				<i class="pi pi-check"></i> Lanjutkan ke Ranking Hasil
			</button>
		</form>
		<a href="/hasil/cancel" class="btn btn-secondary" onclick="return confirm('Batalkan proses dan kembali ke pemilihan mahasiswa?')">
			<i class="pi pi-times"></i> Kembali ke Pemilihan
		</a>
	</div>
</div>

<?= $this->endSection() ?>
