<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Beasiswa</title>
    <link rel="stylesheet" href="https://unpkg.com/primeicons/primeicons.css">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body class="login-body">
<div class="login-bg-shell" aria-hidden="true">
    <aside class="bg-sidebar">
        <div class="bg-brand"><i class="pi pi-star-fill"></i> SPK Beasiswa</div>
        <div class="bg-menu">
            <span><i class="pi pi-th-large"></i> Dashboard</span>
            <span><i class="pi pi-users"></i> Mahasiswa</span>
            <span><i class="pi pi-sliders-h"></i> Kriteria</span>
            <span><i class="pi pi-check-square"></i> Penilaian</span>
            <span><i class="pi pi-trophy"></i> Hasil</span>
        </div>
    </aside>
    <main class="bg-main">
        <header class="bg-topbar">
            <span>Dashboard</span>
            <span><i class="pi pi-user"></i> Admin</span>
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
    <div class="login-modal-grid">
        <aside class="login-side">
            <div class="login-logo"><i class="pi pi-star-fill"></i> SPK Beasiswa</div>
            <h1 id="loginTitle">Masuk ke Sistem</h1>
            <p class="login-subtitle">Seleksi beasiswa berbasis metode SAW dengan proses yang terstruktur, akurat, dan transparan.</p>
            <ul class="login-feature-list">
                <li><i class="pi pi-check-circle"></i> Kelola data mahasiswa dan kriteria</li>
                <li><i class="pi pi-check-circle"></i> Input penilaian terstandar</li>
                <li><i class="pi pi-check-circle"></i> Normalisasi dan ranking otomatis</li>
            </ul>
        </aside>

        <div class="login-form-wrap">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form id="loginForm" action="/login" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="next" value="<?= esc((string) ($next ?? '/dashboard')) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="pi pi-user"></i>
                        <input id="username" type="text" name="username" value="<?= esc(old('username')) ?>" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="pi pi-lock"></i>
                        <input id="password" type="password" name="password" placeholder="Masukkan password" required>
                        <button class="password-toggle" type="button" id="togglePassword" aria-label="Tampilkan password">
                            <i class="pi pi-eye"></i>
                        </button>
                    </div>
                    <div id="capsWarning" class="caps-warning" aria-live="polite"></div>
                </div>

                <button id="loginSubmit" type="submit"><i class="pi pi-sign-in"></i> Login</button>
            </form>

            <p class="meta">Default akun: admin / admin123</p>
        </div>
    </div>

    <script>
    (() => {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const submitBtn = document.getElementById('loginSubmit');
        const form = document.getElementById('loginForm');
        const capsWarning = document.getElementById('capsWarning');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const show = passwordInput.type === 'password';
                passwordInput.type = show ? 'text' : 'password';
                toggleBtn.innerHTML = show ? '<i class="pi pi-eye-slash"></i>' : '<i class="pi pi-eye"></i>';
            });
        }

        if (passwordInput && capsWarning) {
            passwordInput.addEventListener('keyup', (event) => {
                capsWarning.textContent = event.getModifierState('CapsLock') ? 'Caps Lock aktif' : '';
            });
        }

        if (form && submitBtn) {
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="pi pi-spin pi-spinner"></i> Memproses...';
            });
        }
    })();
    </script>
</section>
</body>
</html>
