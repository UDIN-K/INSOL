<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Beasiswa</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
<nav class="topbar">
    <a href="#">HOME</a>
    <a href="#">ABOUT</a>
    <a href="#">SERVICE</a>
    <a href="#">CONTACT</a>
    <a href="/login" class="active">LOGIN</a>
</nav>

<section class="wrap">
    <h1>LOGIN</h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="/login" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="<?= esc(old('username')) ?>" placeholder="Masukkan username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Masukkan password" required>
        </div>

        <div class="extras">
            <label class="remember"><input type="checkbox" disabled> Remember Me</label>
            <a class="forgot" href="#">Forgot Password?</a>
        </div>

        <button type="submit">Login</button>
    </form>

    <p class="meta">Default akun: admin / admin123</p>
</section>
</body>
</html>
