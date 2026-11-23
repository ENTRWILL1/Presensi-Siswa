<?php
declare(strict_types=1);

// Session harus dimulai sebelum halaman mengirimkan HTML.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Muat seluruh model aplikasi.
require_once __DIR__ . '/models/BaseModel.php';
require_once __DIR__ . '/models/Kelas.php';
require_once __DIR__ . '/models/Siswa.php';
require_once __DIR__ . '/models/Presensi.php';
require_once __DIR__ . '/models/User.php';

// Satu koneksi PDO digunakan bersama oleh seluruh model.
$database = new Database();
$pdo = $database->getKoneksi();

// Objek model tersedia untuk halaman yang membutuhkannya.
$modelKelas = new Kelas($pdo);
$modelSiswa = new Siswa($pdo);
$modelPresensi = new Presensi($pdo);
$modelUser = new User($pdo);
