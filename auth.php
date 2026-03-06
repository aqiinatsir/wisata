<?php
// ============================================================
// auth.php - Authentication & User Management
// Portal Wisata Daerah
// Koneksi: MySQL via XAMPP (PDO)
// ============================================================

session_start();

// ── Konfigurasi Database ────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'wisata_daerah');   // Ganti sesuai nama database Anda
define('DB_USER', 'root');            // Default XAMPP
define('DB_PASS', '');                // Default XAMPP (kosong)
define('DB_CHARSET', 'utf8mb4');

// ── Koneksi PDO ─────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid red;border-radius:8px">
                    <h3>❌ Koneksi Database Gagal</h3>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    <p>Pastikan XAMPP MySQL sudah berjalan dan database <strong>' . DB_NAME . '</strong> sudah dibuat.</p>
                 </div>');
        }
    }
    return $pdo;
}

// ── Inisialisasi Tabel (auto-create jika belum ada) ─────────
function initDB(): void {
    $pdo = getDB();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id          VARCHAR(36)  NOT NULL PRIMARY KEY,
            nama        VARCHAR(100) NOT NULL,
            email       VARCHAR(150) NOT NULL UNIQUE,
            password    VARCHAR(255) NOT NULL,
            telepon     VARCHAR(20)  DEFAULT '',
            avatar      VARCHAR(255) DEFAULT '',
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}
initDB();

// ── Helper: Generate UUID v4 ────────────────────────────────
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// ── Registrasi User Baru ────────────────────────────────────
function registerUser(string $nama, string $email, string $password, string $telepon = ''): array {
    $pdo = getDB();

    // Cek email sudah terdaftar
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email sudah terdaftar!'];
    }

    $id   = generateUUID();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare('
        INSERT INTO users (id, nama, email, password, telepon)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$id, $nama, $email, $hash, $telepon]);

    return ['success' => true, 'message' => 'Registrasi berhasil!'];
}

// ── Login User ──────────────────────────────────────────────
function loginUser(string $email, string $password): array {
    $pdo = getDB();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email atau password salah!'];
    }

    // Set session
    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_nama']   = $user['nama'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? '';

    return ['success' => true, 'message' => 'Login berhasil!'];
}

// ── Cek Status Login ────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ── Wajib Login (redirect jika belum) ──────────────────────
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// ── Ambil Semua User (untuk validasi password di profile) ───
function getUsers(): array {
    $pdo  = getDB();
    $stmt = $pdo->query('SELECT * FROM users');
    return $stmt->fetchAll();
}

// ── Update Profil User ──────────────────────────────────────
function updateUser(string $userId, array $data): array {
    $pdo = getDB();

    $fields = [];
    $params = [];

    if (!empty($data['nama'])) {
        $fields[] = 'nama = ?';
        $params[]  = $data['nama'];
    }

    if (isset($data['telepon'])) {
        $fields[] = 'telepon = ?';
        $params[]  = $data['telepon'];
    }

    if (!empty($data['password'])) {
        $fields[] = 'password = ?';
        $params[]  = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    if (!empty($data['avatar'])) {
        $fields[] = 'avatar = ?';
        $params[]  = $data['avatar'];
    }

    if (empty($fields)) {
        return ['success' => false, 'message' => 'Tidak ada data yang diubah.'];
    }

    $params[] = $userId;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Refresh session data
    $stmt2 = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt2->execute([$userId]);
    $user = $stmt2->fetch();

    if ($user) {
        $_SESSION['user_nama']   = $user['nama'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? '';
    }

    return ['success' => true, 'message' => 'Profil berhasil diperbarui!'];
}