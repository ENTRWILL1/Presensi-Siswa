<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_login();

// Hitung jumlah data untuk ringkasan dashboard.
$jumlahSiswa = (int) $pdo->query('SELECT COUNT(*) FROM siswa')->fetchColumn();
$jumlahKelas = (int) $pdo->query('SELECT COUNT(*) FROM kelas')->fetchColumn();
$jumlahPresensiHariIni = (int) $pdo->query(
    'SELECT COUNT(*)
     FROM presensi
     WHERE tanggal_waktu >= CURRENT_DATE()
       AND tanggal_waktu < CURRENT_DATE() + INTERVAL 1 DAY'
)->fetchColumn();

// Ambil delapan presensi terbaru menggunakan INNER JOIN tiga tabel.
$pernyataanTerbaru = $pdo->query(
    'SELECT
        presensi.id,
        siswa.nisn,
        siswa.nama,
        kelas.nama_kelas,
        presensi.tanggal_waktu
     FROM presensi
     INNER JOIN siswa
        ON siswa.nisn = presensi.nisn
     INNER JOIN kelas
        ON kelas.id_kelas = siswa.id_kelas
     ORDER BY presensi.tanggal_waktu DESC
     LIMIT 8'
);
$presensiTerbaru = $pernyataanTerbaru->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/layouts/header.php';
include __DIR__ . '/layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">RINGKASAN APLIKASI</p>
        <h1>Dashboard</h1>
        <p class="muted">Ringkasan data siswa, kelas, dan presensi.</p>
    </div>
    <a class="button primary" href="<?= e(BASE_URL) ?>/presensi/tap.php">Simulasi Tap</a>
</section>

<section class="stats-grid" aria-label="Ringkasan data">
    <article class="stat-card">
        <span>Total Siswa</span>
        <strong><?= e($jumlahSiswa) ?></strong>
        <small>Siswa terdaftar</small>
    </article>
    <article class="stat-card">
        <span>Total Kelas</span>
        <strong><?= e($jumlahKelas) ?></strong>
        <small>Kelas tersedia</small>
    </article>
    <article class="stat-card">
        <span>Presensi Hari Ini</span>
        <strong><?= e($jumlahPresensiHariIni) ?></strong>
        <small>Catatan presensi hari ini</small>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Presensi Terbaru</h2>
            <p class="muted">Delapan catatan presensi terakhir.</p>
        </div>
        <a class="text-link" href="<?= e(BASE_URL) ?>/presensi/index.php">Lihat rekap</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal dan Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($presensiTerbaru === []): ?>
                    <tr>
                        <td colspan="4" class="empty">Belum ada catatan presensi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($presensiTerbaru as $catatan): ?>
                        <tr>
                            <td><?= e($catatan['nisn']) ?></td>
                            <td><?= e($catatan['nama']) ?></td>
                            <td><?= e($catatan['nama_kelas']) ?></td>
                            <td><?= e(date('d-m-Y H:i:s', strtotime($catatan['tanggal_waktu']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/layouts/footer.php'; ?>
