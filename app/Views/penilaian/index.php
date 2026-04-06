<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="fas fa-check-circle"></i> Penilaian Mahasiswa
            </h2>
            <p class="text-muted">
                Pilih mahasiswa untuk dihitung dengan metode SAW. Nilai per kriteria telah dihitung otomatis saat input data.
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

    <!-- Form Select Mahasiswa -->
    <form id="formSelectMahasiswa" method="POST" action="<?= base_url('penilaian/cek-penilaian'); ?>" class="needs-validation" novalidate>
        <?= csrf_field(); ?>

        <!-- Filter dan Actions Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterSemester" class="form-label fw-bold">Filter Semester</label>
                            <select id="filterSemester" class="form-select form-select-lg" onchange="filterBySemester(this.value)">
                                <option value="">-- Semua Semester --</option>
                                <?php 
                                $semesters = [];
                                foreach ($mahasiswa as $m) {
                                    $semesters[$m['semester']] = true;
                                }
                                ksort($semesters);
                                foreach (array_keys($semesters) as $sem): 
                                ?>
                                    <option value="<?= $sem; ?>">Semester <?= $sem; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold" style="visibility: hidden;">Actions</label>
                            <a href="<?= base_url('mahasiswa/input-data'); ?>" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-plus-circle"></i> Input Data Baru
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-lg w-100" onclick="return validateSelection()">
                                <i class="fas fa-arrow-right"></i> Cek Penilaian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mahasiswa Selection Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Pilih Mahasiswa</h5>
                    <span id="selectedCount" class="badge bg-light text-dark">0 dipilih</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($mahasiswa)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Belum ada mahasiswa dengan penilaian lengkap</p>
                        <a href="<?= base_url('mahasiswa/input-data'); ?>" class="btn btn-success">
                            <i class="fas fa-plus"></i> Input Data Mahasiswa
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Select All -->
                    <div class="p-4 border-bottom bg-light">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            <label class="form-check-label fw-bold" for="selectAll">
                                Pilih Semua Mahasiswa
                            </label>
                        </div>
                    </div>

                    <!-- Mahasiswa List -->
                    <div class="p-0">
                        <?php foreach ($mahasiswa as $m): ?>
                            <div class="p-4 border-bottom mahasiswa-item" data-semester="<?= $m['semester']; ?>">
                                <div class="form-check">
                                    <input class="form-check-input mahasiswa-checkbox" 
                                           type="checkbox" 
                                           id="mhs_<?= $m['id']; ?>" 
                                           name="mahasiswa[]" 
                                           value="<?= $m['id']; ?>"
                                           onchange="updateSelectedCount()">
                                    <label class="form-check-label w-100" for="mhs_<?= $m['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= $m['nama']; ?></h6>
                                                <small class="text-muted">
                                                    NIM: <strong><?= $m['nim']; ?></strong> | 
                                                    Semester: <strong><?= $m['semester']; ?></strong> | 
                                                    Angkatan: <strong><?= $m['angkatan']; ?></strong>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-info">IPK: <?= number_format($m['ipk'], 2); ?></span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Alert for no selection -->
    <div id="alertNoSelection" class="alert alert-warning alert-dismissible fade show mt-4" role="alert" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Perhatian!</strong> Silakan pilih minimal 1 mahasiswa untuk melanjutkan.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>

<style>
.mahasiswa-item {
    transition: background-color 0.2s ease;
}

.mahasiswa-item:hover {
    background-color: #f8f9fa;
}

.mahasiswa-item .form-check-input:checked + label {
    color: #0d6efd;
    font-weight: 500;
}
</style>

<script>
function filterBySemester(semester) {
    const items = document.querySelectorAll('.mahasiswa-item');
    items.forEach(item => {
        if (!semester || item.dataset.semester === semester) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function toggleSelectAll(checkbox) {
    const visibleCheckboxes = document.querySelectorAll('.mahasiswa-item:not([style*="display: none"]) .mahasiswa-checkbox');
    visibleCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.mahasiswa-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' dipilih';
}

function validateSelection() {
    const checked = document.querySelectorAll('.mahasiswa-checkbox:checked').length;
    if (checked === 0) {
        document.getElementById('alertNoSelection').style.display = 'block';
        window.scrollTo(0, document.getElementById('alertNoSelection').offsetTop - 100);
        return false;
    }
    return true;
}

// Initialize on page load
updateSelectedCount();
</script>

<?= $this->endSection() ?>
