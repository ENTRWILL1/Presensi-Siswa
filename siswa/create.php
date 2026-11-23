<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$daftarKelas = $pdo->query(
    'SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas'
)->fetchAll();

$nilai = [
    'nisn' => '',
    'nama' => '',
    'id_kelas' => '',
    'alamat' => '',
];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nilai['nisn'] = post_string('nisn');
    $nilai['nama'] = post_string('nama');
    $nilai['id_kelas'] = post_string('id_kelas');
    $nilai['alamat'] = post_string('alamat');

    // NISN wajib tepat 10 digit dan disimpan sebagai teks.
    if (!preg_match('/^[0-9]{10}$/', $nilai['nisn'])) {
        $kesalahan['nisn'] = 'NISN wajib terdiri dari tepat 10 digit angka.';
    } else {
        $cekNisn = $pdo->prepare(
            'SELECT nisn FROM siswa WHERE nisn = :nisn LIMIT 1'
        );
        $cekNisn->execute(['nisn' => $nilai['nisn']]);

        if ($cekNisn->fetch()) {
            $kesalahan['nisn'] = 'NISN sudah terdaftar.';
        }
    }

    if ($nilai['nama'] === '') {
        $kesalahan['nama'] = 'Nama siswa wajib diisi.';
    } elseif (text_length($nilai['nama']) > 100) {
        $kesalahan['nama'] = 'Nama siswa maksimal 100 karakter.';
    }

    $idKelas = filter_var($nilai['id_kelas'], FILTER_VALIDATE_INT);

    if (!$idKelas || $idKelas < 1) {
        $kesalahan['id_kelas'] = 'Silakan pilih kelas.';
    } else {
        $cekKelas = $pdo->prepare(
            'SELECT id_kelas FROM kelas WHERE id_kelas = :id_kelas LIMIT 1'
        );
        $cekKelas->execute(['id_kelas' => $idKelas]);

        if (!$cekKelas->fetch()) {
            $kesalahan['id_kelas'] = 'Kelas yang dipilih tidak tersedia.';
        }
    }

    if (text_length($nilai['alamat']) > 255) {
        $kesalahan['alamat'] = 'Alamat maksimal 255 karakter.';
    }

    if ($kesalahan === []) {
        $tambahSiswa = $pdo->prepare(
            'INSERT INTO siswa (nisn, nama, alamat, id_kelas)
             VALUES (:nisn, :nama, :alamat, :id_kelas)'
        );
        $tambahSiswa->execute([
            'nisn' => $nilai['nisn'],
            'nama' => $nilai['nama'],
            'alamat' => $nilai['alamat'] !== '' ? $nilai['alamat'] : null,
            'id_kelas' => $idKelas,
        ]);

        flash('success', 'Data siswa berhasil ditambahkan.');
        redirect('siswa/index.php');
    }
}

$pageTitle = 'Tambah Siswa';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Tambah Siswa</h1>
        <p class="muted">Isi informasi siswa dengan benar.</p>
    </div>
    <a class="button light" href="<?= e(BASE_URL) ?>/siswa/index.php">Kembali</a>
</section>

<section class="panel form-panel">
    <form method="post" class="form-grid" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>
            NISN <span class="required">*</span>
            <input
                type="text"
                name="nisn"
                inputmode="numeric"
                maxlength="10"
                pattern="[0-9]{10}"
                value="<?= e($nilai['nisn']) ?>"
                placeholder="10 digit angka"
                required
            >
            <?php if (isset($kesalahan['nisn'])): ?>
                <small class="field-error"><?= e($kesalahan['nisn']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Nama Siswa <span class="required">*</span>
            <input
                type="text"
                name="nama"
                maxlength="100"
                value="<?= e($nilai['nama']) ?>"
                required
            >
            <?php if (isset($kesalahan['nama'])): ?>
                <small class="field-error"><?= e($kesalahan['nama']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Kelas <span class="required">*</span>
            <select name="id_kelas" required>
                <option value="">Pilih kelas</option>
                <?php foreach ($daftarKelas as $dataKelas): ?>
                    <option
                        value="<?= (int) $dataKelas['id_kelas'] ?>"
                        <?= $nilai['id_kelas'] === (string) $dataKelas['id_kelas'] ? 'selected' : '' ?>
                    >
                        <?= e($dataKelas['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($kesalahan['id_kelas'])): ?>
                <small class="field-error"><?= e($kesalahan['id_kelas']) ?></small>
            <?php endif; ?>
        </label>

        <label class="full-field">
            Alamat
            <textarea name="alamat" rows="3" maxlength="255"><?= e($nilai['alamat']) ?></textarea>
            <?php if (isset($kesalahan['alamat'])): ?>
                <small class="field-error"><?= e($kesalahan['alamat']) ?></small>
            <?php endif; ?>
        </label>

        <div class="form-actions">
            <button type="submit" class="button primary">Simpan Siswa</button>
            <a class="button light" href="<?= e(BASE_URL) ?>/siswa/index.php">Batal</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
