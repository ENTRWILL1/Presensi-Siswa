<?php
declare(strict_types=1);

/**
 * Kelas dasar untuk seluruh model aplikasi.
 * Menyimpan koneksi PDO dan mewajibkan setiap model memiliki getAll().
 */
abstract class BaseModel
{
    private PDO $koneksi;

    public function __construct(PDO $koneksi)
    {
        $this->koneksi = $koneksi;
    }

    /** Mengambil koneksi PDO yang dimiliki model. */
    protected function getKoneksi(): PDO
    {
        return $this->koneksi;
    }

    /** Setiap model wajib menyediakan metode untuk mengambil data. */
    abstract public function getAll(): array;
}
