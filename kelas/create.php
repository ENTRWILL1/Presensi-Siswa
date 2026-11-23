<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$nilai = [
    'nama_kelas' => '',
    'jurusan' => '',
];
$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nilai['nama_kelas'] = post_string('nama_kelas');
    $nilai['jurusan'] = post_string('jurusan');

    if ($nilai['nama_kelas'] === '') {
        $kesalahan['nama_kelas'] = 'Nama kelas wajib diisi.';
    } elseif (text_length($nilai['nama_kelas']) > 20) {
        $kesalahan['nama_kelas'] = 'Nama kelas maksimal 20 karakter.';
    } else {
        $cekNama = $pdo->prepare(
            'SELECT id_kelas FROM kelas WHERE nama_kelas = :nama_kelas LIMIT 1'
        );
        $cekNama->execute(['nama_kelas' => $nilai['nama_kelas']]);

        if ($cekNama->fetch()) {
            $kesalahan['nama_kelas'] = 'Nama kelas sudah digunakan.';
        }
    }

    if ($nilai['jurusan'] === '') {
        $kesalahan['jurusan'] = 'Jurusan wajib diisi.';
    } elseif (text_length($nilai['jurusan']) > 50) {
        $kesalahan['jurusan'] = 'Jurusan maksimal 50 karakter.';
    }

    if ($kesalahan === []) {
        $tambahKelas = $pdo->prepare(
            'INSERT INTO kelas (nama_kelas, jurusan)
             VALUES (:nama_kelas, :jurusan)'
        );
        $tambahKelas->execute($nilai);

        flash('success', 'Data kelas berhasil ditambahkan.');
        redirect('kelas/index.php');
    }
}

$pageTitle = 'Tambah Kelas';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Tambah Kelas</h1>
        <p class="muted">Masukkan nama kelas dan jurusan.</p>
    </div>
    <a class="button light" href="<?= e(BASE_URL) ?>/kelas/index.php">Kembali</a>
</section>

<section class="panel form-panel">
    <form method="post" class="form-grid" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>
            Nama Kelas <span class="required">*</span>
            <input
                type="text"
                name="nama_kelas"
                maxlength="20"
                value="<?= e($nilai['nama_kelas']) ?>"
                placeholder="Contoh: XI PPLG 1"
                required
            >
            <?php if (isset($kesalahan['nama_kelas'])): ?>
                <small class="field-error"><?= e($kesalahan['nama_kelas']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Jurusan <span class="required">*</span>
            <input
                type="text"
                name="jurusan"
                maxlength="50"
                value="<?= e($nilai['jurusan']) ?>"
                placeholder="Contoh: PPLG"
                required
            >
            <?php if (isset($kesalahan['jurusan'])): ?>
                <small class="field-error"><?= e($kesalahan['jurusan']) ?></small>
            <?php endif; ?>
        </label>

        <div class="form-actions">
            <button type="submit" class="button primary">Simpan Kelas</button>
            <a class="button light" href="<?= e(BASE_URL) ?>/kelas/index.php">Batal</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
