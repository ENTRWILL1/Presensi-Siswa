<?php
declare(strict_types=1);

/**
 * Konfigurasi dan koneksi MariaDB menggunakan PDO.
 * Sesuaikan BASE_URL jika nama folder project berbeda.
 */
const BASE_URL = '/UUK';

final class Database
{
    private PDO $koneksi;

    public function __construct()
    {
        $host = '127.0.0.1';
        $namaDatabase = 'presensi_siswa';
        $username = 'root';
        $password = '';

        $dsn = "mysql:host={$host};dbname={$namaDatabase};charset=utf8mb4";

        try {
            $this->koneksi = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('Koneksi database gagal: ' . $exception->getMessage());
            http_response_code(500);
            exit('Koneksi database gagal. Periksa Laragon dan konfigurasi database.');
        }
    }

    public function getKoneksi(): PDO
    {
        return $this->koneksi;
    }
}
