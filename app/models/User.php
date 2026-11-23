<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

/**
 * Model untuk autentikasi administrator pada tabel users.
 */
class User extends BaseModel
{
    private ?int $idUser = null;
    private string $username = '';
    private string $password = '';

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): void
    {
        $this->idUser = $idUser;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = trim($username);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /** Mengambil daftar akun tanpa menampilkan hash password. */
    public function getAll(): array
    {
        $sql = 'SELECT id_user, username FROM users ORDER BY username ASC';

        return $this->getKoneksi()->query($sql)->fetchAll();
    }

    /** Memeriksa username dan password untuk proses login. */
    public function authenticate(string $username, string $password): ?array
    {
        $sql = '
            SELECT id_user, username, password
            FROM users
            WHERE username = :username
            LIMIT 1
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);
        $pernyataan->execute(['username' => trim($username)]);
        $dataUser = $pernyataan->fetch();

        if (!$dataUser || !password_verify($password, $dataUser['password'])) {
            return null;
        }

        return [
            'id_user' => (int) $dataUser['id_user'],
            'username' => (string) $dataUser['username'],
        ];
    }

    /** Memeriksa apakah username sudah digunakan. */
    public function usernameExists(string $username): bool
    {
        $pernyataan = $this->getKoneksi()->prepare(
            'SELECT id_user FROM users WHERE username = :username LIMIT 1'
        );
        $pernyataan->execute(['username' => trim($username)]);

        return (bool) $pernyataan->fetch();
    }

    /** Menghitung jumlah akun administrator. */
    public function countAll(): int
    {
        return (int) $this->getKoneksi()
            ->query('SELECT COUNT(*) FROM users')
            ->fetchColumn();
    }

    /** Membuat akun administrator dengan password yang langsung di-hash. */
    public function create(): bool
    {
        $sql = '
            INSERT INTO users (username, password)
            VALUES (:username, :password)
        ';

        $pernyataan = $this->getKoneksi()->prepare($sql);

        return $pernyataan->execute([
            'username' => $this->getUsername(),
            'password' => password_hash($this->getPassword(), PASSWORD_DEFAULT),
        ]);
    }
}
