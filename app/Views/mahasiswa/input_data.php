<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-2">
                        <i class="fas fa-user-plus text-primary"></i> Input Data Mahasiswa
                    </h3>
                    <p class="text-muted mb-0">
                        Masukkan data mahasiswa. Nilai per kriteria akan dihitung otomatis berdasarkan kriteria yang telah ditetapkan.
                    </p>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form Input Mahasiswa -->
            <form action="<?= base_url('mahasiswa/simpan'); ?>" method="POST" class="needs-validation" novalidate>
                <?= csrf_field(); ?>

                <!-- Data Identitas -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card"></i> Data Identitas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nim" class="form-label fw-bold">NIM <span class="text-danger">*</span></label>
                                    <input type="text" id="nim" name="nim" class="form-control form-control-lg" required>
                                    <small class="text-muted">Nomor Identitas Mahasiswa</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nama" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" id="nama" name="nama" class="form-control form-control-lg" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="semester" class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                                    <input type="number" id="semester" name="semester" class="form-control form-control-lg" min="1" required>
                                    <small class="text-muted">Contoh: 1, 2, 3</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="angkatan" class="form-label fw-bold">Angkatan <span class="text-danger">*</span></label>
                                    <input type="number" id="angkatan" name="angkatan" class="form-control form-control-lg" min="2000" max="2100" required>
                                    <small class="text-muted">Tahun angkatan, contoh: 2020</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kriteria 1: IPK (Benefit) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-book"></i> C1 - IPK (Benefit) - Bobot 35%</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="ipk" class="form-label fw-bold">Nilai IPK <span class="text-danger">*</span></label>
                            <input type="number" id="ipk" name="ipk" class="form-control form-control-lg" step="0.01" min="0" max="4" placeholder="0.00 - 4.00" required>
                            <small class="text-muted">
                                Range: 0.00 - 4.00
                                <br>Semakin tinggi IPK, semakin baik nilainya
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Kriteria 2: Penghasilan Orang Tua (Cost) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-money-bill"></i> C2 - Penghasilan Orang Tua (Cost) - Bobot 25%</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="penghasilan_ortu" class="form-label fw-bold">Penghasilan Orang Tua (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="penghasilan_ortu" name="penghasilan_ortu" class="form-control form-control-lg" placeholder="Contoh: 1500000" required>
                            <small class="text-muted">
                                Semakin rendah penghasilan, semakin baik nilainya (untuk prioritas beasiswa)
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Kriteria 3: Jumlah Tanggungan (Cost) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-people-carry"></i> C3 - Jumlah Tanggungan (Cost) - Bobot 20%</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="jumlah_tanggungan" class="form-label fw-bold">Jumlah Tanggungan Orang Tua <span class="text-danger">*</span></label>
                            <input type="number" id="jumlah_tanggungan" name="jumlah_tanggungan" class="form-control form-control-lg" min="0" placeholder="Contoh: 2, 3, 4" required>
                            <small class="text-muted">
                                Semakin banyak tanggungan, semakin baik nilainya (kebutuhan lebih tinggi)
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Kriteria 4: Prestasi Non-Akademik (Benefit) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> C4 - Prestasi Non-Akademik (Benefit) - Bobot 20%</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="prestasi" class="form-label fw-bold">Tingkat Prestasi <span class="text-danger">*</span></label>
                            <select id="prestasi" name="prestasi" class="form-select form-select-lg" required>
                                <option value="">-- Pilih Prestasi --</option>
                                <option value="Tidak berprestasi">Tidak berprestasi (0.25)</option>
                                <option value="Universitas">Prestasi Tingkat Universitas (0.50)</option>
                                <option value="Kota">Prestasi Tingkat Kota (0.50)</option>
                                <option value="Provinsi">Prestasi Tingkat Provinsi (0.75)</option>
                                <option value="Nasional">Prestasi Tingkat Nasional (1.00)</option>
                            </select>
                            <small class="text-muted">
                                Semakin tinggi prestasi, semakin baik nilainya
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-save"></i> Simpan Data Mahasiswa
                    </button>
                    <a href="<?= base_url('penilaian'); ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card-header {
    font-weight: 600;
}

.form-control-lg, .form-select-lg {
    font-size: 1rem;
    padding: 0.75rem 1rem;
}

.text-danger {
    font-weight: bold;
}
</style>

<?= $this->endSection(); ?>
