<?php
// cart.php
session_start(); // Mulai sesi untuk mengakses $_SESSION['cart']

// Koneksi ke database
require_once 'koneksi.php';

// Ambil data keranjang dari sesi
$cart = $_SESSION['cart'] ?? []; // Ambil keranjang, default array kosong jika belum ada

$cartItemsDetails = []; // Array untuk menyimpan detail produk dari DB
$cartTotal = 0; // Total harga seluruh keranjang

// Jika keranjang tidak kosong, ambil detail produk dari database
if (!empty($cart)) {
    // Kumpulkan semua product_id dari keranjang untuk query database
    $productIds = [];
    foreach ($cart as $item) {
        $productIds[] = $item['product_id'];
    }

    // Buat string placeholder untuk query IN (?)
    // Contoh: ?, ?, ? jika ada 3 product_id
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds)); // Semua ID adalah integer 'i'

    // Ambil detail produk utama untuk semua item di keranjang sekaligus
    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    if ($stmt === false) {
         // Log error jika persiapan statement gagal
         error_log("Error preparing products fetch for cart: " . $conn->error);
         // Handle error, mungkin tampilkan pesan ke user
         echo "<p class='alert alert-danger'>Gagal mengambil detail produk dari database.</p>";
         $cart = []; // Kosongkan keranjang di tampilan jika gagal ambil detail
    } else {
        $stmt->bind_param($types, ...$productIds); // Binding parameter
        $stmt->execute();
        $result = $stmt->get_result();

        $productsData = [];
        while ($row = $result->fetch_assoc()) {
            $productsData[$row['id']] = $row; // Simpan data produk dengan ID sebagai kunci
        }
        $stmt->close();

        // Ambil gambar thumbnail pertama untuk setiap produk di keranjang
        // Menggunakan tabel product_images2
         $productImages = [];
         if (!empty($productsData)) {
             $imagePlaceholders = implode(',', array_fill(0, count($productsData), '?'));
             $imageTypes = str_repeat('i', count($productsData));
             // Query hanya mengambil gambar pertama (LIMIT 1) untuk setiap product_id
             $stmt_img = $conn->prepare("SELECT product_id, MIN(image_filename) AS image_filename FROM product_images2 WHERE product_id IN ($imagePlaceholders) GROUP BY product_id"); // GROUP BY atau order by dan limit 1 per id
             if ($stmt_img === false) {
                  error_log("Error preparing image fetch for cart: " . $conn->error);
             } else {
                 $productIdsForImages = array_keys($productsData); // Ambil ID produk yang datanya berhasil diambil
                 $stmt_img->bind_param($imageTypes, ...$productIdsForImages);
                 $stmt_img->execute();
                 $imageResult = $stmt_img->get_result();
                 while($imgRow = $imageResult->fetch_assoc()) {
                     $productImages[$imgRow['product_id']] = $imgRow['image_filename']; // Simpan hanya gambar pertama
                 }
                 $stmt_img->close();
             }
         }


        // Gabungkan data keranjang dengan detail produk dari DB
        foreach ($cart as $cartItemId => $item) {
            $productId = $item['product_id'];
            $quantity = $item['qty'];
            $size = $item['size'];

            // Pastikan detail produk ditemukan di hasil query
            if (isset($productsData[$productId])) {
                $product = $productsData[$productId];
                $price = $product['price'];
                $subtotal = $price * $quantity;
                $cartTotal += $subtotal; // Tambahkan ke total keranjang

                $cartItemsDetails[$cartItemId] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'price' => $price,
                    'size' => $size,
                    'qty' => $quantity,
                    'subtotal' => $subtotal,
                    'image' => $productImages[$productId] ?? 'placeholder.jpg' // Ambil gambar atau placeholder
                ];
            } else {
                 // Handle case where product ID in session doesn't exist in DB
                 error_log("Product ID " . $productId . " in cart session not found in database.");
                 // Anda bisa memilih untuk menghapus item ini dari sesi di sini
                 // unset($_SESSION['cart'][$cartItemId]);
            }
        }
    }
}

// Tutup koneksi database di akhir script
$conn->close();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Rebelstuff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
         .cart-table td {
             vertical-align: middle; /* Rata tengah vertikal di tabel */
         }
         .cart-table .form-control-qty {
             width: 70px; /* Atur lebar input jumlah */
             display: inline-block; /* Agar tidak memenuhi lebar */
         }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">REBELSTUFF</a> <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
             <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                     <a class="nav-link text-white" href="index.php#produk">Koleksi Produk</a>
                </li>
                <li class="nav-item">
                     <a class="nav-link text-white" href="index.php#tentang">Tentang Kami</a>
                </li>
                 <li class="nav-item">
                     <a class="nav-link text-white" href="index.php#alamat">Alamat Toko Kami</a>
                 </li>
                 <li class="nav-item">
                    <a class="nav-link text-white" href="admin.php">Admin</a>
                </li>
                 <li class="nav-item">
                     <a class="nav-link text-white" href="cart.php">
                          <i class="bi bi-cart-fill"></i> Keranjang
                          <?php
                          // Tampilkan jumlah total item di keranjang (jika ada)
                          $totalCartQuantityDisplay = 0;
                          if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                              foreach($_SESSION['cart'] as $item) {
                                  $totalCartQuantityDisplay += $item['qty'];
                              }
                              if ($totalCartQuantityDisplay > 0) {
                                  echo ' (' . $totalCartQuantityDisplay . ')';
                              }
                          }
                          ?>
                     </a>
                 </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Keranjang Belanja Anda</h2>

    <?php if (empty($cartItemsDetails)): ?>
        <div class="alert alert-info text-center" role="alert">
            Keranjang Anda kosong. <a href="index.php#produk">Ayo belanja!</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped cart-table">
                <thead>
                    <tr>
                        <th scope="col"></th> <th scope="col">Produk</th>
                        <th scope="col">Ukuran</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItemsDetails as $cartItemId => $item): ?>
                    <tr>
                        <td>
                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" class="cart-item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>
                            <?php
                            // Tampilkan ukuran, jika ada. Jika tidak ada ukuran, tampilkan pesan
                            echo !empty($item['size']) ? htmlspecialchars($item['size']) : '<span class="text-muted">-</span>';
                            ?>
                        </td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td>
                             <input type="number" class="form-control form-control-sm form-control-qty" value="<?php echo $item['qty']; ?>" min="1">
                             <button class="btn btn-sm btn-outline-secondary mt-1 update-qty-btn" data-cart-item-id="<?php echo htmlspecialchars($cartItemId); ?>">Update</button>
                        </td>
                        <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        <td>
                             <button class="btn btn-sm btn-danger remove-item-btn" data-cart-item-id="<?php echo htmlspecialchars($cartItemId); ?>">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Keranjang</h5>
                        <p>Total: <strong class="text-primary">Rp <?php echo number_format($cartTotal, 0, ',', '.'); ?></strong></p>
                      <a href="checkout.php" class="btn btn-success w-100 mt-3">Lanjutkan ke Checkout</a>

                         <button class="btn btn-danger w-100 mt-2 clear-cart-btn">Hapus Semua Item</button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="home.php#produk" class="btn btn-secondary">Lanjut Belanja</a>
    </div>

</div>


<footer class="text-center text-muted py-4 mt-5 bg-light">
    © <?= date('Y') ?> Rebelstuff. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- JavaScript untuk Fungsionalitas Keranjang (akan diimplementasikan nanti) ---

        // Event listener untuk tombol Update Jumlah (Saat Ini Hanya Placeholder)
        document.querySelectorAll('.update-qty-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartItemId = this.getAttribute('data-cart-item-id');
                const newQtyInput = this.closest('tr').querySelector('.form-control-qty');
                const newQty = parseInt(newQtyInput.value);

                if (newQty > 0) {
                    alert('Simulasi: Update kuantitas untuk item ' + cartItemId + ' menjadi ' + newQty);
                    // >>> Di sini Anda akan menambahkan AJAX call ke PHP untuk update_cart.php <<<
                    // refresh halaman atau perbarui tampilan keranjang setelah sukses
                } else {
                    alert('Jumlah harus lebih dari 0.');
                }
            });
        });

        // Event listener untuk tombol Hapus Item (Saat Ini Hanya Placeholder)
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartItemId = this.getAttribute('data-cart-item-id');
                 if (confirm('Anda yakin ingin menghapus item ini dari keranjang?')) {
                    alert('Simulasi: Hapus item ' + cartItemId);
                    // >>> Di sini Anda akan menambahkan AJAX call ke PHP untuk remove_from_cart.php <<<
                    // refresh halaman atau hapus baris item dari DOM setelah sukses
                 }
            });
        });

         // Event listener untuk tombol Hapus Semua Item (Saat Ini Hanya Placeholder)
        document.querySelectorAll('.clear-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                 if (confirm('Anda yakin ingin mengosongkan seluruh keranjang?')) {
                    alert('Simulasi: Kosongkan keranjang');
                    // >>> Di sini Anda akan menambahkan AJAX call ke PHP untuk clear_cart.php <<<
                    // refresh halaman atau arahkan ke halaman keranjang kosong setelah sukses
                 }
            });
        });

        // --- JavaScript untuk Lanjutkan ke Checkout (Akan diimplementasikan nanti) ---
        // Event listener untuk tombol Lanjutkan ke Checkout (Saat Ini Hanya Placeholder)
        document.querySelectorAll('.btn-success.w-100').forEach(button => {
             button.addEventListener('click', function(e) {
                 // Mencegah aksi default link jika belum diimplementasikan
                 // e.preventDefault();
                 alert('Simulasi: Lanjutkan ke proses checkout.');
                 // >>> Di sini Anda akan mengarahkan pengguna ke halaman checkout atau proses WhatsApp akhir <<<
                 // Jika ke WhatsApp, Anda perlu merangkum SEMUA item di cartItemsDetails menjadi satu pesan panjang
             });
        });


    });
</script>


</body>
</html>