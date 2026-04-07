<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
	<h2><?= esc($title) ?></h2>
	<div class="dash-breadcrumb">Kriteria / Form</div>
</div>

<div class="card">
<form action="<?= esc($action) ?>" method="post" class="form-shell">
<?= csrf_field() ?>
<div class="form-card">
	<h3 class="form-card-title"><i class="pi pi-sliders-h"></i> Konfigurasi Kriteria</h3>
	<div class="form-grid-2">
		<div class="form-group"><label>Nomor (C1/C2/C3)</label><input type="text" name="kode" value="<?= esc((string) old('kode', $kriteria['kode'] ?? '')) ?>" placeholder="C1" required></div>
		<div class="form-group"><label>Kriteria</label><input type="text" name="kriteria" value="<?= esc((string) old('kriteria', $kriteria['kriteria'] ?? '')) ?>" required></div>
		<div class="form-group"><label>Bobot</label><input type="number" step="0.0001" name="bobot" value="<?= esc((string) old('bobot', $kriteria['bobot'] ?? '0')) ?>" required></div>
		<div class="form-group">
			<label>Atribut</label>
			<?php $atribut = old('atribut', $kriteria['atribut'] ?? 'benefit'); ?>
			<select name="atribut" required>
				<option value="benefit" <?= $atribut === 'benefit' ? 'selected' : '' ?>>Benefit</option>
				<option value="cost" <?= $atribut === 'cost' ? 'selected' : '' ?>>Cost</option>
			</select>
		</div>
	</div>
</div>
<div class="form-actions">
	<button class="btn" type="submit"><i class="pi pi-save"></i> Simpan</button>
	<a class="btn btn-secondary" href="/kriteria"><i class="pi pi-arrow-left"></i> Kembali</a>
</div>
</form>
</div>
<?= $this->endSection() ?>
