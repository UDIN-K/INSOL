<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2><?= esc($title) ?></h2>
<form action="<?= esc($action) ?>" method="post">
<?= csrf_field() ?>
<div class="form-group">
    <label>Kriteria</label>
    <?php $selectedKriteria = (string) old('kriteria_id', $detail['kriteria_id'] ?? ''); ?>
    <select name="kriteria_id" required>
        <option value="">-- Pilih Kriteria --</option>
        <?php foreach ($kriteria as $item): ?>
            <option value="<?= esc((string) $item['id']) ?>" <?= $selectedKriteria === (string) $item['id'] ? 'selected' : '' ?>>
                <?= esc($item['kode']) ?> - <?= esc($item['kriteria']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group"><label>Sub Kriteria</label><input type="text" name="sub_kriteria" value="<?= esc((string) old('sub_kriteria', $detail['sub_kriteria'] ?? '')) ?>" required></div>
<div class="form-group"><label>Nilai</label><input type="number" step="0.0001" name="nilai" value="<?= esc((string) old('nilai', $detail['nilai'] ?? '0')) ?>" required></div>
<button class="btn" type="submit">Simpan</button>
<a class="btn btn-secondary" href="/kriteria">Kembali</a>
</form>
<?= $this->endSection() ?>
