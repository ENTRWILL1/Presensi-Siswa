<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Model untuk mencatat dan menampilkan data presensi.
 */
class Presensi extends BaseModel
{
    private ?int $id = null;
    private string $nisn = '';
    private ?string $tanggalWaktu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNisn(): string
    {
        return $this->nisn;
    }

    public function setNisn(string $nisn): void
    {
        $this->nisn = trim($nisn);
    }

    public function getTanggalWaktu(): ?string
    {
        return $this->tanggalWaktu;
    }

    public function setTanggalWaktu(?string $tanggalWaktu): void
    {
        $this->tanggalWaktu = $tanggalWaktu;
    }

    /** Mengambil seluruh catatan presensi dengan INNER JOIN tiga tabel. */
    public function getAll(): array
    {
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
            ORDER BY presensi.tanggal_waktu DESC
        ';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Mengambil presensi sesuai tanggal, kelas, dan kata kunci. */
    public function getFiltered(
        string $tanggal,
        ?int $idKelas = null,
        string $kataKunci = ''
    ): array {
        $waktuMulai = $tanggal . ' 00:00:00';
        $waktuSelesai = date(
            'Y-m-d',
            strtotime($tanggal . ' +1 day')
        ) . ' 00:00:00';

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

        $parameter = [
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ];

        if ($idKelas !== null && $idKelas > 0) {
            $sql .= ' AND siswa.id_kelas = :id_kelas';
            $parameter['id_kelas'] = $idKelas;
        }

        if (trim($kataKunci) !== '') {
            $sql .= '
                AND (
                    siswa.nama LIKE :nama
                    OR siswa.nisn LIKE :nisn
                )
            ';

            $nilaiCari = '%' . trim($kataKunci) . '%';
            $parameter['nama'] = $nilaiCari;
            $parameter['nisn'] = $nilaiCari;
        }

        $sql .= ' ORDER BY presensi.tanggal_waktu DESC';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute($parameter);

        return $pernyataan->fetchAll();
    }

    /** Mencatat tap. Hanya NISN yang dikirim ke INSERT. */
    public function createTap(string $nisn): ?array
    {
        $koneksi = $this->getKoneksi();

        $cariSiswa = $koneksi->prepare(
            'SELECT nisn, nama FROM siswa WHERE nisn = :nisn LIMIT 1'
        );
        $cariSiswa->execute(['nisn' => trim($nisn)]);
        $dataSiswa = $cariSiswa->fetch();

        if (!$dataSiswa) {
            return null;
        }

        $tambahPresensi = $koneksi->prepare(
            'INSERT INTO presensi (nisn) VALUES (:nisn)'
        );
        $tambahPresensi->execute(['nisn' => trim($nisn)]);

        $this->setId((int) $koneksi->lastInsertId());
        $this->setNisn($nisn);

        return $dataSiswa;
    }

    /** Menghitung jumlah presensi pada hari ini. */
    public function countToday(): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM presensi
            WHERE tanggal_waktu >= CURRENT_DATE()
              AND tanggal_waktu < CURRENT_DATE() + INTERVAL 1 DAY
        ';

        return (int) $this->getKoneksi()->query($sql)->fetchColumn();
    }

    /** Mengambil delapan catatan presensi terbaru untuk dashboard. */
    public function getLatest(): array
    {
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
            ORDER BY presensi.tanggal_waktu DESC
            LIMIT 8
        ';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Menghapus satu catatan presensi berdasarkan ID. */
    public function deleteById(int $id): bool
    {
        $pernyataan = $this->getKoneksi()->prepare(
            'DELETE FROM presensi WHERE id = :id'
        );
        $pernyataan->execute(['id' => $id]);

        return $pernyataan->rowCount() > 0;
    }
}
