<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$kataKunci = get_string('q');

// INNER JOIN menampilkan nama kelas dari tabel kelas.
$sql = '
    SELECT
        siswa.nisn,
        siswa.nama,
        siswa.alamat,
        kelas.nama_kelas
    FROM siswa
    INNER JOIN kelas
        ON kelas.id_kelas = siswa.id_kelas
';

$parameter = [];

if ($kataKunci !== '') {
    $sql .= '
        WHERE siswa.nisn LIKE :nisn
           OR siswa.nama LIKE :nama
    ';

    $nilaiCari = '%' . $kataKunci . '%';
    $parameter['nisn'] = $nilaiCari;
    $parameter['nama'] = $nilaiCari;
}

$sql .= ' ORDER BY siswa.nama ASC';

$pernyataan = $pdo->prepare($sql);
$pernyataan->execute($parameter);
$daftarSiswa = $pernyataan->fetchAll();

$pageTitle = 'Data Siswa';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Data Siswa</h1>
        <p class="muted">Kelola data siswa dan kelasnya.</p>
    </div>
    <a class="button primary" href="<?= e(BASE_URL) ?>/siswa/create.php">Tambah Siswa</a>
</section>

<section class="panel">
    <form method="get" class="filter-form">
        <input
            type="search"
            name="q"
            value="<?= e($kataKunci) ?>"
            placeholder="Cari berdasarkan NISN atau nama siswa..."
        >
        <button type="submit" class="button secondary">Cari</button>
        <a class="button light" href="<?= e(BASE_URL) ?>/siswa/index.php">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarSiswa === []): ?>
                    <tr>
                        <td colspan="6" class="empty">Data siswa tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarSiswa as $nomor => $dataSiswa): ?>
                        <tr>
                            <td><?= $nomor + 1 ?></td>
                            <td><?= e($dataSiswa['nisn']) ?></td>
                            <td class="strong"><?= e($dataSiswa['nama']) ?></td>
                            <td><?= e($dataSiswa['nama_kelas']) ?></td>
                            <td
                                class="address-cell"
                                title="<?= e($dataSiswa['alamat'] ?? '') ?>"
                            >
                                <?php if (trim((string) ($dataSiswa['alamat'] ?? '')) !== ''): ?>
                                    <?= e($dataSiswa['alamat']) ?>
                                <?php else: ?>
                                    <span class="muted">Belum diisi</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a
                                    class="text-link"
                                    href="<?= e(BASE_URL) ?>/siswa/edit.php?nisn=<?= rawurlencode($dataSiswa['nisn']) ?>"
                                >Edit</a>
                                <form
                                    method="post"
                                    action="<?= e(BASE_URL) ?>/siswa/delete.php"
                                    onsubmit="return confirm('Hapus data siswa ini?')"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="nisn" value="<?= e($dataSiswa['nisn']) ?>">
                                    <button type="submit" class="danger-link">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p class="table-foot">Menampilkan <?= count($daftarSiswa) ?> data siswa.</p>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
