<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid.']);
    exit();
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Data JSON tidak valid.']);
    exit();
}

$productId = filter_var($data['product_id'] ?? 0, FILTER_VALIDATE_INT);
$quantity = filter_var($data['qty'] ?? 0, FILTER_VALIDATE_INT);
$size = !empty($data['size']) ? htmlspecialchars($data['size']) : null;

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Produk atau Kuantitas tidak valid.']);
    exit();
}

// --- Koneksi Database (Aktifkan biar validasi jalan) ---
$host = 'localhost';
$db = 'rebelstuff'; // pastiin sama kayak home.php lo
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Gagal terhubung ke database.']);
    exit();
}

$stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$productDetails = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$productDetails) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
    exit();
}

// --- Session Keranjang ---
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartItemId = $productId . ($size ? '_' . $size : '');

if (isset($_SESSION['cart'][$cartItemId])) {
    $_SESSION['cart'][$cartItemId]['qty'] += $quantity;
} else {
    $_SESSION['cart'][$cartItemId] = [
        'product_id' => $productId,
        'name'       => $productDetails['name'],
        'price'      => $productDetails['price'],
        'size'       => $size,
        'qty'        => $quantity
    ];
}

$totalItemsInCart = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItemsInCart += $item['qty'];
}

echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil ditambahkan ke keranjang!',
    'cart_item_count' => count($_SESSION['cart']),
    'cart_total_quantity' => $totalItemsInCart,
    'cart_items' => $_SESSION['cart']
]);
