<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Beasiswa - INSOL</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="app-body">
<?php $path = trim((string) service('request')->getUri()->getPath(), '/'); ?>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">SPK Beasiswa</div>
        <div class="sidebar-menu">
            <a class="sidebar-link <?= ($path === '' || str_starts_with($path, 'dashboard')) ? 'active' : '' ?>" href="/dashboard">Dashboard</a>
            <a class="sidebar-link <?= str_starts_with($path, 'mahasiswa') ? 'active' : '' ?>" href="/mahasiswa">Mahasiswa</a>
            <a class="sidebar-link <?= str_starts_with($path, 'kriteria') ? 'active' : '' ?>" href="/kriteria">Kriteria</a>
            <a class="sidebar-link <?= str_starts_with($path, 'penilaian') ? 'active' : '' ?>" href="/penilaian">Penilaian</a>
            <a class="sidebar-link <?= str_starts_with($path, 'hasil') ? 'active' : '' ?>" href="/hasil">Hasil</a>
            <a class="sidebar-link" href="/logout">Logout</a>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-search-wrap">
                    <input class="topbar-search" type="text" placeholder="Search..." aria-label="Search">
                    <button class="topbar-search-btn" type="button">⌕</button>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-chip">EN</span>
                <span class="topbar-chip">☼</span>
                <span class="topbar-chip">⚙</span>
                <span class="topbar-chip">🔔</span>
                <div class="topbar-user">👤 <?= esc((string) session()->get('nama')) ?></div>
            </div>
        </header>

        <div class="container">
            <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>
</body>
</html>
