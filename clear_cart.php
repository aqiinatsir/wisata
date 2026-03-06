<?php
// clear_cart.php - Kosongkan keranjang user (session + database)
require_once 'auth.php';
require_once 'db_functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    clearCart($_SESSION['user_id']);
    $_SESSION['cart'] = [];
}

echo 'ok';
?>
