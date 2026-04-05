<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Beasiswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body class="login-body">
<div class="login-bg-shell" aria-hidden="true">
    <aside class="bg-sidebar">
        <div class="bg-brand"><i class="bi bi-gem"></i> SPK Beasiswa</div>
        <div class="bg-menu">
            <span><i class="bi bi-grid-1x2-fill"></i> Dashboard</span>
            <span><i class="bi bi-people-fill"></i> Mahasiswa</span>
            <span><i class="bi bi-sliders2-vertical"></i> Kriteria</span>
            <span><i class="bi bi-ui-checks-grid"></i> Penilaian</span>
            <span><i class="bi bi-trophy-fill"></i> Hasil</span>
        </div>
    </aside>
    <main class="bg-main">
        <header class="bg-topbar">
            <span>Dashboard</span>
            <span><i class="bi bi-person-circle"></i> Admin</span>
        </header>
        <section class="bg-content">
            <div class="bg-card"></div>
            <div class="bg-card"></div>
            <div class="bg-card"></div>
            <div class="bg-card"></div>
        </section>
    </main>
</div>

<div class="login-overlay"></div>

<section class="login-modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <h1 id="loginTitle">Masuk</h1>
    <p class="login-subtitle">Silakan login untuk mengakses dashboard SPK Beasiswa.</p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="/login" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input id="username" type="text" name="username" value="<?= esc(old('username')) ?>" placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input id="password" type="password" name="password" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>

    <p class="meta">Default akun: admin / admin123</p>
</section>
</body>
</html>
