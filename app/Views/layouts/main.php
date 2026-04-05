<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Beasiswa - INSOL</title>
    <style>
        body{font-family:Arial,sans-serif;margin:0;background:#f4f6f8}
        nav{background:#1f2937;color:#fff;padding:12px 18px;display:flex;justify-content:space-between}
        nav a{color:#fff;text-decoration:none;margin-right:12px}
        .container{max-width:1080px;margin:20px auto;background:#fff;border-radius:8px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,.08)}
        table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f3f4f6}
        .btn{display:inline-block;background:#2563eb;color:#fff;border:none;padding:8px 12px;border-radius:6px;text-decoration:none;cursor:pointer}
        .btn-danger{background:#dc2626}.btn-secondary{background:#4b5563}.btn-success{background:#16a34a}
        .alert{padding:10px 12px;border-radius:6px;margin-bottom:12px}.alert-success{background:#dcfce7;color:#166534}.alert-error{background:#fee2e2;color:#991b1b}
        .form-group{margin-bottom:12px}label{display:block;margin-bottom:5px;font-weight:bold}input,select{width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px}
    </style>
</head>
<body>
<nav>
    <div><strong>SPK Beasiswa (SAW)</strong></div>
    <div>
        <a href="/dashboard">Dashboard</a>
        <a href="/mahasiswa">Mahasiswa</a>
        <a href="/kriteria">Kriteria</a>
        <a href="/penilaian">Penilaian</a>
        <a href="/hasil">Hasil</a>
        <a href="/logout">Logout</a>
    </div>
</nav>
<div class="container">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?= $this->renderSection('content') ?>
</div>
</body>
</html>
