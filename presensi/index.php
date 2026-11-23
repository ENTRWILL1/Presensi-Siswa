<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$tanggalHariIni = date('Y-m-d');
$tanggalDipilih = get_string('tanggal');
$kataKunci = get_string('q');
$idKelasInput = get_string('id_kelas');

if ($tanggalDipilih === '') {
    $tanggalDipilih = $tanggalHariIni;
}

$pesanTanggal = '';
if (!is_valid_date($tanggalDipilih)) {
    $pesanTanggal = 'Format tanggal tidak valid. Menampilkan presensi hari ini.';
    $tanggalDipilih = $tanggalHariIni;
}

$idKelasDipilih = 0;
if ($idKelasInput !== '') {
    $idKelasValid = filter_var($idKelasInput, FILTER_VALIDATE_INT);

    if ($idKelasValid !== false && $idKelasValid > 0) {
        $idKelasDipilih = $idKelasValid;
    }
}

$daftarKelas = $pdo->query(
    'SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas'
)->fetchAll();

// Rekap menggunakan INNER JOIN tiga tabel: presensi, siswa, dan kelas.
$sql = '
    SELECT
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
    WHERE presensi.tanggal_waktu >= :waktu_mulai
      AND presensi.tanggal_waktu < :waktu_selesai
';

$waktuMulai = $tanggalDipilih . ' 00:00:00';
$waktuSelesai = date('Y-m-d', strtotime($tanggalDipilih . ' +1 day')) . ' 00:00:00';

$parameter = [
    'waktu_mulai' => $waktuMulai,
    'waktu_selesai' => $waktuSelesai,
];

if ($idKelasDipilih > 0) {
    $sql .= ' AND siswa.id_kelas = :id_kelas';
    $parameter['id_kelas'] = $idKelasDipilih;
}

if ($kataKunci !== '') {
    $sql .= '
        AND (
            siswa.nama LIKE :nama
            OR siswa.nisn LIKE :nisn
        )
    ';

    $nilaiCari = '%' . $kataKunci . '%';
    $parameter['nama'] = $nilaiCari;
    $parameter['nisn'] = $nilaiCari;
}

$sql .= ' ORDER BY presensi.tanggal_waktu DESC';

$pernyataanPresensi = $pdo->prepare($sql);
$pernyataanPresensi->execute($parameter);
$daftarPresensi = $pernyataanPresensi->fetchAll();

$pageTitle = 'Rekap Presensi';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">PRESENSI</p>
        <h1>Rekap Presensi</h1>
        <p class="muted">Secara bawaan menampilkan presensi hari ini.</p>
    </div>
    <a class="button primary" href="<?= e(BASE_URL) ?>/presensi/tap.php">Simulasi Tap</a>
</section>

<?php if ($pesanTanggal !== ''): ?>
    <div class="alert error" role="alert"><?= e($pesanTanggal) ?></div>
<?php endif; ?>

<section class="panel">
    <form method="get" class="filter-form">
        <input
            type="search"
            name="q"
            value="<?= e($kataKunci) ?>"
            placeholder="Cari nama siswa atau NISN..."
        >
        <input type="date" name="tanggal" value="<?= e($tanggalDipilih) ?>">
        <select name="id_kelas">
            <option value="">Semua Kelas</option>
            <?php foreach ($daftarKelas as $dataKelas): ?>
                <option
                    value="<?= (int) $dataKelas['id_kelas'] ?>"
                    <?= $idKelasDipilih === (int) $dataKelas['id_kelas'] ? 'selected' : '' ?>
                >
                    <?= e($dataKelas['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button secondary">Terapkan</button>
        <a class="button light" href="<?= e(BASE_URL) ?>/presensi/index.php">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal dan Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarPresensi === []): ?>
                    <tr>
                        <td colspan="6" class="empty">Tidak ada data presensi untuk filter tersebut.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPresensi as $nomor => $catatan): ?>
                        <tr>
                            <td><?= $nomor + 1 ?></td>
                            <td><?= e($catatan['nisn']) ?></td>
                            <td class="strong"><?= e($catatan['nama']) ?></td>
                            <td><?= e($catatan['nama_kelas']) ?></td>
                            <td><?= e(date('d-m-Y H:i:s', strtotime($catatan['tanggal_waktu']))) ?></td>
                            <td class="actions">
                                <form
                                    method="post"
                                    action="<?= e(BASE_URL) ?>/presensi/delete.php"
                                    onsubmit="return confirm('Hapus catatan presensi ini?')"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $catatan['id'] ?>">
                                    <button type="submit" class="danger-link">Hapus Log</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p class="table-foot">Menampilkan <?= count($daftarPresensi) ?> catatan presensi.</p>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
