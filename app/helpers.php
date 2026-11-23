<?php
declare(strict_types=1);

/** Amankan keluaran sebelum ditampilkan ke HTML. */
function e(mixed $nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Arahkan pengguna ke halaman di dalam folder aplikasi. */
function redirect(string $alamat): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($alamat, '/'));
    exit;
}

/** Pastikan administrator sudah login. */
function require_login(): void
{
    if (empty($_SESSION['id_user'])) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

/** Ambil identitas administrator yang sedang login. */
function current_admin(): ?array
{
    if (empty($_SESSION['id_user'])) {
        return null;
    }

    return [
        'id_user' => (int) $_SESSION['id_user'],
        'username' => (string) ($_SESSION['username'] ?? ''),
    ];
}

/** Buat token CSRF untuk form yang mengubah data. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Periksa token CSRF dari form POST. */
function verify_csrf(): void
{
    $tokenForm = $_POST['csrf_token'] ?? '';
    $tokenSession = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($tokenForm)
        || !is_string($tokenSession)
        || $tokenSession === ''
        || !hash_equals($tokenSession, $tokenForm)
    ) {
        http_response_code(419);
        exit('Permintaan tidak valid. Muat ulang halaman, lalu coba kembali.');
    }
}

/** Simpan pesan sementara untuk halaman berikutnya. */
function flash(string $jenis, string $pesan): void
{
    $_SESSION['flash'] = [
        'jenis' => $jenis,
        'pesan' => $pesan,
    ];
}

/** Ambil lalu hapus pesan sementara. */
function get_flash(): ?array
{
    $pesan = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($pesan) ? $pesan : null;
}

/** Periksa format tanggal YYYY-MM-DD. */
function is_valid_date(string $tanggal): bool
{
    $hasil = DateTime::createFromFormat('!Y-m-d', $tanggal);

    return $hasil !== false && $hasil->format('Y-m-d') === $tanggal;
}

/** Hitung karakter tanpa mewajibkan ekstensi mbstring. */
function text_length(string $teks): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($teks, 'UTF-8');
    }

    $jumlah = preg_match_all('/./us', $teks);

    return $jumlah === false ? strlen($teks) : $jumlah;
}

/** Ambil string POST dan tolak nilai berbentuk array. */
function post_string(string $nama): string
{
    $nilai = $_POST[$nama] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}

/** Ambil string GET dan tolak nilai berbentuk array. */
function get_string(string $nama): string
{
    $nilai = $_GET[$nama] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}
