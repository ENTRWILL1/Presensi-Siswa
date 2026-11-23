<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();

// Kosongkan data session yang sedang aktif.
$_SESSION = [];

// Hapus cookie session dari browser jika session menggunakan cookie.
if (ini_get('session.use_cookies')) {
    $parameterCookie = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parameterCookie['path'],
        $parameterCookie['domain'],
        $parameterCookie['secure'],
        $parameterCookie['httponly']
    );
}

session_destroy();

header('Location: ' . BASE_URL . '/login.php');
exit;
