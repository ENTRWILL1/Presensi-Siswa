-- =============================================================
-- DATABASE: SISTEM PRESENSI SISWA
-- Platform : MariaDB
-- Bahasa   : PHP Native + PDO
-- Struktur : 3 tabel inti + 1 tabel pendukung
-- =============================================================

CREATE DATABASE IF NOT EXISTS presensi_siswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE presensi_siswa;

-- =============================================================
-- 1. TABEL INTI: KELAS
-- Satu kelas dapat memiliki banyak siswa.
-- =============================================================
CREATE TABLE IF NOT EXISTS kelas (
    id_kelas INT NOT NULL AUTO_INCREMENT,
    nama_kelas VARCHAR(20) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,

    CONSTRAINT pk_kelas PRIMARY KEY (id_kelas),
    CONSTRAINT uq_kelas_nama UNIQUE (nama_kelas)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 2. TABEL INTI: SISWA
-- NISN memakai CHAR(10), bukan INT, agar angka nol di depan
-- tetap tersimpan. Validasi tepat 10 digit dilakukan di PHP.
-- =============================================================
CREATE TABLE IF NOT EXISTS siswa (
    nisn CHAR(10) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NULL,
    id_kelas INT NOT NULL,

    CONSTRAINT pk_siswa PRIMARY KEY (nisn),
    CONSTRAINT fk_siswa_kelas
        FOREIGN KEY (id_kelas)
        REFERENCES kelas (id_kelas)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_siswa_nama (nama),
    INDEX idx_siswa_id_kelas (id_kelas)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 3. TABEL INTI: PRESENSI
-- Satu baris mewakili satu kali tap kartu.
-- Form hanya mengirim NISN. ID dan tanggal_waktu otomatis
-- dibuat oleh MariaDB.
-- =============================================================
CREATE TABLE IF NOT EXISTS presensi (
    id INT NOT NULL AUTO_INCREMENT,
    nisn CHAR(10) NOT NULL,
    tanggal_waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_presensi PRIMARY KEY (id),
    CONSTRAINT fk_presensi_siswa
        FOREIGN KEY (nisn)
        REFERENCES siswa (nisn)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_presensi_nisn (nisn),
    INDEX idx_presensi_tanggal_waktu (tanggal_waktu),
    INDEX idx_presensi_tanggal_nisn (tanggal_waktu, nisn)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 4. TABEL PENDUKUNG: USERS
-- Menyimpan akun administrator untuk login.
-- Kolom password berisi hasil password_hash(), bukan teks asli.
-- Tabel ini berdiri sendiri dan tidak berelasi dengan tabel inti.
-- =============================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,

    CONSTRAINT pk_users PRIMARY KEY (id_user),
    CONSTRAINT uq_users_username UNIQUE (username)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;