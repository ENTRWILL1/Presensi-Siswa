<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Model untuk mengelola data siswa.
 */
class Siswa extends BaseModel
{
    private string $nisn = '';
    private string $nama = '';
    private string $alamat = '';
    private ?int $idKelas = null;

    public function getNisn(): string
    {
        return $this->nisn;
    }

    public function setNisn(string $nisn): void
    {
        $this->nisn = trim($nisn);
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function setNama(string $nama): void
    {
        $this->nama = trim($nama);
    }

    public function getAlamat(): string
    {
        return $this->alamat;
    }

    public function setAlamat(string $alamat): void
    {
        $this->alamat = trim($alamat);
    }

    public function getIdKelas(): ?int
    {
        return $this->idKelas;
    }

    public function setIdKelas(?int $idKelas): void
    {
        $this->idKelas = $idKelas;
    }

    /** Mengambil semua siswa dan nama kelas melalui INNER JOIN. */
    public function getAll(): array
    {
        $sql = '
            SELECT
                siswa.nisn,
                siswa.nama,
                siswa.alamat,
                siswa.id_kelas,
                kelas.nama_kelas
            FROM siswa
            INNER JOIN kelas
                ON kelas.id_kelas = siswa.id_kelas
            ORDER BY siswa.nama ASC
        ';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Mencari siswa berdasarkan nama atau NISN. */
    public function search(string $kataKunci): array
    {
        if (trim($kataKunci) === '') {
            return $this->getAll();
        }

        $sql = '
            SELECT
                siswa.nisn,
                siswa.nama,
                siswa.alamat,
                siswa.id_kelas,
                kelas.nama_kelas
            FROM siswa
            INNER JOIN kelas
                ON kelas.id_kelas = siswa.id_kelas
            WHERE siswa.nama LIKE :nama
               OR siswa.nisn LIKE :nisn
            ORDER BY siswa.nama ASC
        ';

        $nilaiCari = '%' . trim($kataKunci) . '%';
        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute([
            'nama' => $nilaiCari,
            'nisn' => $nilaiCari,
        ]);

        return $pernyataan->fetchAll();
    }

    /** Mencari satu siswa berdasarkan NISN. */
    public function findByNisn(string $nisn): ?array
    {
        $sql = '
            SELECT nisn, nama, alamat, id_kelas
            FROM siswa
            WHERE nisn = :nisn
            LIMIT 1
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute(['nisn' => $nisn]);
        $data = $pernyataan->fetch();

        return $data ?: null;
    }

    /** Memeriksa apakah NISN sudah digunakan siswa lain. */
    public function isNisnUsed(string $nisn, ?string $kecualiNisn = null): bool
    {
        $sql = 'SELECT nisn FROM siswa WHERE nisn = :nisn';
        $parameter = ['nisn' => trim($nisn)];

        if ($kecualiNisn !== null) {
            $sql .= ' AND nisn <> :nisn_lama';
            $parameter['nisn_lama'] = $kecualiNisn;
        }

        $sql .= ' LIMIT 1';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute($parameter);

        return (bool) $pernyataan->fetch();
    }

    /** Memeriksa keberadaan kelas yang dipilih. */
    public function classExists(int $idKelas): bool
    {
        $pernyataan = $this->getKoneksi()->prepare(
            'SELECT id_kelas FROM kelas WHERE id_kelas = :id_kelas LIMIT 1'
        );
        $pernyataan->execute(['id_kelas' => $idKelas]);

        return (bool) $pernyataan->fetch();
    }

    /** Menambahkan siswa menggunakan nilai dari setter. */
    public function create(): bool
    {
        if ($this->getIdKelas() === null) {
            throw new InvalidArgumentException('Kelas siswa harus dipilih.');
        }

        $sql = '
            INSERT INTO siswa (nisn, nama, alamat, id_kelas)
            VALUES (:nisn, :nama, :alamat, :id_kelas)
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);

        return $pernyataan->execute([
            'nisn' => $this->getNisn(),
            'nama' => $this->getNama(),
            'alamat' => $this->getAlamat() !== '' ? $this->getAlamat() : null,
            'id_kelas' => $this->getIdKelas(),
        ]);
    }

    /** Memperbarui data siswa, termasuk NISN jika berubah. */
    public function update(string $nisnLama): bool
    {
        if ($this->getIdKelas() === null) {
            throw new InvalidArgumentException('Kelas siswa harus dipilih.');
        }

        $sql = '
            UPDATE siswa
            SET nisn = :nisn_baru,
                nama = :nama,
                alamat = :alamat,
                id_kelas = :id_kelas
            WHERE nisn = :nisn_lama
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);

        return $pernyataan->execute([
            'nisn_baru' => $this->getNisn(),
            'nama' => $this->getNama(),
            'alamat' => $this->getAlamat() !== '' ? $this->getAlamat() : null,
            'id_kelas' => $this->getIdKelas(),
            'nisn_lama' => $nisnLama,
        ]);
    }

    /** Menghitung jumlah seluruh siswa. */
    public function countAll(): int
    {
        return (int) $this->getKoneksi()
            ->query('SELECT COUNT(*) FROM siswa')
            ->fetchColumn();
    }

    /**
     * Menghapus siswa hanya jika belum memiliki catatan presensi.
     * Mengembalikan false jika siswa tidak ditemukan.
     */
    public function delete(string $nisn): bool
    {
        $koneksi = $this->getKoneksi();

        $cekPresensi = $koneksi->prepare(
            'SELECT COUNT(*) FROM presensi WHERE nisn = :nisn'
        );
        $cekPresensi->execute(['nisn' => $nisn]);

        if ((int) $cekPresensi->fetchColumn() > 0) {
            throw new RuntimeException(
                'Siswa tidak dapat dihapus karena masih memiliki riwayat presensi. Hapus log presensinya terlebih dahulu.'
            );
        }

        try {
            $hapus = $koneksi->prepare(
                'DELETE FROM siswa WHERE nisn = :nisn'
            );
            $hapus->execute(['nisn' => $nisn]);

            return $hapus->rowCount() > 0;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                throw new RuntimeException(
                    'Siswa tidak dapat dihapus karena masih terhubung dengan data presensi.'
                );
            }

            throw $exception;
        }
    }
}
