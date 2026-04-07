<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Beasiswa - INSOL</title>
    <link rel="stylesheet" href="https://unpkg.com/primeicons/primeicons.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="app-body">
<?php $path = trim((string) service('request')->getUri()->getPath(), '/'); ?>
<?php $isLoggedIn = session()->get('user_id') !== null; ?>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><i class="pi pi-star-fill"></i> SPK Beasiswa</div>
        <div class="sidebar-menu">
            <a class="sidebar-link <?= ($path === '' || str_starts_with($path, 'dashboard')) ? 'active' : '' ?>" href="/dashboard"><i class="pi pi-th-large"></i> Dashboard</a>
            <a class="sidebar-link <?= str_starts_with($path, 'mahasiswa') ? 'active' : '' ?>" href="/mahasiswa"><i class="pi pi-users"></i> Mahasiswa</a>
            <a class="sidebar-link <?= str_starts_with($path, 'kriteria') ? 'active' : '' ?>" href="/kriteria"><i class="pi pi-sliders-h"></i> Kriteria</a>
            <a class="sidebar-link <?= str_starts_with($path, 'penilaian') ? 'active' : '' ?>" href="/penilaian"><i class="pi pi-check-square"></i> Penilaian</a>
            <a class="sidebar-link <?= str_starts_with($path, 'hasil') ? 'active' : '' ?>" href="/hasil"><i class="pi pi-trophy"></i> Hasil</a>
            <?php if ($isLoggedIn): ?>
                <a class="sidebar-link" href="/logout"><i class="pi pi-sign-out"></i> Logout</a>
            <?php else: ?>
                <a class="sidebar-link" href="/login"><i class="pi pi-sign-in"></i> Login</a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-title"><i class="pi pi-chart-line"></i> Sistem Pendukung Keputusan Beasiswa</div>
            </div>
            <div class="topbar-right">
                <span class="topbar-chip"><i class="pi pi-check-circle"></i> Sistem Aktif</span>
                <?php if ($isLoggedIn): ?>
                    <div class="topbar-user"><i class="pi pi-user"></i> <?= esc((string) session()->get('nama')) ?></div>
                <?php else: ?>
                    <a href="/login" class="topbar-user topbar-login-btn"><i class="pi pi-sign-in"></i> Login</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="container">
            <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
            <?php $validationErrors = session()->getFlashdata('validation_errors'); ?>
            <?php if (! empty($validationErrors)): ?>
                <div class="alert alert-error">
                    <strong><i class="pi pi-exclamation-triangle"></i> Periksa input berikut:</strong>
                    <ul style="margin:8px 0 0 18px; padding:0;">
                        <?php foreach ((array) $validationErrors as $message): ?>
                            <li><?= esc((string) $message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>
</body>
</html>
