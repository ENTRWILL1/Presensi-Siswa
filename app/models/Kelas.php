<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Model untuk mengelola data kelas.
 */
class Kelas extends BaseModel
{
    private ?int $idKelas = null;
    private string $namaKelas = '';
    private string $jurusan = '';

    public function getIdKelas(): ?int
    {
        return $this->idKelas;
    }

    public function setIdKelas(?int $idKelas): void
    {
        $this->idKelas = $idKelas;
    }

    public function getNamaKelas(): string
    {
        return $this->namaKelas;
    }

    public function setNamaKelas(string $namaKelas): void
    {
        $this->namaKelas = trim($namaKelas);
    }

    public function getJurusan(): string
    {
        return $this->jurusan;
    }

    public function setJurusan(string $jurusan): void
    {
        $this->jurusan = trim($jurusan);
    }

    /** Mengambil semua kelas beserta jumlah siswa di setiap kelas. */
    public function getAll(): array
    {
        $sql = '
            SELECT
                kelas.id_kelas,
                kelas.nama_kelas,
                kelas.jurusan,
                COUNT(siswa.nisn) AS jumlah_siswa
            FROM kelas
            LEFT JOIN siswa
                ON siswa.id_kelas = kelas.id_kelas
            GROUP BY
                kelas.id_kelas,
                kelas.nama_kelas,
                kelas.jurusan
            ORDER BY kelas.nama_kelas ASC
        ';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Mengambil daftar singkat kelas untuk pilihan pada form. */
    public function getOptions(): array
    {
        $sql = 'SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas ASC';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Mencari satu kelas berdasarkan ID. */
    public function findById(int $idKelas): ?array
    {
        $sql = '
            SELECT id_kelas, nama_kelas, jurusan
            FROM kelas
            WHERE id_kelas = :id_kelas
            LIMIT 1
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute(['id_kelas' => $idKelas]);
        $data = $pernyataan->fetch();

        return $data ?: null;
    }

    /** Memeriksa apakah nama kelas sudah dipakai kelas lain. */
    public function isNameUsed(string $namaKelas, ?int $kecualiId = null): bool
    {
        $sql = 'SELECT id_kelas FROM kelas WHERE nama_kelas = :nama_kelas';
        $parameter = ['nama_kelas' => trim($namaKelas)];

        if ($kecualiId !== null) {
            $sql .= ' AND id_kelas <> :id_kelas';
            $parameter['id_kelas'] = $kecualiId;
        }

        $sql .= ' LIMIT 1';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute($parameter);

        return (bool) $pernyataan->fetch();
    }

    /** Menambahkan kelas menggunakan nilai dari setter. */
    public function create(): bool
    {
        $sql = '
            INSERT INTO kelas (nama_kelas, jurusan)
            VALUES (:nama_kelas, :jurusan)
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);

        return $pernyataan->execute([
            'nama_kelas' => $this->getNamaKelas(),
            'jurusan' => $this->getJurusan(),
        ]);
    }

    /** Memperbarui kelas berdasarkan ID yang diatur melalui setter. */
    public function update(): bool
    {
        if ($this->getIdKelas() === null) {
            throw new InvalidArgumentException('ID kelas harus diisi sebelum diperbarui.');
        }

        $sql = '
            UPDATE kelas
            SET nama_kelas = :nama_kelas,
                jurusan = :jurusan
            WHERE id_kelas = :id_kelas
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);

        return $pernyataan->execute([
            'nama_kelas' => $this->getNamaKelas(),
            'jurusan' => $this->getJurusan(),
            'id_kelas' => $this->getIdKelas(),
        ]);
    }

    /** Menghitung jumlah seluruh kelas. */
    public function countAll(): int
    {
        return (int) $this->getKoneksi()
            ->query('SELECT COUNT(*) FROM kelas')
            ->fetchColumn();
    }

    /**
     * Menghapus kelas hanya jika belum memiliki siswa.
     * Mengembalikan false jika ID kelas tidak ditemukan.
     */
    public function delete(int $idKelas): bool
    {
        $koneksi = $this->getKoneksi();

        $cekSiswa = $koneksi->prepare(
            'SELECT COUNT(*) FROM siswa WHERE id_kelas = :id_kelas'
        );
        $cekSiswa->execute(['id_kelas' => $idKelas]);

        if ((int) $cekSiswa->fetchColumn() > 0) {
            throw new RuntimeException(
                'Kelas tidak dapat dihapus karena masih memiliki siswa.'
            );
        }

        try {
            $hapus = $koneksi->prepare(
                'DELETE FROM kelas WHERE id_kelas = :id_kelas'
            );
            $hapus->execute(['id_kelas' => $idKelas]);

            return $hapus->rowCount() > 0;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                throw new RuntimeException(
                    'Kelas tidak dapat dihapus karena masih digunakan oleh data lain.'
                );
            }

            throw $exception;
        }
    }
}
