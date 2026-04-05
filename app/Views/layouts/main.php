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
<?php endif; ?>

<script>
(() => {
    const loginPopup = document.getElementById('loginPopup');
    const nextInput = document.getElementById('loginNext');
    const closeLoginBtn = document.getElementById('closeLoginPopup');
    const usernameInput = document.getElementById('popupUsername');

    const pagePopup = document.getElementById('pagePopup');
    const pagePopupFrame = document.getElementById('pagePopupFrame');
    const pagePopupTitle = document.getElementById('pagePopupTitle');
    const closePagePopupBtn = document.getElementById('closePagePopup');
    const openNewTabBtn = document.getElementById('openPageInTab');

    const focusLoginInput = () => {
        if (!usernameInput) return;
        setTimeout(() => usernameInput.focus(), 60);
    };

    const openLoginPopup = (nextUrl) => {
        if (!loginPopup) return;
        if (nextUrl && nextInput) {
            nextInput.value = nextUrl;
        }
        loginPopup.classList.add('open');
        focusLoginInput();
    };

    const closeLoginPopup = () => {
        if (!loginPopup) return;
        loginPopup.classList.remove('open');
    };

    const openPagePopup = (href, titleText) => {
        if (!pagePopup || !pagePopupFrame) return;
        pagePopup.classList.add('open');
        document.body.classList.add('popup-open');
        pagePopupFrame.src = href;
        pagePopupFrame.dataset.currentHref = href;
        if (pagePopupTitle) {
            pagePopupTitle.textContent = titleText && titleText.trim() !== '' ? titleText.trim() : 'Halaman';
        }
    };

    const closePagePopup = () => {
        if (!pagePopup || !pagePopupFrame) return;
        pagePopup.classList.remove('open');
        document.body.classList.remove('popup-open');
        setTimeout(() => {
            if (!pagePopup.classList.contains('open')) {
                pagePopupFrame.src = 'about:blank';
                pagePopupFrame.dataset.currentHref = '';
            }
        }, 140);
    };

    const shouldOpenInPagePopup = (anchor, href) => {
        if (!pagePopup || !href) return false;
        if (anchor.classList.contains('login-trigger') || anchor.classList.contains('requires-login')) return false;
        if (anchor.hasAttribute('data-no-popup')) return false;
        if (anchor.getAttribute('target') && anchor.getAttribute('target') !== '_self') return false;
        if (href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;

        try {
            const targetUrl = new URL(href, window.location.origin);
            if (targetUrl.origin !== window.location.origin) return false;
            const path = targetUrl.pathname || '';
            if (path === '/logout' || path === '/login') return false;
            return true;
        } catch (error) {
            return false;
        }
    };

    document.querySelectorAll('.login-trigger').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            openLoginPopup(window.location.pathname + window.location.search);
        });
    });

    document.querySelectorAll('a.requires-login').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            const href = el.getAttribute('href') || '/dashboard';
            openLoginPopup(href);
        });
    });

    if (closeLoginBtn) {
        closeLoginBtn.addEventListener('click', closeLoginPopup);
    }

    document.addEventListener('click', (e) => {
        const target = e.target;
        if (!(target instanceof Element)) return;

        const anchor = target.closest('a[href]');
        if (!anchor) {
            if (loginPopup && loginPopup.classList.contains('open') && !target.closest('#loginPopup')) {
                closeLoginPopup();
            }
            if (pagePopup && pagePopup.classList.contains('open') && target.id === 'pagePopup') {
                closePagePopup();
            }
            return;
        }

        if (e.defaultPrevented) return;

        const href = anchor.getAttribute('href') || '';
        if (shouldOpenInPagePopup(anchor, href)) {
            e.preventDefault();
            const targetUrl = new URL(href, window.location.origin);
            openPagePopup(targetUrl.pathname + targetUrl.search + targetUrl.hash, anchor.textContent || 'Halaman');
        }
    });

    if (closePagePopupBtn) {
        closePagePopupBtn.addEventListener('click', closePagePopup);
    }

    if (openNewTabBtn && pagePopupFrame) {
        openNewTabBtn.addEventListener('click', () => {
            const href = pagePopupFrame.dataset.currentHref || pagePopupFrame.src;
            if (!href || href === 'about:blank') return;
            window.open(href, '_blank', 'noopener');
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (loginPopup && loginPopup.classList.contains('open')) {
            closeLoginPopup();
            return;
        }
        if (pagePopup && pagePopup.classList.contains('open')) {
            closePagePopup();
        }
    });

    if (loginPopup && loginPopup.classList.contains('open')) {
        focusLoginInput();
    }
})();
</script>

<div id="pagePopup" class="page-popup" aria-hidden="true">
    <div class="page-popup-dialog" role="dialog" aria-modal="true" aria-label="Popup Halaman">
        <div class="page-popup-header">
            <strong id="pagePopupTitle"><i class="bi bi-window"></i> Halaman</strong>
            <div class="page-popup-actions">
                <button type="button" id="openPageInTab" class="page-popup-btn" title="Buka di tab baru"><i class="bi bi-box-arrow-up-right"></i></button>
                <button type="button" id="closePagePopup" class="page-popup-btn" title="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <iframe id="pagePopupFrame" class="page-popup-frame" src="about:blank" loading="lazy"></iframe>
    </div>
</div>
</body>
</html>
