<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2>Hasil Ranking SAW</h2>
	<div class="toolbar-actions no-print">
		<button type="button" class="btn btn-ghost" onclick="window.print()"><i class="pi pi-print"></i> Cetak Laporan</button>
		<div class="dash-breadcrumb">Hasil / Ranking Seleksi</div>
	</div>
</div>

<div style="margin-bottom: 20px;">
	<a href="/hasil/history" class="btn btn-secondary"><i class="pi pi-history"></i> Lihat Riwayat Hasil</a>
</div>

<div class="card" style="margin-bottom:12px;">
	<h3 class="panel-title"><i class="pi pi-list-check"></i> Pilih Mahasiswa untuk Diproses SAW</h3>
	
	<!-- FORM FILTER - menggunakan GET -->
	<form method="get" action="/hasil" id="filterForm" style="margin-bottom: 15px;">
		<div style="padding: 12px; background-color: #f5f5f5; border-radius: 4px;">
			<div style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 10px; margin-bottom: 12px; align-items: end;">
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
				<button class="btn btn-secondary" type="submit">Cari</button>
				<button class="btn btn-secondary" type="button" onclick="resetFilters()">Reset</button>
			</div>
			<p style="margin: 0; font-size: 0.9em; color: #666;">
				<strong id="selectedCount">0</strong> dari <strong><?= count($mahasiswa) ?></strong> mahasiswa dipilih | Jumlah Kriteria: <strong><?= esc((string) $jumlahKriteria) ?></strong>
			</p>
		</div>
	</form>
		
	<!-- FORM PROSES SAW - menggunakan POST -->
	<form action="/hasil/proses" method="post" id="processingForm">
		<?= csrf_field() ?>
		
		<!-- Pilih Semua dan Skor Minimum Lolos -->
		<div style="display: grid; grid-template-columns: auto auto; gap: 12px; align-items: center; margin-top: 8px; margin-bottom: 8px; font-size: 0.9em;">
			<div style="display: flex; align-items: center; gap: 5px;">
				<label style="margin: 0;">Skor Minimum Lolos</label>
				<input type="number" name="skor_minimum" min="0" max="1" step="0.01" value="0.5" required style="width: 80px;">
			</div>
		</div>
		
		
		<table style="margin-bottom: 15px;">
			<thead>
				<tr>
					<th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"></th>
					<th style="width: 40px;">No</th>
					<th>NIM</th>
					<th>Nama</th>
					<th>Semester</th>
					<th>IPK</th>
					<th>Tanggungan Ortu</th>
					<th>Penghasilan Ortu</th>
					<th>Prestasi Non Akademik</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($mahasiswa)): ?>
					<tr><td colspan="9" style="text-align: center; color: #999;">Tidak ada data mahasiswa yang sesuai.</td></tr>
				<?php else: ?>
					<?php foreach ($mahasiswa as $i => $item): ?>
						<tr>
							<td>
								<input type="checkbox" name="mahasiswa_ids[]" value="<?= esc((string) $item['id']) ?>" class="selectCheckbox" onchange="updateSelectedCount()">
							</td>
							<td><?= $i + 1 ?></td>
							<td><?= esc($item['nim']) ?></td>
							<td><?= esc($item['nama']) ?></td>
							<td><?= esc((string) ($item['semester'] ?? '-')) ?></td>
							<td><?= esc((string) ($item['ipk'] ?? '-')) ?></td>
							<td><?= esc((string) ($item['jumlah_tanggungan'] ?? '-')) ?></td>
							<td><?= esc((string) ($item['penghasilan_ortu'] ?? '-')) ?></td>
							<td><?= esc((string) ($item['prestasi_non_akademik'] ?? '-')) ?></td>

						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<div style="display: flex; gap: 12px; align-items: center;">
			<button class="btn btn-success" type="submit" id="processBtn" disabled><i class="pi pi-play"></i> Proses SAW & Simpan</button>
			<small style="color: #999;" id="processHint">Pilih minimal satu mahasiswa untuk memproses</small>
		</div>
	</form>
</div>

<script>
function updateSelectedCount() {
	const checkboxes = document.querySelectorAll('input[name="mahasiswa_ids[]"]');
	const selected = document.querySelectorAll('input[name="mahasiswa_ids[]"]:checked').length;
	document.getElementById('selectedCount').textContent = selected;
	
	const processBtn = document.getElementById('processBtn');
	const processHint = document.getElementById('processHint');
	
	if (selected > 0) {
		processBtn.disabled = false;
		processHint.textContent = selected + ' mahasiswa dipilih untuk diproses';
		processHint.style.color = '#4caf50';
	} else {
		processBtn.disabled = true;
		processHint.textContent = 'Pilih minimal satu mahasiswa untuk memproses';
		processHint.style.color = '#999';
	}
	
	// Update select all checkbox
	const selectAll = document.getElementById('selectAllBtn');
	if (selectAll) {
		selectAll.checked = selected === checkboxes.length && checkboxes.length > 0;
	}
}

function toggleSelectAll(checkbox) {
	const checkboxes = document.querySelectorAll('input[name="mahasiswa_ids[]"]');
	checkboxes.forEach(cb => {
		cb.checked = checkbox.checked;
	});
	updateSelectedCount();
}

function resetFilters() {
	document.getElementById('filterForm').reset();
	document.getElementById('filterForm').submit();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
	updateSelectedCount();
});
</script>

<style>
input[type="checkbox"]:disabled {
	cursor: not-allowed;
	opacity: 0.5;
}

button:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

table th:first-child {
	text-align: center;
	width: 40px;
}
</style>
<?= $this->endSection() ?>
