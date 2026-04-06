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

        <!-- Mahasiswa Selection Table -->
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
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                        </div>
                                    </th>
                                    <th width="50">No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th width="100">Semester</th>
                                    <th width="120">Tahun Masuk</th>
                                    <th width="80">IPK</th>
                                    <th>Penghasilan Ortu</th>
                                    <th width="100">Tanggungan</th>
                                    <th>Prestasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa as $idx => $m): ?>
                                    <tr class="mahasiswa-item" data-semester="<?= $m['semester']; ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input mahasiswa-checkbox" 
                                                       type="checkbox" 
                                                       id="mhs_<?= $m['id']; ?>" 
                                                       name="mahasiswa[]" 
                                                       value="<?= $m['id']; ?>"
                                                       onchange="updateSelectedCount()">
                                            </div>
                                        </td>
                                        <td><?= $idx + 1; ?></td>
                                        <td><strong><?= esc($m['nim']); ?></strong></td>
                                        <td><?= esc($m['nama']); ?></td>
                                        <td><?= esc((string) ($m['semester'] ?? '-')); ?></td>
                                        <td><?= esc((string) ($m['tahun_masuk'] ?? '-')); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= number_format($m['ipk'] ?? 0, 2); ?>
                                            </span>
                                        </td>
                                        <td><?= esc((string) number_format((int) ($m['penghasilan_ortu'] ?? 0))); ?></td>
                                        <td><?= esc((string) ($m['jumlah_tanggungan'] ?? '-')); ?></td>
                                        <td><?= esc((string) ($m['prestasi_non_akademik'] ?? '-')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
