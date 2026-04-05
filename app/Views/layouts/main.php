<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Beasiswa - INSOL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="app-body">
<?php $path = trim((string) service('request')->getUri()->getPath(), '/'); ?>
<?php $isLoggedIn = session()->get('user_id') !== null; ?>
<?php $popupNext = (string) service('request')->getGet('next'); ?>
<?php $openPopupByQuery = ((string) service('request')->getGet('login')) === '1'; ?>
<?php if ($popupNext === '') { $popupNext = '/dashboard'; } ?>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand"><i class="bi bi-gem"></i> SPK Beasiswa</div>
        <div class="sidebar-menu">
            <a class="sidebar-link <?= ($path === '' || str_starts_with($path, 'dashboard')) ? 'active' : '' ?>" href="/dashboard"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a class="sidebar-link <?= str_starts_with($path, 'mahasiswa') ? 'active' : '' ?> <?= ! $isLoggedIn ? 'requires-login' : '' ?>" href="/mahasiswa"><i class="bi bi-people-fill"></i> Mahasiswa</a>
            <a class="sidebar-link <?= str_starts_with($path, 'kriteria') ? 'active' : '' ?> <?= ! $isLoggedIn ? 'requires-login' : '' ?>" href="/kriteria"><i class="bi bi-sliders2-vertical"></i> Kriteria</a>
            <a class="sidebar-link <?= str_starts_with($path, 'penilaian') ? 'active' : '' ?> <?= ! $isLoggedIn ? 'requires-login' : '' ?>" href="/penilaian"><i class="bi bi-ui-checks-grid"></i> Penilaian</a>
            <a class="sidebar-link <?= str_starts_with($path, 'hasil') ? 'active' : '' ?> <?= ! $isLoggedIn ? 'requires-login' : '' ?>" href="/hasil"><i class="bi bi-trophy-fill"></i> Hasil</a>
            <?php if ($isLoggedIn): ?>
                <a class="sidebar-link" href="/logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a class="sidebar-link login-trigger" href="#"><i class="bi bi-box-arrow-in-right"></i> Login</a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-title"><i class="bi bi-bar-chart-line-fill"></i> Dashboard</div>
                <div class="topbar-search-wrap">
                    <input class="topbar-search" type="text" placeholder="Search..." aria-label="Search">
                    <button class="topbar-search-btn" type="button"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-chip"><i class="bi bi-check-circle"></i> Sistem Aktif</span>
                <span class="topbar-chip"><i class="bi bi-bell"></i></span>
                <?php if ($isLoggedIn): ?>
                    <div class="topbar-user"><i class="bi bi-person-circle"></i> <?= esc((string) session()->get('nama')) ?></div>
                <?php else: ?>
                    <button type="button" class="topbar-user topbar-login-btn login-trigger"><i class="bi bi-box-arrow-in-right"></i> Login</button>
                <?php endif; ?>
            </div>
        </header>

        <div class="container">
            <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?><div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>

<?php if (! $isLoggedIn): ?>
<div id="loginPopup" class="login-popup <?= $openPopupByQuery || session()->getFlashdata('error') ? 'open' : '' ?>">
    <div class="login-popup-header">
        <strong><i class="bi bi-shield-lock"></i> Login Diperlukan</strong>
        <button type="button" id="closeLoginPopup" class="login-popup-close"><i class="bi bi-x-lg"></i></button>
    </div>
    <p>Silakan login untuk mengakses fitur ini.</p>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <form method="post" action="/login?popup=1">
        <?= csrf_field() ?>
        <input type="hidden" name="popup" value="1">
        <input type="hidden" name="next" id="loginNext" value="<?= esc($popupNext !== '' ? $popupNext : '/dashboard') ?>">
        <div class="form-group">
            <label for="popupUsername">Username</label>
            <input id="popupUsername" type="text" name="username" value="<?= esc(old('username')) ?>" required>
        </div>
        <div class="form-group">
            <label for="popupPassword">Password</label>
            <input id="popupPassword" type="password" name="password" required>
        </div>
        <button class="btn" type="submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>
</div>

<script>
(() => {
    const popup = document.getElementById('loginPopup');

    if (!popup) return;

    const nextInput = document.getElementById('loginNext');
    const closeBtn = document.getElementById('closeLoginPopup');
    const usernameInput = document.getElementById('popupUsername');

    const focusFirstInput = () => {
        if (!usernameInput) return;
        setTimeout(() => usernameInput.focus(), 60);
    };

    const openPopup = (nextUrl) => {
        if (nextUrl && nextInput) {
            nextInput.value = nextUrl;
        }
        popup.classList.add('open');
        focusFirstInput();
    };

    const closePopup = () => popup.classList.remove('open');

    document.querySelectorAll('.login-trigger').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            openPopup(window.location.pathname + window.location.search);
        });
    });

    document.querySelectorAll('a.requires-login').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            const href = el.getAttribute('href') || '/dashboard';
            openPopup(href);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closePopup);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popup.classList.contains('open')) {
            closePopup();
        }
    });

    document.addEventListener('click', (e) => {
        if (!popup.classList.contains('open')) return;
        const target = e.target;
        if (!(target instanceof Element)) return;
        if (target.closest('#loginPopup') || target.closest('.login-trigger') || target.closest('a.requires-login')) return;
        closePopup();
    });

    if (popup.classList.contains('open')) {
        focusFirstInput();
    }
})();
</script>
<?php endif; ?>
</body>
</html>
