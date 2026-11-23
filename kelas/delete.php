<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();
$idKelas = filter_var($_POST['id_kelas'] ?? '', FILTER_VALIDATE_INT);

if (!$idKelas || $idKelas < 1) {
    flash('error', 'ID kelas tidak valid.');
    redirect('kelas/index.php');
}

// Kelas tidak boleh dihapus jika masih memiliki siswa.
$cekSiswa = $pdo->prepare(
    'SELECT COUNT(*) FROM siswa WHERE id_kelas = :id_kelas'
);
$cekSiswa->execute(['id_kelas' => $idKelas]);

if ((int) $cekSiswa->fetchColumn() > 0) {
    flash('error', 'Kelas tidak dapat dihapus karena masih memiliki siswa. Pindahkan siswa terlebih dahulu.');
    redirect('kelas/index.php');
}

try {
    $hapusKelas = $pdo->prepare(
        'DELETE FROM kelas WHERE id_kelas = :id_kelas'
    );
    $hapusKelas->execute(['id_kelas' => $idKelas]);

    if ($hapusKelas->rowCount() > 0) {
        flash('success', 'Data kelas berhasil dihapus.');
    } else {
        flash('error', 'Data kelas tidak ditemukan.');
    }
} catch (PDOException $exception) {
    // Tangani kondisi ketika data siswa terhubung saat proses penghapusan.
    flash('error', 'Kelas tidak dapat dihapus karena masih digunakan oleh data siswa.');
}

redirect('kelas/index.php');
