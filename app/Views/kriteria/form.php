<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2><?= esc($title) ?></h2>
<form action="<?= esc($action) ?>" method="post">
<?= csrf_field() ?>
<div class="form-group"><label>Kriteria</label><input type="text" name="kriteria" value="<?= esc((string) old('kriteria', $kriteria['kriteria'] ?? '')) ?>" required></div>
<div class="form-group"><label>Bobot</label><input type="number" step="0.0001" name="bobot" value="<?= esc((string) old('bobot', $kriteria['bobot'] ?? '0')) ?>" required></div>
<button class="btn" type="submit">Simpan</button>
<a class="btn btn-secondary" href="/kriteria">Kembali</a>
</form>
<?= $this->endSection() ?>
