<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid mt-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="fas fa-eye"></i> Cek Nilai Penilaian
            </h2>
            <p class="text-muted">
                Verifikasi nilai mahasiswa untuk setiap kriteria sebelum melakukan perhitungan SAW
            </p>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list-check"></i> 
                Nilai Penilaian Mahasiswa per Kriteria
                <span class="badge bg-light text-dark float-end">
                    <?= count($mahasiswa); ?> mahasiswa
                </span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 250px;">
                                <i class="fas fa-user"></i> Mahasiswa
                            </th>
                            <?php foreach ($kriteria as $k): ?>
                                <th class="text-center" style="width: 140px;">
                                    <strong><?= $k['kode'] ?></strong>
                                    <br>
                                    <small><?= $k['kriteria']; ?></small>
                                    <br>
                                    <span class="badge bg-secondary"><?= number_format($k['bobot'] * 100, 0) ?>%</span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa as $m): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($m['nama']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        NIM: <?= esc($m['nim']); ?> | Sem: <?= $m['semester']; ?> | Angkatan: <?= esc((string) ($m['tahun_masuk'] ?? '-')); ?>
                                    </small>
                                </td>
                                <?php foreach ($kriteria as $k): ?>
                                    <td class="text-center align-middle">
                                        <h5 class="mb-0 text-info">
                                            <?php 
                                                $nilai = $nilaiTable[$m['id']][$k['id']] ?? 0;
                                                echo number_format($nilai, 4);
                                            ?>
                                        </h5>
                                        <small class="text-muted d-block mt-1">
                                            <?php 
                                                $atribut = strtoupper($k['atribut']);
                                                echo "({$atribut})";
                                            ?>
                                        </small>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <form id="formMulaiHitung" action="<?= base_url('penilaian/hitung-saw'); ?>" method="POST">
                <?= csrf_field(); ?>
                
                <!-- Hidden input untuk selected mahasiswa -->
                <?php foreach ($selectedMahasiswa as $mId): ?>
                    <input type="hidden" name="mahasiswa[]" value="<?= $mId; ?>">
                <?php endforeach; ?>

                <!-- Periode dan Threshold -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <label for="penilaian_ke" class="form-label fw-bold">Periode Penilaian</label>
                                <input type="number" id="penilaian_ke" name="penilaian_ke" class="form-control form-control-lg" value="1" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <label for="threshold" class="form-label fw-bold">Threshold Lolos</label>
                                <input type="number" id="threshold" name="threshold" class="form-control form-control-lg" value="0.65" step="0.01" min="0" max="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-play-circle"></i> Mulai Perhitungan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="<?= base_url('penilaian'); ?>" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Card -->
    <div class="alert alert-info" role="alert">
        <h5 class="alert-heading"><i class="fas fa-lightbulb"></i> Keterangan</h5>
        <ul class="mb-0">
            <li>Nilai di atas adalah hasil otomatis mapping dari data mahasiswa ke skala 0-1</li>
            <li>Klik tombol <strong>Mulai Perhitungan</strong> untuk melihat detail step-by-step perhitungan SAW</li>
            <li>Periode penil aian digunakan untuk tracking multiple evaluation cycles</li>
            <li>Threshold Lolos menentukan apakah mahasiswa lolos atau tidak berdasarkan skor akhir</li>
        </ul>
    </div>
</div>

<?= $this->endSection(); ?>
