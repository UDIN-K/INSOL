<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2><?= esc($title) ?></h2>
	<div class="dash-breadcrumb">Mahasiswa / Form</div>
</div>

<div class="card">
<form action="<?= esc($action) ?>" method="post" class="form-shell">
<?= csrf_field() ?>

<div class="form-card">
	<h3 class="form-card-title"><i class="pi pi-id-card"></i> Identitas Mahasiswa</h3>
	<div class="form-grid-2">
		<div class="form-group"><label>NIM</label><input type="text" name="nim" value="<?= esc((string) old('nim', $mahasiswa['nim'] ?? '')) ?>" required></div>
		<div class="form-group"><label>Nama</label><input type="text" name="nama" value="<?= esc((string) old('nama', $mahasiswa['nama'] ?? '')) ?>" required></div>
		<div class="form-group">
			<label>Jenis Kelamin</label>
			<?php $jk = old('jenis_kelamin', $mahasiswa['jenis_kelamin'] ?? ''); ?>
			<select name="jenis_kelamin">
				<option value="">-- Pilih Jenis Kelamin --</option>
				<option value="Laki-laki" <?= $jk === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
				<option value="Perempuan" <?= $jk === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
			</select>
		</div>
		<div class="form-group"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" value="<?= esc((string) old('tempat_lahir', $mahasiswa['tempat_lahir'] ?? '')) ?>"></div>
		<div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="<?= esc((string) old('tanggal_lahir', $mahasiswa['tanggal_lahir'] ?? '')) ?>"></div>
		<div class="form-group"><label>Telepon</label><input type="text" name="telepon" value="<?= esc((string) old('telepon', $mahasiswa['telepon'] ?? '')) ?>"></div>
		<div class="form-group"><label>Email</label><input type="email" name="email" value="<?= esc((string) old('email', $mahasiswa['email'] ?? '')) ?>"></div>
		<div class="form-group"><label>Alamat</label><input type="text" name="alamat" value="<?= esc((string) old('alamat', $mahasiswa['alamat'] ?? '')) ?>"></div>
	</div>
</div>

<div class="form-card">
	<h3 class="form-card-title"><i class="pi pi-book"></i> Data Akademik & Keluarga</h3>
	<div class="form-grid-2">
		<div class="form-group"><label>Prodi</label><input type="text" name="prodi" value="<?= esc((string) old('prodi', $mahasiswa['prodi'] ?? '')) ?>"></div>
		<div class="form-group"><label>Semester</label><input type="number" min="1" name="semester" value="<?= esc((string) old('semester', $mahasiswa['semester'] ?? '')) ?>"></div>
		<div class="form-group"><label>Tahun Masuk</label><input type="number" min="2000" name="tahun_masuk" value="<?= esc((string) old('tahun_masuk', $mahasiswa['tahun_masuk'] ?? '')) ?>"></div>
		<div class="form-group"><label>IPK</label><input type="number" step="0.01" min="0" max="4" name="ipk" value="<?= esc((string) old('ipk', $mahasiswa['ipk'] ?? '')) ?>"></div>
		<div class="form-group"><label>Nama Ibu</label><input type="text" name="nama_ibu" value="<?= esc((string) old('nama_ibu', $mahasiswa['nama_ibu'] ?? '')) ?>"></div>
		<div class="form-group"><label>Nama Bapak</label><input type="text" name="nama_bapak" value="<?= esc((string) old('nama_bapak', $mahasiswa['nama_bapak'] ?? '')) ?>"></div>
		<div class="form-group"><label>Penghasilan Orang Tua</label><input type="number" min="0" name="penghasilan_ortu" value="<?= esc((string) old('penghasilan_ortu', $mahasiswa['penghasilan_ortu'] ?? '')) ?>"></div>
		<div class="form-group"><label>Jumlah Tanggungan</label><input type="number" min="0" name="jumlah_tanggungan" value="<?= esc((string) old('jumlah_tanggungan', $mahasiswa['jumlah_tanggungan'] ?? '')) ?>"></div>
		<div class="form-group">
			<label>Prestasi Non Akademik</label>
			<?php $prestasi = old('prestasi_non_akademik', $mahasiswa['prestasi_non_akademik'] ?? ''); ?>
			<select name="prestasi_non_akademik">
				<option value="">-- Pilih Prestasi --</option>
				<option value="universitas" <?= $prestasi === 'universitas' ? 'selected' : '' ?>>Universitas</option>
				<option value="kota" <?= $prestasi === 'kota' ? 'selected' : '' ?>>Kota</option>
				<option value="provinsi" <?= $prestasi === 'provinsi' ? 'selected' : '' ?>>Provinsi</option>
				<option value="nasional" <?= $prestasi === 'nasional' ? 'selected' : '' ?>>Nasional</option>
				<option value="internasional" <?= $prestasi === 'internasional' ? 'selected' : '' ?>>Internasional</option>
			</select>
		</div>
	</div>
</div>

<div class="form-actions">
	<button class="btn" type="submit"><i class="pi pi-save"></i> Simpan</button>
	<a class="btn btn-secondary" href="/mahasiswa"><i class="pi pi-arrow-left"></i> Kembali</a>
</div>
</form>
</div>
<?= $this->endSection() ?>
