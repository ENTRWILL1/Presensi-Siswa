<?php
$admin = current_admin();
$pesanFlash = get_flash();
$pathSekarang = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

$halamanDashboard = str_ends_with($pathSekarang, '/dashboard.php');
$halamanSiswa = str_contains($pathSekarang, '/siswa/');
$halamanKelas = str_contains($pathSekarang, '/kelas/');
$halamanPresensi = str_contains($pathSekarang, '/presensi/');
$halamanRekap = $pathSekarang === BASE_URL . '/presensi/index.php';
$halamanTap = $pathSekarang === BASE_URL . '/presensi/tap.php';
?>
<aside class="sidebar">
    <a class="brand" href="<?= e(BASE_URL) ?>/presensi/index.php">
        <span class="brand-mark">PS</span>
        <span>
            Presensi Siswa
            <small>Administrasi sekolah</small>
        </span>
    </a>

    <p class="nav-label">MENU UTAMA</p>
    <a class="nav-link <?= $halamanDashboard ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/dashboard.php">
        Dashboard
    </a>
    <a class="nav-link <?= $halamanRekap ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/presensi/index.php">
        Rekap Presensi
    </a>
    <a class="nav-link <?= $halamanTap ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/presensi/tap.php">
        Simulasi Tap Kartu
    </a>
    <a class="nav-link <?= $halamanSiswa ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/siswa/index.php">
        Data Siswa
    </a>
    <a class="nav-link <?= $halamanKelas ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/kelas/index.php">
        Data Kelas
    </a>

    <div class="sidebar-bottom">
        <div class="user-chip">
            <span class="avatar"><?= e(strtoupper(substr($admin['username'] ?? 'A', 0, 1))) ?></span>
            <span>
                <?= e($admin['username'] ?? 'Administrator') ?>
                <small>Administrator</small>
            </span>
        </div>

        <form method="post" action="<?= e(BASE_URL) ?>/logout.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="nav-link logout-button">Logout</button>
        </form>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <span>UUK PPLG · PHP Native + PDO + MariaDB</span>
        <span><?= e(date('d-m-Y')) ?></span>
    </header>

    <div class="content-area">
        <?php if ($pesanFlash !== null): ?>
            <div class="alert <?= e($pesanFlash['jenis']) ?>" role="status">
                <?= e($pesanFlash['pesan']) ?>
            </div>
        <?php endif; ?>
