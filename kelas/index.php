<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

// Tampilkan kelas beserta jumlah siswa pada setiap kelas.
$pernyataan = $pdo->query(
    'SELECT
        kelas.id_kelas,
        kelas.nama_kelas,
        kelas.jurusan,
        COUNT(siswa.nisn) AS jumlah_siswa
     FROM kelas
     LEFT JOIN siswa
        ON siswa.id_kelas = kelas.id_kelas
     GROUP BY kelas.id_kelas, kelas.nama_kelas, kelas.jurusan
     ORDER BY kelas.nama_kelas'
);
$daftarKelas = $pernyataan->fetchAll();

$pageTitle = 'Data Kelas';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Data Kelas</h1>
        <p class="muted">Tambah, ubah, dan hapus data kelas.</p>
    </div>
    <a class="button primary" href="<?= e(BASE_URL) ?>/kelas/create.php">Tambah Kelas</a>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Jumlah Siswa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarKelas === []): ?>
                    <tr>
                        <td colspan="5" class="empty">Belum ada data kelas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarKelas as $nomor => $dataKelas): ?>
                        <tr>
                            <td><?= $nomor + 1 ?></td>
                            <td class="strong"><?= e($dataKelas['nama_kelas']) ?></td>
                            <td><?= e($dataKelas['jurusan']) ?></td>
                            <td><?= (int) $dataKelas['jumlah_siswa'] ?></td>
                            <td class="actions">
                                <a class="text-link"
                                    href="<?= e(BASE_URL) ?>/kelas/edit.php?id_kelas=<?= (int) $dataKelas['id_kelas'] ?>">Edit</a>
                                <form method="post" action="<?= e(BASE_URL) ?>/kelas/delete.php"
                                    onsubmit="return confirm('Hapus kelas ini?')">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id_kelas" value="<?= (int) $dataKelas['id_kelas'] ?>">
                                    <button type="submit" class="danger-link">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="table-foot">Menampilkan <?= count($daftarKelas) ?> data kelas.</p>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>