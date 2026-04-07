<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Pilih Mahasiswa untuk Dinilai</h2>
	<div class="dash-breadcrumb">Proses / Penilaian</div>
</div>

<div class="card">
	<form method="get" action="/penilaian/cari" id="filterForm" style="margin-bottom: 20px;">
		<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
			<div>
				<label for="search">Cari (NIM/Nama):</label>
				<input type="text" id="search" name="search" placeholder="Masukkan NIM atau Nama..." value="<?= esc($search ?? '') ?>">
			</div>
			<div>
				<label for="semester">Filter Semester:</label>
				<select id="semester" name="semester">
					<option value="">-- Semua Semester --</option>
					<?php foreach ($semesters as $sem): ?>
						<option value="<?= esc((string) $sem['semester']) ?>" <?= ($semester ?? '') === (string) $sem['semester'] ? 'selected' : '' ?>>
							Semester <?= esc((string) $sem['semester']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<button class="btn" type="submit">Cari</button>
			</div>
		</div>
	</form>

	<?php if (isset($mahasiswa)): ?>
		<div style="margin-top: 20px;">
			<p>Ditemukan <strong><?= count($mahasiswa) ?></strong> mahasiswa</p>
			<?php if (empty($mahasiswa)): ?>
				<p style="color: #999;">Tidak ada data mahasiswa yang sesuai dengan kriteria pencarian.</p>
			<?php else: ?>
				<table>
					<thead>
						<tr>
							<th>No</th>
							<th>NIM</th>
							<th>Nama</th>
							<th>Semester</th>
							<th>Status Penilaian</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($mahasiswa as $i => $item): ?>
							<tr>
								<td><?= $i + 1 ?></td>
								<td><?= esc($item['nim']) ?></td>
								<td><?= esc($item['nama']) ?></td>
								<td><?= esc((string) ($item['semester'] ?? '-')) ?></td>
								<td>
									<?php if ($item['penilaian_lengkap']): ?>
										<span class="status-badge status-badge-success"><i class="pi pi-check-circle"></i> Lengkap</span>
									<?php else: ?>
										<span class="status-badge status-badge-warning"><i class="pi pi-exclamation-circle"></i> Belum</span>
									<?php endif; ?>
								</td>
								<td>
									<a class="btn" href="/penilaian/input/<?= $item['id'] ?>">Nilai</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<div style="text-align: center; padding: 40px; color: #999;">
			<p><i class="pi pi-search" style="font-size: 48px;"></i></p>
			<p>Gunakan form di atas untuk mencari dan memilih mahasiswa yang akan dinilai</p>
		</div>
	<?php endif; ?>
</div>
<?= $this->endSection() ?>
