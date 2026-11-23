<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();
$nisn = post_string('nisn');

if (!preg_match('/^[0-9]{10}$/', $nisn)) {
    flash('error', 'NISN siswa tidak valid.');
    redirect('siswa/index.php');
}

// Siswa yang memiliki riwayat presensi tidak dapat dihapus sebelum log dihapus.
$cekPresensi = $pdo->prepare(
    'SELECT COUNT(*) FROM presensi WHERE nisn = :nisn'
);
$cekPresensi->execute(['nisn' => $nisn]);

if ((int) $cekPresensi->fetchColumn() > 0) {
    flash('error', 'Siswa tidak dapat dihapus karena masih memiliki riwayat presensi. Hapus log presensinya terlebih dahulu.');
    redirect('siswa/index.php');
}

$hapusSiswa = $pdo->prepare(
    'DELETE FROM siswa WHERE nisn = :nisn'
);
$hapusSiswa->execute(['nisn' => $nisn]);

if ($hapusSiswa->rowCount() > 0) {
    flash('success', 'Data siswa berhasil dihapus.');
} else {
    flash('error', 'Data siswa tidak ditemukan.');
}

redirect('siswa/index.php');
