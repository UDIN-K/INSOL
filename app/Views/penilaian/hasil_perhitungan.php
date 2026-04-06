<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Perhitungan SAW - Periode <?= $penilaian_ke ?></h2>
    
    <?php if ($result['success']): ?>
        <div class="alert alert-success">
            <strong>✓ Perhitungan Berhasil!</strong> 
            Total <?= $result['total_mahasiswa'] ?> mahasiswa telah diproses.
        </div>

        <!-- STEP 1: MATRIKS KEPUTUSAN -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">STEP 1: Matriks Keputusan (X)</h5>
                <small><?= $result['steps'][0]['description'] ?></small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="background-color: #f8f9fa;">Mahasiswa</th>
                                <?php foreach ($result['steps'][0]['data']['headers'] as $header): ?>
                                    <th class="text-center"><?= $header ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['steps'][0]['data']['rows'] as $mId => $nilai): ?>
                                <tr>
                                    <td><strong><?= $result['steps'][0]['mahasiswa_names'][$mId] ?? 'Unknown' ?></strong></td>
                                    <?php foreach ($result['steps'][0]['data']['headers'] as $idx => $header): ?>
                                        <td class="text-center">
                                            <?php 
                                                // Get kriteria_id from index
                                                $kriteriaId = $idx + 1;
                                                echo isset($nilai[$kriteriaId]) ? number_format($nilai[$kriteriaId], 4) : '0.0000';
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 2: MATRIKS NORMALISASI -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">STEP 2: Matriks Normalisasi (R)</h5>
                <small><?= $result['steps'][1]['description'] ?></small>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <strong>Rumus Normalisasi:</strong>
                    <ul class="mb-0">
                        <li><strong>Benefit:</strong> r(i,j) = X(i,j) / MAX(Xj)</li>
                        <li><strong>Cost:</strong> r(i,j) = MIN(Xj) / X(i,j)</li>
                    </ul>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="background-color: #f8f9fa;">Mahasiswa</th>
                                <?php foreach ($result['steps'][1]['data']['headers'] as $header): ?>
                                    <th class="text-center"><?= $header ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['steps'][1]['data']['rows'] as $mId => $nilai): ?>
                                <tr>
                                    <td><strong><?= $result['steps'][1]['mahasiswa_names'][$mId] ?? 'Unknown' ?></strong></td>
                                    <?php foreach ($result['steps'][1]['data']['headers'] as $idx => $header): ?>
                                        <td class="text-center">
                                            <?php 
                                                $kriteriaId = $idx + 1;
                                                echo isset($nilai[$kriteriaId]) ? number_format($nilai[$kriteriaId], 4) : '0.0000';
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 3: PERHITUNGAN DENGAN BOBOT -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">STEP 3: Perhitungan dengan Bobot (R × W)</h5>
                <small><?= $result['steps'][2]['description'] ?></small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th colspan="4" class="text-center bg-light"><strong>Perhitungan Per Mahasiswa × Per Kriteria</strong></th>
                            </tr>
                            <tr>
                                <th>Kriteria</th>
                                <th class="text-center">R(i,j)</th>
                                <th class="text-center">W(j)</th>
                                <th class="text-center">R × W</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['steps'][2]['data'] as $mId => $kalkulasi): ?>
                                <tr>
                                    <td colspan="4" class="bg-light"><strong><?= $result['steps'][2]['mahasiswa_names'][$mId] ?? 'Unknown' ?></strong></td>
                                </tr>
                                <?php foreach ($kalkulasi as $kId => $detail): ?>
                                    <tr>
                                        <td style="padding-left: 30px;"><?= $detail['kriteria'] ?> (<?= $detail['kode'] ?>)</td>
                                        <td class="text-center"><?= number_format($detail['rij'], 4) ?></td>
                                        <td class="text-center"><?= number_format($detail['bobot'], 4) ?></td>
                                        <td class="text-center"><strong><?= number_format($detail['kontribusi'], 6) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 4: NILAI PREFERENSI -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">STEP 4: Nilai Preferensi (P)</h5>
                <small><?= $result['steps'][3]['description'] ?></small>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <strong>Formula:</strong> P(i) = Σ(W(j) × R(i,j))
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th colspan="5" class="text-center bg-light"><strong>Penjumlahan Kontribusi Bobot</strong></th>
                            </tr>
                            <tr>
                                <th>Mahasiswa</th>
                                <th class="text-center" colspan="3">Perhitungan</th>
                                <th class="text-center">Nilai Preferensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['steps'][3]['data'] as $mId => $pref): ?>
                                <tr>
                                    <td><strong><?= $result['steps'][3]['mahasiswa_names'][$mId] ?? 'Unknown' ?></strong></td>
                                    <td class="text-center" colspan="3">
                                        <?php 
                                            $calc = [];
                                            foreach ($pref['breakdown'] as $detail) {
                                                $calc[] = number_format($detail['kontribusi'], 3);
                                            }
                                            echo implode(' + ', $calc);
                                        ?>
                                    </td>
                                    <td class="text-center"><strong style="font-size: 1.1em; color: #007bff;"><?= number_format($pref['skor'], 6) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 5: RANKING FINAL -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">STEP 5: Ranking Hasil Akhir</h5>
                <small><?= $result['steps'][4]['description'] ?></small>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <strong>Threshold (Nilai Minimum Lolos):</strong> <?= number_format($threshold, 2) ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">Ranking</th>
                                <th>Nama Mahasiswa</th>
                                <th class="text-center">Skor</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['steps'][4]['data'] as $row): ?>
                                <tr>
                                    <td class="text-center"><strong><?= $row['ranking'] ?></strong></td>
                                    <td><?= $row['nama'] ?></td>
                                    <td class="text-center"><strong><?= number_format($row['skor'], 6) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge <?= $row['status_lolos'] === 'Lolos' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['status_lolos'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RINGKASAN -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Ringkasan Hasil</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>Total Mahasiswa</h5>
                                <h3 class="text-primary"><?= $result['total_mahasiswa'] ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>Yang Lolos</h5>
                                <h3 class="text-success">
                                    <?php 
                                        $lolos = array_filter($result['steps'][4]['data'], fn($r) => $r['status_lolos'] === 'Lolos');
                                        echo count($lolos);
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>Tidak Lolos</h5>
                                <h3 class="text-danger">
                                    <?php 
                                        $tidakLolos = array_filter($result['steps'][4]['data'], fn($r) => $r['status_lolos'] === 'Tidak Lolos');
                                        echo count($tidakLolos);
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>Periode Penilaian</h5>
                                <h3 class="text-info"><?= $penilaian_ke ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Action Buttons -->
                            <a href="<?= base_url('penilaian/form-hitung-saw'); ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="fas fa-redo-alt"></i> Mulai Perhitungan Baru
                            </a>
                            
                            <button class="btn btn-danger btn-lg" 
                                    onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export ke PDF
                            </button>
                            
                            <a href="<?= base_url('penilaian'); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times-circle"></i> Batal
                            </a>

                            <a href="<?= base_url('hasil?penilaian_ke=' . $penilaian_ke); ?>" 
                               class="btn btn-info btn-lg ms-auto">
                                <i class="fas fa-chart-bar"></i> Lihat Detail Hasil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function exportToPDF() {
            // Simple implementation - in production use DomPDF or similar library
            const element = document.querySelector('.container-fluid');
            const printContent = element.innerHTML;
            const printWindow = window.open('', '', 'width=800,height=600');
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Hasil Perhitungan SAW - Periode <?= $penilaian_ke ?></title>
                    <meta charset="utf-8">
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h2 { color: #333; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
                        .card { page-break-inside: avoid; margin-bottom: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f8f9fa; }
                        .badge { padding: 3px 8px; border-radius: 3px; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <h2>Hasil Perhitungan SAW - Periode <?= $penilaian_ke ?></h2>
                    <p><strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
                    <p><strong>Threshold:</strong> <?= $threshold ?></p>
                    ${printContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
        </script>

    <div class="mb-4">

    <?php else: ?>
        <div class="alert alert-danger">
            <strong>✗ Perhitungan Gagal!</strong><br>
            <?= $result['message'] ?>
        </div>
        <a href="/penilaian" class="btn btn-secondary">← Kembali</a>
    <?php endif; ?>

</div>

<?php $this->endSection(); ?>
