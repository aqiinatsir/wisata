<?php
// ============================================================
// auth.php — Koneksi DB + Autentikasi User
// ============================================================
session_start();

define('DB_HOST',    'localhost');
define('DB_NAME',    'wisata');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid red;border-radius:8px">
                <h3>❌ Koneksi Database Gagal</h3>
                <p>'.$e->getMessage().'</p>
                <p>Pastikan XAMPP MySQL aktif dan database <b>'.DB_NAME.'</b> sudah diimport.</p>
            </div>');
        }
    }
    return $pdo;
}

function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: login.php'); exit; }
}

function registerUser(string $nama, string $email, string $password, string $telepon = ''): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) return ['success'=>false,'message'=>'Email sudah terdaftar!'];

    $pdo->prepare('INSERT INTO users (id,nama,email,password,telepon) VALUES (?,?,?,?,?)')
        ->execute([generateUUID(), $nama, $email, password_hash($password, PASSWORD_BCRYPT), $telepon]);

    return ['success'=>true,'message'=>'Registrasi berhasil!'];
}

function loginUser(string $email, string $password): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password']))
        return ['success'=>false,'message'=>'Email atau password salah!'];

    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_nama']   = $user['nama'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
    return ['success'=>true,'message'=>'Login berhasil!'];
}

function getUsers(): array {
    return getDB()->query('SELECT * FROM users')->fetchAll();
}

function updateUser(string $userId, array $data): array {
    $pdo = getDB();
    $fields = []; $params = [];

    if (!empty($data['nama']))     { $fields[]='nama=?';     $params[]=$data['nama']; }
    if (isset($data['telepon']))   { $fields[]='telepon=?';  $params[]=$data['telepon']; }
    if (!empty($data['password'])) { $fields[]='password=?'; $params[]=password_hash($data['password'],PASSWORD_BCRYPT); }
    if (!empty($data['avatar']))   { $fields[]='avatar=?';   $params[]=$data['avatar']; }

    if (empty($fields)) return ['success'=>false,'message'=>'Tidak ada data yang diubah.'];

    $params[] = $userId;
    $pdo->prepare('UPDATE users SET '.implode(',',$fields).' WHERE id=?')->execute($params);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_nama']   = $u['nama'];
        $_SESSION['user_email']  = $u['email'];
        $_SESSION['user_avatar'] = $u['avatar'] ?? '';
    }
    return ['success'=>true,'message'=>'Profil berhasil diperbarui!'];
}
