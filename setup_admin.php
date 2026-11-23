<?php
declare(strict_types=1);

/**
 * Installer satu kali untuk membuat akun administrator pertama.
 *
 * Setelah akun berhasil dibuat, hapus file setup_admin.php
 * dari server demi keamanan.
 */

require_once __DIR__ . '/app/bootstrap.php';

// Jika akun sudah ada, installer tidak boleh digunakan lagi.
$jumlahPengguna = $modelUser->countAll();

if ($jumlahPengguna > 0) {
    http_response_code(403);
    exit('Akun administrator sudah tersedia. Hapus file setup_admin.php demi keamanan.');
}

$kesalahan = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = post_string('username');
    $password = $_POST['password'] ?? '';
    $password = is_string($password) ? $password : '';

    // Validasi username di sisi server.
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $kesalahan['username'] = 'Username harus 3–50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    }

    // Validasi password di sisi server.
    if (strlen($password) < 8) {
        $kesalahan['password'] = 'Password minimal 8 karakter.';
    }

    // Periksa apakah username sudah digunakan.
    if ($username !== '' && $modelUser->usernameExists($username)) {
        $kesalahan['username'] = 'Username sudah digunakan.';
    }

    // Simpan akun hanya jika semua validasi berhasil.
    if ($kesalahan === []) {
        $modelUser->setUsername($username);
        $modelUser->setPassword($password);
        $modelUser->create();

        flash('success', 'Administrator berhasil dibuat. Silakan login.');
        redirect('login.php');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Administrator | Sistem Presensi Siswa</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <div class="brand-mark auth-brand">PS</div>
        <p class="eyebrow">INSTALASI AWAL</p>
        <h1>Buat Administrator</h1>
        <p class="muted">Halaman ini hanya digunakan untuk membuat akun pertama.</p>

        <form method="post" class="auth-form" novalidate>
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

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
                <?php if (isset($kesalahan['username'])): ?>
                    <small class="field-error">
                        <?= e($kesalahan['username']) ?>
                    </small>
                <?php endif; ?>
            </label>

            <label>
                Password
                <input
                    type="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
                <?php if (isset($kesalahan['password'])): ?>
                    <small class="field-error">
                        <?= e($kesalahan['password']) ?>
                    </small>
                <?php endif; ?>
            </label>

            <button type="submit" class="button primary full">
                Buat Administrator
            </button>
        </form>
    </main>
</body>
</html>
