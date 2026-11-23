<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

if (!empty($_SESSION['id_user'])) {
    redirect('presensi/index.php');
}

$kesalahan = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = post_string('username');
    $password = $_POST['password'] ?? '';
    $password = is_string($password) ? $password : '';

    if ($username === '' || $password === '') {
        $kesalahan['login'] = 'Username atau password tidak sesuai.';
    } else {
        $admin = $modelUser->authenticate($username, $password);

        if ($admin !== null) {
            session_regenerate_id(true);

            $_SESSION['id_user'] = $admin['id_user'];
            $_SESSION['username'] = $admin['username'];

            redirect('presensi/index.php');
        }

        // Pesan umum tidak membocorkan apakah username atau password yang salah.
        $kesalahan['login'] = 'Username atau password tidak sesuai.';
    }
}

$pesanFlash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sistem Presensi Siswa</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <div class="brand-mark auth-brand">PS</div>
        <p class="eyebrow">SISTEM PRESENSI SISWA</p>
        <h1>Login Administrator</h1>
        <p class="muted">Masukkan username dan password untuk melanjutkan.</p>

        <?php if ($pesanFlash !== null): ?>
            <div class="alert <?= e($pesanFlash['jenis']) ?>" role="status">
                <?= e($pesanFlash['pesan']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($kesalahan['login'])): ?>
            <div class="alert error" role="alert">
                <?= e($kesalahan['login']) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <label>
                Username
                <input
                    type="text"
                    name="username"
                    value="<?= e($username) ?>"
                    maxlength="50"
                    autocomplete="username"
                    required
                >
            </label>

            <label>
                Password
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </label>

            <button type="submit" class="button primary full">Login</button>
        </form>
    </main>
</body>
</html>
