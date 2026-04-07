<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="dash-title-row">
    <h2><?= esc($title) ?></h2>
    <div class="dash-breadcrumb">Kriteria / Detail Form</div>
</div>

<div class="card">
<form action="<?= esc($action) ?>" method="post" class="form-shell">
<?= csrf_field() ?>
<div class="form-card">
    <h3 class="form-card-title"><i class="pi pi-list"></i> Detail Sub-Kriteria</h3>
    <div class="form-grid-2">
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
        <div class="form-group"><label>Sub Kriteria (text)</label><input type="text" name="sub_kriteria" value="<?= esc((string) old('sub_kriteria', $detail['sub_kriteria'] ?? '')) ?>"></div>
        <div class="form-group">
            <label>Jenis Kondisi</label>
            <?php $jenis = old('jenis_kondisi', $detail['jenis_kondisi'] ?? 'text'); ?>
            <select name="jenis_kondisi" required>
                <option value="text" <?= $jenis === 'text' ? 'selected' : '' ?>>Text</option>
                <option value="range" <?= $jenis === 'range' ? 'selected' : '' ?>>Range (min-max)</option>
                <option value="eq" <?= $jenis === 'eq' ? 'selected' : '' ?>>Sama Dengan (=)</option>
                <option value="gt" <?= $jenis === 'gt' ? 'selected' : '' ?>>Lebih Besar (>)</option>
                <option value="gte" <?= $jenis === 'gte' ? 'selected' : '' ?>>Lebih Besar Sama Dengan (>=)</option>
                <option value="lt" <?= $jenis === 'lt' ? 'selected' : '' ?>>Lebih Kecil (<)</option>
                <option value="lte" <?= $jenis === 'lte' ? 'selected' : '' ?>>Lebih Kecil Sama Dengan (<=)</option>
            </select>
        </div>
        <div class="form-group"><label>Nilai</label><input type="number" step="0.0001" name="nilai" value="<?= esc((string) old('nilai', $detail['nilai'] ?? '0')) ?>" required></div>
        <div class="form-group"><label>Batas Bawah (angka)</label><input type="number" step="0.0001" name="batas_bawah" value="<?= esc((string) old('batas_bawah', $detail['batas_bawah'] ?? '')) ?>"></div>
        <div class="form-group"><label>Batas Atas (angka)</label><input type="number" step="0.0001" name="batas_atas" value="<?= esc((string) old('batas_atas', $detail['batas_atas'] ?? '')) ?>"></div>
    </div>
</div>
<div class="form-actions">
    <button class="btn" type="submit"><i class="pi pi-save"></i> Simpan</button>
    <a class="btn btn-secondary" href="/kriteria"><i class="pi pi-arrow-left"></i> Kembali</a>
</div>
</form>
</div>
<?= $this->endSection() ?>
