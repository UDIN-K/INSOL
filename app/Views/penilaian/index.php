<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-3">
                <i class="fas fa-clipboard-list"></i> Input Penilaian Mahasiswa
            </h2>
            <p class="text-muted">
                Kelola data penilaian untuk semua kriteria. Kriteria aktif: <strong><?= esc((string) $jumlahKriteria) ?></strong>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('penilaian/form-hitung-saw'); ?>" class="btn btn-success btn-lg">
                <i class="fas fa-calculator"></i> Mulai Perhitungan SAW
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Mahasiswa</h5>
                    <h3 class="text-primary"><?= count($mahasiswa) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-muted">Penilaian Lengkap</h5>
                    <h3 class="text-success">
                        <?php 
                            $lengkap = array_filter($mahasiswa, fn($m) => $m['penilaian_lengkap']);
                            echo count($lengkap);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-muted">Belum Lengkap</h5>
                    <h3 class="text-warning">
                        <?php 
                            $belum = array_filter($mahasiswa, fn($m) => !$m['penilaian_lengkap']);
                            echo count($belum);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Mahasiswa Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> Daftar Mahasiswa</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Status Penilaian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mahasiswa)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> Belum ada data mahasiswa
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach ($mahasiswa as $i => $item): ?>
                            <tr>
                                <td class="ps-4"><?= $i + 1 ?></td>
                                <td><strong><?= esc($item['nim']) ?></strong></td>
                                <td><?= esc($item['nama']) ?></td>
                                <td>
                                    <?php if ($item['penilaian_lengkap']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Lengkap
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock"></i> Belum Lengkap
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('penilaian/input/' . $item['id']); ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Input/Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info mt-4" role="alert">
        <h5 class="alert-heading"><i class="fas fa-lightbulb"></i> Informasi</h5>
        <ul class="mb-0">
            <li>Klik tombol <strong>Input/Edit</strong> untuk mengisi atau mengubah penilaian mahasiswa</li>
            <li>Penilaian dianggap <strong>Lengkap</strong> jika semua criterianya sudah diisi</li>
            <li>Klik tombol <strong>Mulai Perhitungan SAW</strong> di atas untuk menghitung hasil dengan metode SAW</li>
            <li>Minimal <strong>1 mahasiswa</strong> dengan penilaian lengkap diperlukan untuk melakukan perhitungan</li>
        </ul>
    </div>
</div>

<?= $this->endSection() ?>
