<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-2">
                        <i class="fas fa-calculator text-primary"></i> Perhitungan SAW
                    </h3>
                    <p class="text-muted mb-0">
                        Pilih mahasiswa yang sudah memiliki penilaian lengkap untuk dihitung dengan metode SAW
                    </p>
                </div>
            </div>

            <!-- Form Hitung SAW -->
            <form action="<?= base_url('penilaian/hitung-saw'); ?>" method="POST" class="needs-validation">
                <?= csrf_field(); ?>

                <!-- Periode Penilaian -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Konfigurasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penilaian_ke" class="form-label fw-bold">Periode Penilaian</label>
                                    <input type="number" 
                                           id="penilaian_ke" 
                                           name="penilaian_ke" 
                                           class="form-control form-control-lg" 
                                           value="1" 
                                           min="1" 
                                           required>
                                    <small class="text-muted">Masukkan nomor periode penilaian</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="threshold" class="form-label fw-bold">Threshold Lolos</label>
                                    <input type="number" 
                                           id="threshold" 
                                           name="threshold" 
                                           class="form-control form-control-lg" 
                                           value="0.65" 
                                           step="0.01" 
                                           min="0" 
                                           max="1" 
                                           required>
                                    <small class="text-muted">Range: 0.00 - 1.00</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Select Mahasiswa -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Pilih Mahasiswa</h5>
                            <small class="badge bg-light text-dark">Total: <?= $totalMahasiswa; ?> mahasiswa</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mahasiswa)): ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Tidak ada mahasiswa dengan penilaian lengkap!</strong>
                                <p class="mb-0 mt-2">Silakan lengkapi penilaian terlebih dahulu sebelum melakukan perhitungan SAW.</p>
                            </div>
                        <?php else: ?>
                            <!-- Select All Checkbox -->
                            <div class="form-check mb-3 pb-3 border-bottom">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="selectAll" 
                                       name="select_all"
                                       onclick="toggleSelectAll(this)">
                                <label class="form-check-label" for="selectAll">
                                    <strong>Pilih Semua Mahasiswa</strong>
                                </label>
                            </div>

                            <!-- Mahasiswa List -->
                            <div class="mahasiswa-list">
                                <?php foreach ($mahasiswa as $index => $m): ?>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input mahasiswa-checkbox" 
                                               type="checkbox" 
                                               id="mhs_<?= $m['id']; ?>" 
                                               name="mahasiswa[]" 
                                               value="<?= $m['id']; ?>">
                                        <label class="form-check-label w-100" for="mhs_<?= $m['id']; ?>">
                                            <div class="ps-2">
                                                <div>
                                                    <strong><?= $m['nama']; ?></strong>
                                                    <span class="badge bg-info text-dark ms-2"><?= $m['nim']; ?></span>
                                                </div>
                                                <small class="text-muted">
                                                    IPK: <?= number_format($m['ipk'], 2); ?> | 
                                                    Penghasilan: Rp<?= number_format($m['penghasilan_ortu'], 0, ',', '.'); ?> | 
                                                    Tanggungan: <?= $m['jumlah_tanggungan']; ?> | 
                                                    Prestasi: <?= $m['prestasi']; ?>
                                                </small>
                                            </div>
                                        </label>
                                    </div>
                                    <hr class="my-2">
                                <?php endforeach; ?>
                            </div>

                            <!-- Summary Selected -->
                            <div class="alert alert-info mt-4" role="alert">
                                <span id="selectedCount">0</span> dari <strong><?= count($mahasiswa); ?></strong> mahasiswa dipilih
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if (!empty($mahasiswa)): ?>
                    <div class="d-flex gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                            <i class="fas fa-play-circle"></i> Mulai Perhitungan SAW
                        </button>
                        <a href="<?= base_url('penilaian'); ?>" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times-circle"></i> Batal
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.mahasiswa-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.mahasiswa-checkbox');
    const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
    document.getElementById('selectedCount').textContent = checked;
}

// Update count saat checkbox diklik
document.querySelectorAll('.mahasiswa-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

// Update on page load
updateSelectedCount();
</script>

<style>
.mahasiswa-list .form-check {
    padding: 12px;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.mahasiswa-list .form-check:hover {
    background-color: #f8f9fa;
}

.mahasiswa-list .form-check input:checked + label {
    color: #0d6efd;
    font-weight: 500;
}
</style>
<?= $this->endSection(); ?>
