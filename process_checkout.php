<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Keranjang kosong.");
}

// Format pesan
$message = "*Pesanan Baru dari Rebelstuff*\n";
$message .= "Nama: $name\n";
$message .= "No. WhatsApp: $phone\n";
$message .= "Alamat: $address\n\n";
$message .= "*Rincian Pesanan:*\n";

$total = 0;
foreach ($cart as $item) {
    $message .= "- " . $item['qty'] . "x Produk ID " . $item['product_id'];
    $message .= ", Ukuran: " . ($item['size'] ?: '-') . "\n";
    $total += $item['qty']; // Tambahkan logic harga jika dibutuhkan
}

$message .= "\nTotal item: $total\n";
$message .= "*Silakan konfirmasi pesanan ini segera.*";

// Encode ke URL format
$encodedMessage = urlencode($message);

// Redirect ke WhatsApp (ganti 6281234567890 dengan nomor admin)
$whatsappNumber = "6281234567890";
header("Location: https://wa.me/$whatsappNumber?text=$encodedMessage");
exit;
