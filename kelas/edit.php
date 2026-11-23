<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$idKelas = filter_input(INPUT_GET, 'id_kelas', FILTER_VALIDATE_INT);

if (!$idKelas || $idKelas < 1) {
    http_response_code(400);
    exit('ID kelas tidak valid.');
}

$cariKelas = $pdo->prepare(
    'SELECT id_kelas, nama_kelas, jurusan
     FROM kelas
     WHERE id_kelas = :id_kelas
     LIMIT 1'
);
$cariKelas->execute(['id_kelas' => $idKelas]);
$dataKelas = $cariKelas->fetch();

if (!$dataKelas) {
    http_response_code(404);
    exit('Data kelas tidak ditemukan.');
}

$nilai = [
    'nama_kelas' => $dataKelas['nama_kelas'],
    'jurusan' => $dataKelas['jurusan'],
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
            'SELECT id_kelas
             FROM kelas
             WHERE nama_kelas = :nama_kelas
               AND id_kelas <> :id_kelas
             LIMIT 1'
        );
        $cekNama->execute([
            'nama_kelas' => $nilai['nama_kelas'],
            'id_kelas' => $idKelas,
        ]);

        if ($cekNama->fetch()) {
            $kesalahan['nama_kelas'] = 'Nama kelas sudah digunakan kelas lain.';
        }
    }

    if ($nilai['jurusan'] === '') {
        $kesalahan['jurusan'] = 'Jurusan wajib diisi.';
    } elseif (text_length($nilai['jurusan']) > 50) {
        $kesalahan['jurusan'] = 'Jurusan maksimal 50 karakter.';
    }

    if ($kesalahan === []) {
        $ubahKelas = $pdo->prepare(
            'UPDATE kelas
             SET nama_kelas = :nama_kelas,
                 jurusan = :jurusan
             WHERE id_kelas = :id_kelas'
        );
        $ubahKelas->execute([
            'nama_kelas' => $nilai['nama_kelas'],
            'jurusan' => $nilai['jurusan'],
            'id_kelas' => $idKelas,
        ]);

        flash('success', 'Data kelas berhasil diperbarui.');
        redirect('kelas/index.php');
    }
}

$pageTitle = 'Edit Kelas';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Edit Kelas</h1>
        <p class="muted">Perbarui nama kelas dan jurusan.</p>
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
                required
            >
            <?php if (isset($kesalahan['jurusan'])): ?>
                <small class="field-error"><?= e($kesalahan['jurusan']) ?></small>
            <?php endif; ?>
        </label>

        <div class="form-actions">
            <button type="submit" class="button primary">Simpan Perubahan</button>
            <a class="button light" href="<?= e(BASE_URL) ?>/kelas/index.php">Batal</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
