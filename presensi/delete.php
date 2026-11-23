<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();
$idPresensi = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if (!$idPresensi || $idPresensi < 1) {
    flash('error', 'ID catatan presensi tidak valid.');
    redirect('presensi/index.php');
}

$hapusPresensi = $pdo->prepare(
    'DELETE FROM presensi WHERE id = :id'
);
$hapusPresensi->execute(['id' => $idPresensi]);

if ($hapusPresensi->rowCount() > 0) {
    flash('success', 'Catatan presensi berhasil dihapus.');
} else {
    flash('error', 'Catatan presensi tidak ditemukan.');
}

redirect('presensi/index.php');
