# Sistem Presensi Siswa

Aplikasi web untuk mengelola data kelas, data siswa, dan pencatatan kehadiran siswa melalui simulasi tap kartu menggunakan NISN. Project ini dibuat sebagai bagian dari Uji Unjuk Kerja (UUK) PPLG dengan PHP Native, PDO, dan MariaDB.

> **Catatan:** Fitur tap pada aplikasi ini merupakan simulasi melalui input NISN. Integrasi dengan perangkat keras atau kartu RFID fisik tidak termasuk dalam project ini.

---

## Daftar Isi

- [Tentang Aplikasi](#tentang-aplikasi)
- [Fitur Aplikasi](#fitur-aplikasi)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi dan Menjalankan Aplikasi](#instalasi-dan-menjalankan-aplikasi)
- [Akun Demo](#akun-demo)
- [Database dan ERD](#database-dan-erd)
- [Struktur Folder](#struktur-folder)
- [Hasil Pengujian](#hasil-pengujian)
- [Tangkapan Layar Halaman](#tangkapan-layar-halaman)

---

## Tentang Aplikasi

**Sistem Presensi Siswa** adalah aplikasi berbasis web yang membantu administrator mengelola informasi kelas dan siswa serta mencatat kehadiran siswa. Pencatatan presensi dilakukan melalui halaman **Simulasi Tap Kartu** dengan memasukkan NISN siswa. Jika NISN terdaftar, aplikasi menyimpan catatan presensi dan MariaDB mengisi tanggal serta waktu secara otomatis.

Aplikasi ini menggunakan satu jenis akun, yaitu **Administrator**. Administrator dapat masuk ke aplikasi untuk mengelola data kelas, data siswa, dan catatan presensi.

## Fitur Aplikasi

- **Login dan logout:** Administrator masuk menggunakan username dan password, lalu dapat mengakhiri sesi melalui fitur logout.
- **Dashboard:** Menampilkan ringkasan data kelas, siswa, presensi hari ini, dan catatan presensi terbaru.
- **Kelola kelas:** Menampilkan, menambahkan, mengubah, dan menghapus data kelas. Kelas yang masih memiliki siswa tidak dapat dihapus.
- **Kelola siswa:** Menampilkan, mencari, menambahkan, mengubah, dan menghapus data siswa.
- **Validasi NISN:** Memastikan NISN berisi tepat 10 digit dan tidak digunakan oleh siswa lain.
- **Simulasi tap kartu:** Menerima NISN siswa terdaftar dan menyimpan catatan presensi dengan waktu otomatis.
- **Rekap presensi:** Menampilkan catatan presensi beserta informasi siswa dan kelas, serta menyediakan pencarian dan filter. Catatan presensi yang keliru dapat dihapus.

## Teknologi

- **Bahasa pemrograman:** PHP Native
- **Database:** MariaDB / MySQL
- **Koneksi database:** PDO
- **Frontend:** HTML dan CSS
- **Server lokal:** Laragon (Apache dan MariaDB)

## Persyaratan

Sebelum menjalankan aplikasi, siapkan:

- Sistem operasi Windows.
- Laragon yang menyediakan Apache, PHP, dan MariaDB.
- PHP dengan ekstensi `pdo` dan `pdo_mysql` aktif.
- Browser web.

## Instalasi dan Menjalankan Aplikasi

Ikuti langkah-langkah berikut untuk menjalankan project secara lokal menggunakan Laragon.

### 1. Letakkan folder project

Ekstrak folder project ke direktori web Laragon. Contoh lokasi:

```text
C:\laragon\www\UUK
```

Pastikan file `login.php`, `schema.sql`, dan folder `app`, `config`, `kelas`, `siswa`, serta `presensi` berada di dalam folder `UUK`.

### 2. Jalankan Laragon

Buka Laragon, kemudian jalankan layanan **Apache** dan **MariaDB**.

### 3. Import database

1. Buka phpMyAdmin melalui Laragon.
2. Pilih menu **Import**.
3. Pilih file `schema.sql` yang berada di folder utama project.
4. Jalankan proses import.

File `schema.sql` akan membuat database bernama `presensi_siswa` beserta tabel `kelas`, `siswa`, `presensi`, dan `users`.

### 4. Periksa konfigurasi database

Buka file:

```text
config/database.php
```

Konfigurasi bawaan untuk Laragon:

- **Host:** `127.0.0.1`
- **Database:** `presensi_siswa`
- **Username:** `root`
- **Password:** kosong
- **BASE_URL:** `/UUK`

Sesuaikan konfigurasi tersebut apabila username, password, host MariaDB, atau nama folder project berbeda.

### 5. Buat akun administrator

Buka alamat berikut melalui browser:

```text
http://localhost/UUK/setup_admin.php
```

Buat satu akun administrator. Ketentuan username pada aplikasi adalah 3–50 karakter dan hanya boleh menggunakan huruf, angka, titik, garis bawah, atau tanda hubung. Password minimal 8 karakter.

### 6. Hapus file setup

Setelah akun administrator berhasil dibuat, **hapus file `setup_admin.php`** dari folder project. File tersebut hanya digunakan untuk membuat akun administrator pertama kali.

### 7. Login ke aplikasi

Buka alamat berikut:

```text
http://localhost/UUK/
```

Masuk menggunakan username dan password administrator yang telah dibuat. Setelah berhasil login, halaman Dashboard akan ditampilkan.

## Akun Demo

Gunakan **satu akun administrator** yang sama untuk pengujian dan demonstrasi aplikasi.

- **Jenis akun:** Administrator
- **Username:** `Willy`
- **Password:** `12345678`

Pastikan username dan password di atas sama dengan akun demo yang benar-benar sudah dibuat melalui `setup_admin.php` dan tersedia pada database yang digunakan saat penilaian. Gunakan akun khusus demonstrasi, bukan akun pribadi.

## Database dan ERD

Database aplikasi bernama **`presensi_siswa`**. Database memiliki tiga tabel inti sesuai gambar kerja dan satu tabel pendukung untuk autentikasi administrator.

### ERD konseptual sesuai gambar kerja

![ERD Sistem Presensi Siswa](docs/erd.svg)

**Relasi pada ERD:**

- **Kelas (1) — (N) Siswa:** satu kelas dapat memiliki banyak siswa, sedangkan satu siswa terdaftar pada satu kelas.
- **Siswa (1) — (N) Presensi:** satu siswa dapat memiliki banyak catatan presensi.

Pada gambar kerja, atribut Foreign Key tidak digambarkan. Kolom penghubung ditambahkan ketika ERD dikonversi menjadi tabel relasional.

### Kolom penghubung (Foreign Key)

- Pada tabel `siswa`, kolom `id_kelas` merupakan Foreign Key yang mengacu ke `kelas.id_kelas`.
- Pada tabel `presensi`, kolom `nisn` merupakan Foreign Key yang mengacu ke `siswa.nisn`.

### Rincian tabel database

#### 1. Tabel `kelas`

- `id_kelas` — `INT`, Primary Key dan Auto Increment.
- `nama_kelas` — `VARCHAR(20)`, nama kelas, wajib diisi dan unik.
- `jurusan` — `VARCHAR(50)`, nama jurusan, wajib diisi.

#### 2. Tabel `siswa`

- `nisn` — `CHAR(10)`, Primary Key, menyimpan NISN 10 digit.
- `nama` — `VARCHAR(100)`, nama siswa, wajib diisi.
- `alamat` — `VARCHAR(255)`, alamat siswa, boleh kosong.
- `id_kelas` — `INT`, Foreign Key yang mengacu ke `kelas.id_kelas`.

#### 3. Tabel `presensi`

- `id` — `INT`, Primary Key dan Auto Increment.
- `nisn` — `CHAR(10)`, Foreign Key yang mengacu ke `siswa.nisn`.
- `tanggal_waktu` — `DATETIME`, waktu presensi, otomatis menggunakan `CURRENT_TIMESTAMP`.

#### 4. Tabel `users` (pendukung login)

- `id_user` — `INT`, Primary Key dan Auto Increment.
- `username` — `VARCHAR(50)`, username administrator dan harus unik.
- `password` — `VARCHAR(255)`, menyimpan password dalam bentuk hash.

Tabel `users` merupakan tabel pendukung autentikasi administrator dan bukan bagian dari tiga entitas utama pada ERD gambar kerja.

## Struktur Folder

```text
UUK/
├── app/
│   ├── bootstrap.php
│   ├── helpers.php
│   └── models/
│       ├── BaseModel.php
│       ├── Kelas.php
│       ├── Siswa.php
│       ├── Presensi.php
│       └── User.php
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── docs/
│   ├── erd.svg
│   └── screenshots/
│       └── testing/
├── kelas/
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   └── index.php
├── layouts/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── presensi/
│   ├── tap.php
│   ├── index.php
│   └── delete.php
├── siswa/
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   └── index.php
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── schema.sql
└── setup_admin.php
```

## Hasil Pengujian

Delapan skenario berikut telah dijalankan pada aplikasi. Setiap skenario dilengkapi tempat untuk menampilkan bukti tangkapan layar. Simpan screenshot asli sesuai nama file dan lokasi yang tercantum agar gambar muncul di README GitHub.

### 1. Login menggunakan password yang salah

- **Hasil yang diharapkan:** Login ditolak dan pesan kesalahan ditampilkan.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 1 - Login dengan password salah](docs/screenshots/testing/01-login-password-salah.png)

### 2. Membuka halaman yang dilindungi melalui URL tanpa login

- **Hasil yang diharapkan:** Pengguna diarahkan ke halaman login.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 2 - Akses halaman tanpa login](docs/screenshots/testing/02-akses-tanpa-login.png)

### 3. Menambahkan siswa dengan NISN 9 digit

- **Hasil yang diharapkan:** Validasi menolak data, pesan kesalahan tampil, dan isian form tidak hilang.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 3 - NISN 9 digit](docs/screenshots/testing/03-nisn-9-digit.png)

### 4. Menambahkan siswa dengan NISN yang sudah terdaftar

- **Hasil yang diharapkan:** Data ditolak dan pesan bahwa NISN sudah terdaftar ditampilkan.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 4 - NISN duplikat](docs/screenshots/testing/04-nisn-duplikat.png)

### 5. Simulasi tap menggunakan NISN yang tidak terdaftar

- **Hasil yang diharapkan:** Tap ditolak dan pesan yang jelas ditampilkan.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 5 - NISN tidak terdaftar](docs/screenshots/testing/05-tap-nisn-tidak-terdaftar.png)

### 6. Simulasi tap menggunakan NISN yang valid

- **Hasil yang diharapkan:** Catatan muncul pada rekap hari ini dengan waktu otomatis.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 6 - Tap dengan NISN valid dan hasil rekap](docs/screenshots/testing/06-tap-nisn-valid.png)

### 7. Menghapus kelas yang masih memiliki siswa

- **Hasil yang diharapkan:** Penghapusan ditolak dan alasan ditampilkan.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 7 - Menghapus kelas yang masih memiliki siswa](docs/screenshots/testing/07-hapus-kelas-berisi-siswa.png)

### 8. Mencari siswa menggunakan sebagian nama

- **Hasil yang diharapkan:** Hanya siswa yang sesuai dengan kata pencarian ditampilkan.
- **Hasil pengujian:** **Berhasil / Lulus.**
- **Bukti tangkapan layar:**

![Bukti pengujian 8 - Pencarian sebagian nama siswa](docs/screenshots/testing/08-pencarian-sebagian-nama.png)

> **Catatan:** Ganti seluruh gambar bukti di atas dengan screenshot asli hasil pengujian. Jangan hanya membuat folder atau menuliskan nama file; file gambar PNG/JPG-nya juga harus dimasukkan ke folder `docs/screenshots/testing/`.

## Tangkapan Layar Halaman

Selain bukti pengujian, masukkan tangkapan layar halaman utama aplikasi ke folder `docs/screenshots/`.

### 1. Halaman Login

![Halaman Login](docs/screenshots/01-login.png)

### 2. Dashboard

![Dashboard](docs/screenshots/02-dashboard.png)

### 3. Data Kelas

![Data Kelas](docs/screenshots/03-data-kelas.png)

### 4. Data Siswa

![Data Siswa](docs/screenshots/04-data-siswa.png)

### 5. Simulasi Tap Kartu

![Simulasi Tap Kartu](docs/screenshots/05-simulasi-tap.png)

### 6. Rekap Presensi

![Rekap Presensi](docs/screenshots/06-rekap-presensi.png)

---

## Informasi Project

- **Nama aplikasi:** Sistem Presensi Siswa
- **Jenis project:** Uji Unjuk Kerja (UUK) PPLG
- **Metode presensi:** Simulasi tap berdasarkan NISN
- **Database:** `presensi_siswa`
- **File database:** `schema.sql`

---

**Dibuat untuk memenuhi kebutuhan project Uji Unjuk Kerja (UUK) PPLG.**
