<?php
require_once 'auth.php';
requireLogin();

// Handle cart operations
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $paket_id = $_POST['paket_id'];
        $pakets = getPakets();
        if (isset($pakets[$paket_id])) {
            $cart[$paket_id] = [
                'id' => $paket_id,
                'nama' => $pakets[$paket_id]['nama'],
                'harga' => $pakets[$paket_id]['harga'],
                'durasi' => $pakets[$paket_id]['durasi'],
                'qty' => 1,
                'tanggal' => $_POST['tanggal'] ?? '',
                'peserta' => intval($_POST['peserta'] ?? 1),
            ];
            $_SESSION['cart'] = $cart;
            header('Location: checkout.php');
            exit;
        }
    }
    if ($_POST['action'] === 'remove') {
        unset($cart[$_POST['paket_id']]);
        $_SESSION['cart'] = $cart;
        header('Location: checkout.php');
        exit;
    }
    if ($_POST['action'] === 'update') {
        if (isset($cart[$_POST['paket_id']])) {
            $cart[$_POST['paket_id']]['peserta'] = max(1, intval($_POST['peserta']));
            $cart[$_POST['paket_id']]['tanggal'] = $_POST['tanggal'] ?? '';
        }
        $_SESSION['cart'] = $cart;
        header('Location: checkout.php');
        exit;
    }
}

function getPakets() {
    return [
        'hemat' => ['nama' => 'Paket Hemat', 'harga' => 500000, 'durasi' => '1 Hari'],
        'reguler' => ['nama' => 'Paket Reguler', 'harga' => 1500000, 'durasi' => '2H1M'],
        'premium' => ['nama' => 'Paket Premium', 'harga' => 3000000, 'durasi' => '3H2M'],
        'eksklusif' => ['nama' => 'Paket Eksklusif', 'harga' => 7500000, 'durasi' => '5H4M'],
        'keluarga' => ['nama' => 'Paket Keluarga', 'harga' => 2000000, 'durasi' => '2H1M'],
    ];
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['harga'] * $item['peserta'];
}

$view = isset($_GET['view']) ? $_GET['view'] : 'cart';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $view === 'payment' ? 'Pembayaran' : 'Keranjang' ?> - Portal Wisata Daerah</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="checkout.css">
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
                        <span><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?></span>
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
                <h2><?= $view === 'payment' ? '💳 Pilih Pembayaran' : '🛒 Keranjang Wisata' ?></h2>
                <p><?= $view === 'payment' ? 'Selesaikan pemesanan Anda' : 'Kelola paket wisata pilihan Anda' ?></p>
            </div>
        </section>

        <!-- Stepper -->
        <div class="checkout-stepper">
            <div class="container">
                <div class="steps">
                    <div class="step <?= $view === 'cart' ? 'active' : 'done' ?>">
                        <div class="step-num"><?= $view !== 'cart' ? '✓' : '1' ?></div>
                        <span>Keranjang</span>
                    </div>
                    <div class="step-line <?= $view === 'payment' ? 'done' : '' ?>"></div>
                    <div class="step <?= $view === 'payment' ? 'active' : '' ?>">
                        <div class="step-num">2</div>
                        <span>Pembayaran</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <span>Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($view === 'cart'): ?>
        <!-- CART VIEW -->
        <section class="checkout-section">
            <div class="container">
                <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <div class="empty-icon">🧳</div>
                    <h3>Keranjang Kosong</h3>
                    <p>Belum ada paket wisata yang dipilih</p>
                    <a href="paket-wisata.html" class="btn-primary">Lihat Paket Wisata</a>
                </div>
                <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <h3 class="cart-title">Paket Dipilih (<?= count($cart) ?>)</h3>
                        <?php foreach ($cart as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-icon">🏔️</div>
                                <div>
                                    <h4><?= htmlspecialchars($item['nama']) ?></h4>
                                    <p class="cart-item-meta">⏱ <?= htmlspecialchars($item['durasi']) ?></p>
                                    <?php if ($item['tanggal']): ?>
                                    <p class="cart-item-meta">📅 <?= htmlspecialchars(date('d M Y', strtotime($item['tanggal']))) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="cart-item-controls">
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="paket_id" value="<?= $item['id'] ?>">
                                    <div class="qty-control">
                                        <label>Peserta:</label>
                                        <input type="number" name="peserta" value="<?= $item['peserta'] ?>" min="1" max="50" onchange="this.form.submit()" class="qty-input">
                                    </div>
                                    <input type="hidden" name="tanggal" value="<?= $item['tanggal'] ?>">
                                </form>
                                <div class="cart-item-price">
                                    Rp <?= number_format($item['harga'] * $item['peserta'], 0, ',', '.') ?>
                                    <small>(Rp <?= number_format($item['harga'], 0, ',', '.') ?>/orang)</small>
                                </div>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="paket_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn-remove">🗑 Hapus</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Summary -->
                    <div class="cart-summary">
                        <h3>Ringkasan Pesanan</h3>
                        <?php foreach ($cart as $item): ?>
                        <div class="summary-row">
                            <span><?= htmlspecialchars($item['nama']) ?> ×<?= $item['peserta'] ?></span>
                            <span>Rp <?= number_format($item['harga'] * $item['peserta'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="summary-row summary-fee">
                            <span>Biaya Admin</span>
                            <span>Rp 0</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <a href="checkout.php?view=payment" class="btn-checkout">
                            Lanjut ke Pembayaran →
                        </a>
                        <a href="paket-wisata.html" class="btn-continue">+ Tambah Paket</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <?php elseif ($view === 'payment'): ?>
        <!-- PAYMENT VIEW -->
        <section class="checkout-section">
            <div class="container">
                <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <h3>Keranjang Kosong</h3>
                    <a href="paket-wisata.html" class="btn-primary">Pilih Paket</a>
                </div>
                <?php else: ?>
                <div class="payment-layout">
                    <div class="payment-methods">
                        <h3>Pilih Metode Pembayaran</h3>

                        <div class="payment-groups">
                            <!-- Transfer Bank -->
                            <div class="payment-group">
                                <h4>🏦 Transfer Bank</h4>
                                <div class="payment-options">
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="bca" checked>
                                        <div class="payment-card">
                                            <div class="payment-logo bank-bca">BCA</div>
                                            <div class="payment-info">
                                                <strong>Bank BCA</strong>
                                                <span>Transfer ke rekening BCA</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="mandiri">
                                        <div class="payment-card">
                                            <div class="payment-logo bank-mandiri">MDR</div>
                                            <div class="payment-info">
                                                <strong>Bank Mandiri</strong>
                                                <span>Transfer ke rekening Mandiri</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="bri">
                                        <div class="payment-card">
                                            <div class="payment-logo bank-bri">BRI</div>
                                            <div class="payment-info">
                                                <strong>Bank BRI</strong>
                                                <span>Transfer ke rekening BRI</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- E-Wallet -->
                            <div class="payment-group">
                                <h4>📱 Dompet Digital</h4>
                                <div class="payment-options">
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="gopay">
                                        <div class="payment-card">
                                            <div class="payment-logo wallet-gopay">GP</div>
                                            <div class="payment-info">
                                                <strong>GoPay</strong>
                                                <span>Bayar dengan GoPay</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="ovo">
                                        <div class="payment-card">
                                            <div class="payment-logo wallet-ovo">OVO</div>
                                            <div class="payment-info">
                                                <strong>OVO</strong>
                                                <span>Bayar dengan OVO</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="dana">
                                        <div class="payment-card">
                                            <div class="payment-logo wallet-dana">DANA</div>
                                            <div class="payment-info">
                                                <strong>DANA</strong>
                                                <span>Bayar dengan DANA</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="qris">
                                        <div class="payment-card">
                                            <div class="payment-logo wallet-qris">QRIS</div>
                                            <div class="payment-info">
                                                <strong>QRIS</strong>
                                                <span>Scan QR Code</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Virtual Account -->
                            <div class="payment-group">
                                <h4>🔢 Virtual Account</h4>
                                <div class="payment-options">
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="va_bni">
                                        <div class="payment-card">
                                            <div class="payment-logo bank-bni">BNI</div>
                                            <div class="payment-info">
                                                <strong>VA Bank BNI</strong>
                                                <span>Virtual Account BNI</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                    <label class="payment-option">
                                        <input type="radio" name="payment" value="alfamart">
                                        <div class="payment-card">
                                            <div class="payment-logo store-alfa">ALFA</div>
                                            <div class="payment-info">
                                                <strong>Alfamart</strong>
                                                <span>Bayar di minimarket</span>
                                            </div>
                                            <div class="payment-check">✓</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="payment-summary">
                        <h3>Detail Pesanan</h3>
                        <?php foreach ($cart as $item): ?>
                        <div class="summary-row">
                            <span><?= htmlspecialchars($item['nama']) ?><br><small><?= $item['peserta'] ?> peserta</small></span>
                            <span>Rp <?= number_format($item['harga'] * $item['peserta'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="summary-total">
                            <span>Total Bayar</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <div class="billing-info">
                            <h4>Informasi Pemesan</h4>
                            <div class="billing-row">
                                <span>Nama</span>
                                <span><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                            </div>
                            <div class="billing-row">
                                <span>Email</span>
                                <span><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                            </div>
                        </div>

                        <button onclick="processPayment()" class="btn-checkout" id="btnPay">
                            💳 Bayar Sekarang
                        </button>
                        <a href="checkout.php" class="btn-continue">← Kembali ke Keranjang</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

    </main>

    <!-- Payment Success Modal -->
    <div class="modal-overlay" id="successModal" style="display:none">
        <div class="modal-card success-modal">
            <div class="success-icon">🎉</div>
            <h2>Pembayaran Berhasil!</h2>
            <p>Terima kasih, pesanan Anda telah dikonfirmasi.</p>
            <div class="order-code">Kode Booking: <strong id="bookingCode"></strong></div>
            <p class="success-note">Tim kami akan menghubungi Anda dalam 1x24 jam untuk konfirmasi jadwal.</p>
            <a href="index.html" class="btn-primary" onclick="clearCart()">Kembali ke Beranda</a>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 Portal Wisata Daerah. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Highlight selected payment
        document.querySelectorAll('input[name="payment"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
                this.closest('.payment-option').querySelector('.payment-card').classList.add('selected');
            });
        });

        // Init first selection
        const firstRadio = document.querySelector('input[name="payment"]:checked');
        if (firstRadio) {
            firstRadio.closest('.payment-option').querySelector('.payment-card').classList.add('selected');
        }

        function generateCode() {
            return 'WD-' + Math.random().toString(36).substr(2,6).toUpperCase() + '-' + Date.now().toString().slice(-4);
        }

        function processPayment() {
            const btn = document.getElementById('btnPay');
            btn.textContent = '⏳ Memproses...';
            btn.disabled = true;
            setTimeout(() => {
                document.getElementById('bookingCode').textContent = generateCode();
                document.getElementById('successModal').style.display = 'flex';
            }, 2000);
        }

        function clearCart() {
            // POST to clear cart
            fetch('clear_cart.php', { method: 'POST' });
        }
    </script>
</body>
</html>
