<?php
require_once 'auth.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama' => trim($_POST['nama'] ?? ''),
        'telepon' => trim($_POST['telepon'] ?? ''),
        'password' => $_POST['new_password'] ?? '',
    ];

    $current_password = $_POST['current_password'] ?? '';

    // Validate current password if changing password
    if (!empty($data['password'])) {
        if (empty($current_password)) {
            $error = 'Masukkan password saat ini untuk menggantinya!';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'Konfirmasi password baru tidak cocok!';
        } elseif (strlen($data['password']) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } else {
            // Verify current password
            $users = getUsers();
            $valid = false;
            foreach ($users as $user) {
                if ($user['id'] === $_SESSION['user_id']) {
                    $valid = password_verify($current_password, $user['password']);
                    break;
                }
            }
            if (!$valid) {
                $error = 'Password saat ini salah!';
            }
        }
    } else {
        $data['password'] = '';
    }

    if (empty($error)) {
        // Handle avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array(strtolower($ext), $allowed)) {
                $avatarDir = __DIR__ . '/avatars/';
                if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);
                $filename = $_SESSION['user_id'] . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarDir . $filename)) {
                    $data['avatar'] = 'avatars/' . $filename;
                }
            } else {
                $error = 'Format foto tidak didukung!';
            }
        }

        if (empty($error)) {
            $result = updateUser($_SESSION['user_id'], $data);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get current user data
$users = getUsers();
$currentUser = null;
foreach ($users as $user) {
    if ($user['id'] === $_SESSION['user_id']) {
        $currentUser = $user;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Portal Wisata Daerah</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1><a href="index.html" style="color:inherit;text-decoration:none">WISATA<span>DAERAH</span></a></h1>
            </div>
            <nav class="navbar">
                <ul class="nav-list">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="destinasi.html">Destinasi</a></li>
                    <li><a href="paket-wisata.html">Paket Wisata</a></li>
                    <li><a href="kontak.html">Kontak Kami</a></li>
                </ul>
                <div class="user-menu">
                    <div class="user-avatar-small">
                        <?php if (!empty($_SESSION['user_avatar']) && file_exists($_SESSION['user_avatar'])): ?>
                            <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                            <span><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="user-name-nav"><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                    <div class="user-dropdown">
                        <a href="profile.php">👤 Profil Saya</a>
                        <a href="logout.php" class="logout-link">🚪 Logout</a>
                    </div>
                </div>
                <div class="hamburger"><span></span><span></span><span></span></div>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Profil Saya</h2>
                <p>Kelola informasi akun Anda</p>
            </div>
        </section>

        <section class="profile-section">
            <div class="container">
                <div class="profile-grid">
                    <!-- Sidebar -->
                    <div class="profile-sidebar">
                        <div class="avatar-container">
                            <div class="avatar-preview" id="avatarPreview">
                                <?php if (!empty($currentUser['avatar']) && file_exists($currentUser['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Avatar" id="avatarImg">
                                <?php else: ?>
                                    <div class="avatar-initials"><?= strtoupper(substr($currentUser['nama'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <p class="profile-name"><?= htmlspecialchars($currentUser['nama']) ?></p>
                            <p class="profile-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                            <p class="profile-joined">Bergabung: <?= date('d M Y', strtotime($currentUser['created_at'])) ?></p>
                        </div>

                        <div class="profile-nav">
                            <a href="#info" class="profile-nav-item active">📋 Informasi Akun</a>
                            <a href="#security" class="profile-nav-item">🔒 Keamanan</a>
                            <a href="logout.php" class="profile-nav-item logout">🚪 Logout</a>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="profile-content">
                        <?php if ($error): ?>
                            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php" enctype="multipart/form-data">
                            <div class="profile-card" id="info">
                                <h3>Informasi Akun</h3>

                                <div class="form-group">
                                    <label>Foto Profil</label>
                                    <div class="avatar-upload">
                                        <input type="file" id="avatarUpload" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                                        <label for="avatarUpload" class="btn-upload">📷 Ganti Foto</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nama">Nama Lengkap *</label>
                                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email_display">Email</label>
                                    <input type="email" id="email_display" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled class="input-disabled">
                                    <small>Email tidak dapat diubah</small>
                                </div>

                                <div class="form-group">
                                    <label for="telepon">Nomor Telepon</label>
                                    <input type="tel" id="telepon" name="telepon" value="<?= htmlspecialchars($currentUser['telepon']) ?>" placeholder="+62 xxx xxxx xxxx">
                                </div>
                            </div>

                            <div class="profile-card" id="security">
                                <h3>Ubah Password</h3>
                                <p class="form-hint">Kosongkan jika tidak ingin mengubah password</p>

                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" placeholder="Masukkan password saat ini">
                                </div>

                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru">
                                </div>
                            </div>

                            <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 Portal Wisata Daerah. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="avatarImg">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.querySelectorAll('.profile-nav-item:not(.logout)').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.profile-nav-item').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
    <script src="script.js"></script>
</body>
</html>
