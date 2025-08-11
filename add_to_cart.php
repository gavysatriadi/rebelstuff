<?php
// add_to_cart.php

session_start(); // Mulai sesi PHP

header('Content-Type: application/json'); // Beri tahu browser bahwa respons adalah JSON

// Pastikan permintaan datang melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid.']);
    exit();
}

// Ambil data JSON dari body permintaan
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); // Decode data JSON menjadi array asosiatif

// Cek apakah data berhasil didecode dan tidak kosong
if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Data JSON tidak valid.']);
    exit();
}

// --- Validasi dan Ambil Data Produk dari Permintaan ---
$productId = filter_var($data['product_id'] ?? 0, FILTER_VALIDATE_INT);
$quantity = filter_var($data['qty'] ?? 0, FILTER_VALIDATE_INT);
// Ukuran bisa berupa string kosong jika tidak ada dropdown atau tidak dipilih
$size = htmlspecialchars($data['size'] ?? '');

// Validasi dasar
if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Produk atau Kuantitas tidak valid.']);
    exit();
}

// --- Koneksi ke Database untuk Validasi Produk (Opsional tapi disarankan) ---
// Disarankan untuk memverifikasi bahwa product_id benar-benar ada di database
// dan mengambil nama/harga dari DB daripada mengandalkan data dari client side
// (Ini demi keamanan dan konsistensi)

// $host = 'localhost';
// $db = 'rebelstuff';
// $user = 'root';
// $pass = '';
// $conn = new mysqli($host, $user, $pass, $db);
// if ($conn->connect_error) {
//     // Log error koneksi database jika perlu
//     echo json_encode(['success' => false, 'message' => 'Gagal terhubung ke database.']);
//     exit();
// }

// $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
// $stmt->bind_param("i", $productId);
// $stmt->execute();
// $result = $stmt->get_result();
// $productDetails = $result->fetch_assoc();
// $stmt->close();
// $conn->close();

// if (!$productDetails) {
//     echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
//     exit();
// }

// --- Mengelola Keranjang Menggunakan Session ---

// Pastikan array keranjang ada di sesi
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Buat kunci unik untuk item keranjang (misal: IDProduk_Ukuran)
// Jika tidak ada ukuran, gunakan hanya ID Produk
$cartItemId = $productId . ($size ? '_' . $size : '');

// Cek apakah item (dengan ukuran spesifik) sudah ada di keranjang
if (isset($_SESSION['cart'][$cartItemId])) {
    // Jika sudah ada, tambahkan kuantitasnya
    $_SESSION['cart'][$cartItemId]['qty'] += $quantity;
} else {
    // Jika belum ada, tambahkan item baru ke keranjang
    $_SESSION['cart'][$cartItemId] = [
        'product_id' => $productId,
        'size' => $size,
        'qty' => $quantity,
        // Disarankan menyimpan nama dan harga juga dari DB setelah validasi di atas
        // 'name' => $productDetails['name'],
        // 'price' => $productDetails['price']
    ];
}

// Hitung jumlah total item di keranjang (untuk feedback ke frontend)
$totalItemsInCart = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItemsInCart += $item['qty'];
}

// Beri respons sukses ke frontend
echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil ditambahkan ke keranjang!',
    'cart_item_count' => count($_SESSION['cart']), // Jumlah item unik
    'cart_total_quantity' => $totalItemsInCart // Jumlah total kuantitas
]);

?>