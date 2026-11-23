<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$nisn = '';
$kesalahan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nisn = post_string('nisn');

    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $kesalahan = 'Masukkan NISN yang terdiri dari tepat 10 digit angka.';
    } else {
        // Cari siswa berdasarkan NISN yang dikirim dari simulasi tap.
        $cariSiswa = $pdo->prepare(
            'SELECT nisn, nama
             FROM siswa
             WHERE nisn = :nisn
             LIMIT 1'
        );
        $cariSiswa->execute(['nisn' => $nisn]);
        $dataSiswa = $cariSiswa->fetch();

        if (!$dataSiswa) {
            $kesalahan = 'NISN tidak terdaftar. Periksa kembali data siswa.';
        } else {
            // Hanya NISN yang dikirim. id dan tanggal_waktu dibuat MariaDB.
            $tambahPresensi = $pdo->prepare(
                'INSERT INTO presensi (nisn) VALUES (:nisn)'
            );
            $tambahPresensi->execute(['nisn' => $nisn]);

            flash('success', 'Presensi berhasil dicatat untuk ' . $dataSiswa['nama'] . '.');
            redirect('presensi/tap.php');
        }
    }
}

$pageTitle = 'Simulasi Tap Kartu';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">PRESENSI</p>
        <h1>Simulasi Tap Kartu</h1>
        <p class="muted">Masukkan NISN untuk mensimulasikan pembacaan kartu RFID.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="post" class="form-grid" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label class="full-field">
            NISN <span class="required">*</span>
            <input
                type="text"
                name="nisn"
                inputmode="numeric"
                maxlength="10"
                pattern="[0-9]{10}"
                value="<?= e($nisn) ?>"
                placeholder="Masukkan 10 digit NISN"
                required
                autofocus
            >
            <?php if ($kesalahan !== ''): ?>
                <small class="field-error"><?= e($kesalahan) ?></small>
            <?php endif; ?>
        </label>

        <div class="form-actions">
            <button type="submit" class="button primary">Proses Tap</button>
            <a class="button light" href="<?= e(BASE_URL) ?>/presensi/index.php">Lihat Rekap</a>
        </div>
    </form>

    <p class="muted small">
        Form hanya mengirim NISN. ID presensi dan tanggal/waktu dibuat otomatis oleh database.
    </p>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
