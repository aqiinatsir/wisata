<?php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: index.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telepon = trim($_POST['telepon'] ?? '');

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        $result = registerUser($nama, $email, $password, $telepon);
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Portal Wisata Daerah</title>
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-bg">
        <div class="auth-bg-overlay"></div>
    </div>

    <div class="auth-container">
        <div class="auth-card auth-card-register">
            <div class="auth-logo">
                <a href="index.html">WISATA<span>DAERAH</span></a>
            </div>
            <h2 class="auth-title">Buat Akun Baru</h2>
            <p class="auth-subtitle">Bergabung dan mulai petualangan Anda</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="auth-form">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" id="nama" name="nama" placeholder="Nama lengkap Anda" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉</span>
                        <input type="email" id="email" name="email" placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="telepon">Nomor Telepon</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📱</span>
                        <input type="tel" id="telepon" name="telepon" placeholder="+62 xxx xxxx xxxx" value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="toggle-pass" onclick="togglePassword('password', this)">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                        <button type="button" class="toggle-pass" onclick="togglePassword('confirm_password', this)">👁</button>
                    </div>
                </div>

                <div class="password-strength" id="strengthBar" style="display:none">
                    <div class="strength-fill" id="strengthFill"></div>
                    <span id="strengthText">Lemah</span>
                </div>

                <button type="submit" class="btn-auth">Daftar Sekarang</button>
            </form>

            <p class="auth-switch">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁';
            }
        }

        document.getElementById('password').addEventListener('input', function() {
            const val = this.value;
            const bar = document.getElementById('strengthBar');
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            if (val.length === 0) { bar.style.display = 'none'; return; }
            bar.style.display = 'flex';
            let strength = 0;
            if (val.length >= 6) strength++;
            if (val.length >= 10) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;
            const levels = ['', 'Lemah', 'Cukup', 'Baik', 'Kuat', 'Sangat Kuat'];
            const colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
            fill.style.width = (strength * 20) + '%';
            fill.style.background = colors[strength];
            text.textContent = levels[strength];
        });
    </script>
</body>
</html>
