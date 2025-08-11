<?php
session_start();

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Rebelstuff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Checkout</h2>
    <form action="process_checkout.php" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Nomor WhatsApp</label>
            <input type="text" name="phone" id="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat Lengkap</label>
            <textarea name="address" id="address" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Konfirmasi dan Kirim Pesanan</button>
    </form>

    <div class="mt-4">
        <a href="cart.php" class="btn btn-secondary">Kembali ke Keranjang</a>
    </div>
</div>
</body>
</html>
