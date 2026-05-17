<?php
// Mulai sesi jika belum dimulai
session_start();

require_once 'koneksi.php';

$message = '';

// Memastikan bahwa pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Arahkan ke login jika tidak login
    exit();
}

// --- Fungsi Helper untuk Gambar (Tetap menggunakan product_images2) ---
// Fungsi untuk mengunggah banyak gambar
function uploadImages($files, $productId, $conn) {
    $targetDir = "uploads/";
    $uploadedImages = [];

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileNames = $files['name'];
    $tmpNames = $files['tmp_name'];
    $errors = $files['error'];
    $fileCounts = count($fileNames);

    $stmt_insert_image = null;

    // *** Pastikan nama tabel gambar sesuai, contoh: product_images2 ***
    $stmt_insert_image = $conn->prepare("INSERT INTO product_images2 (product_id, image_filename) VALUES (?, ?)");
    if ($stmt_insert_image === false) {
        error_log("Error preparing INSERT statement for images: " . $conn->error);
        return false;
    }

    for ($i = 0; $i < $fileCounts; $i++) {
        $fileName = basename($fileNames[$i]);
        $tmpName = $tmpNames[$i];
        $error = $errors[$i];

        if ($error != UPLOAD_ERR_OK || empty($fileName)) {
            if ($error != UPLOAD_ERR_NO_FILE) {
                 error_log("File upload error for index " . $i . ": Code " . $error . " Filename: " . $fileName);
            }
            continue;
        }

        $targetFile = $targetDir . $fileName;
        $uploadOk = true;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $check = getimagesize($tmpName);
        if ($check === false) {
             error_log("File is not a valid image: " . $fileName);
             $uploadOk = false;
        }

        // Izinkan format file tertentu
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
             error_log("Sorry, only JPG, JPEG, PNG & GIF files are allowed: " . $fileName);
             $uploadOk = false;
        }

        if ($uploadOk == false) {
             error_log("Image upload failed validation for file: " . $fileName);
        } else {
            if (move_uploaded_file($tmpName, $targetFile)) {
                $stmt_insert_image->bind_param("is", $productId, $fileName);
                if ($stmt_insert_image->execute()) {
                     $uploadedImages[] = $fileName;
                     error_log("Successfully saved image info to DB: " . $fileName);
                } else {
                    unlink($targetFile);
                    error_log("Failed to save image info to database: " . $stmt_insert_image->error . " for file " . $fileName);
                }
            } else {
                error_log("Failed to move uploaded file to server: " . $fileName . " Error: " . error_get_last()['message'] ?? 'Unknown error');
            }
        }
    }
     if ($stmt_insert_image) $stmt_insert_image->close();
    return $uploadedImages;
}

// Fungsi untuk mendapatkan gambar produk
function getProductImages($productId, $conn) {
    $images = [];
    // *** Pastikan nama tabel gambar sesuai, contoh: product_images2 ***
    $stmt = $conn->prepare("SELECT image_filename FROM product_images2 WHERE product_id = ?");
    if ($stmt === false) {
         error_log("Error preparing getProductImages statement: " . $conn->error);
         return $images;
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row['image_filename'];
    }
    $stmt->close();
    return $images;
}

// Fungsi untuk menghapus gambar produk (file fisik dan DB)
function deleteProductImages($productId, $conn) {
    $images = getProductImages($productId, $conn); // Get filenames first

    $targetDir = "uploads/";
    foreach ($images as $imageName) {
        $filePath = $targetDir . $imageName;
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                error_log("Successfully deleted physical image file: " . $filePath);
            } else {
                error_log("Failed to delete physical image file: " . $filePath);
            }
        } else {
             error_log("Image file not found to delete: " . $filePath);
        }
    }

    // Delete from database
    // *** Pastikan nama tabel gambar sesuai, contoh: product_images2 ***
    $stmt = $conn->prepare("DELETE FROM product_images2 WHERE product_id = ?");
     if ($stmt === false) {
         error_log("Error preparing deleteProductImages statement: " . $conn->error);
         return false;
    }
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        error_log("Successfully deleted image records for product ID: " . $productId);
        return true;
    } else {
        error_log("Error deleting image records for product ID: " . $productId . " Error: " . $stmt->error);
        return false;
    }
    $stmt->close();
}
// --- End Fungsi Helper untuk Gambar ---


// --- Tidak Ada Fungsi Helper untuk Varian (Dihapus) ---
// Fungsi getProductVariants dan saveProductVariants Dihapus
// --- End Tidak Ada Fungsi Helper untuk Varian ---


// Handle product deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        // Hapus gambar terkait (file dan DB)
        deleteProductImages($id, $conn); // <-- Tetap menggunakan fungsi helper gambar

        // *** Tidak perlu menghapus varian dari tabel product_variants lagi ***
        // *** Logika penghapusan varian Dihapus di sini ***

        // Hapus produk dari tabel products
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt === false) {
             error_log("Error preparing DELETE product statement: " . $conn->error);
             throw new Exception("Terjadi kesalahan internal saat menyiapkan hapus produk.");
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Produk berhasil dihapus!";
        } else {
            throw new Exception("Gagal menghapus produk dari tabel produk: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $message = "Gagal menghapus produk: " . $e->getMessage();
         error_log("Error deleting product ID " . $id . ": " . $e->getMessage());
    }

    // Redirect setelah delete
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
    exit();
}

// Handle product creation/update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("--- Starting POST handling ---"); // Add entry log

    // Ambil data produk utama dari POST
    $name = $_POST['name'] ?? '';
    $price = intval($_POST['price'] ?? 0);
    $description = $_POST['description'] ?? '';
    $ukuran = $_POST['ukuran'] ?? ''; // <<< Ambil nilai ukuran dari input teks
    $bahan = $_POST['bahan'] ?? '';
    $desain = $_POST['desain'] ?? '';

    // *** Tidak perlu memproses $_POST['variants'] lagi ***
    // $submittedVariants = $_POST['variants'] ?? []; // Dihapus


    error_log("Received POST data for product. Name: " . $name . ", Ukuran: " . $ukuran);
    error_log("Received POST data. Files: " . print_r($_FILES, true));


    $productId = null;
    $isUpdate = isset($_POST['update']);

    // --- Validasi Input Dasar ---
    if (empty($name) || $price < 0) {
        $message = "Nama produk dan Harga harus diisi dan harga tidak boleh negatif.";
         error_log("Validation failed: Name or Price missing/invalid.");
         header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
         exit();
    }
    // --- End Validasi Input Dasar ---


    // --- Proses INSERT atau UPDATE Produk Utama ---
    if ($isUpdate) {
        $productId = intval($_POST['id'] ?? 0);
         if ($productId <= 0) {
             $message = "ID Produk tidak valid untuk update.";
             error_log("Validation failed: Invalid product ID for update: " . ($_POST['id'] ?? 'N/A'));
             header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
             exit();
         }
        // Update produk utama - <<< Tambahkan kolom 'ukuran' kembali ke query UPDATE
        $stmt_product = $conn->prepare("UPDATE products SET name=?, price=?, description=?, ukuran=?, bahan=?, desain=? WHERE id=?");
         if ($stmt_product === false) {
             error_log("Error preparing UPDATE product statement: " . $conn->error);
             $message = "Terjadi kesalahan internal saat menyiapkan update produk.";
              header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
              exit();
         }
        $stmt_product->bind_param("sissssi", $name, $price, $description, $ukuran, $bahan, $desain, $productId); // <<< Bind nilai ukuran

        if ($stmt_product->execute()) {
             $message = "Produk berhasil diperbarui!";
             error_log("Product ID " . $productId . " updated successfully.");
        } else {
            $message = "Gagal memperbarui produk: " . $stmt_product->error;
            error_log("Error updating product ID " . $productId . ": " . $stmt_product->error);
             header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
             exit();
        }
        $stmt_product->close();

    } else { // Create new product
        // Insert produk utama - <<< Tambahkan kolom 'ukuran' kembali ke query INSERT
        $stmt_product = $conn->prepare("INSERT INTO products (name, price, description, ukuran, bahan, desain) VALUES (?, ?, ?, ?, ?, ?)");
         if ($stmt_product === false) {
             error_log("Error preparing INSERT product statement: " . $conn->error);
             $message = "Terjadi kesalahan internal saat menyiapkan tambah produk.";
             header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
             exit();
         }
        $stmt_product->bind_param("sissss", $name, $price, $description, $ukuran, $bahan, $desain); // <<< Bind nilai ukuran

        if ($stmt_product->execute()) {
            $productId = $conn->insert_id; // Ambil ID produk yang baru dibuat
            $message = "Produk berhasil ditambahkan!";
             error_log("New product created with ID: " . $productId);
        } else {
            $message = "Gagal menambahkan produk: " . $stmt_product->error;
            error_log("Error creating product: " . $stmt_product->error);
             header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
             exit();
        }
        $stmt_product->close();
    }
    // --- End Proses INSERT atau UPDATE Produk Utama ---


    // Jika produk utama berhasil disimpan (baik update atau create), lanjutkan simpan gambar
    if ($productId > 0) { // Pastikan productId valid
        // *** Tidak perlu memanggil saveProductVariants lagi ***
        // saveProductVariants($productId, $submittedVariants, $conn); // Dihapus

        // Unggah gambar baru
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            if ($_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                 error_log("Processing image uploads for product ID: " . $productId);
                 if (uploadImages($_FILES['images'], $productId, $conn)) {
                      // Gambar berhasil diunggah (logging ada di dalam fungsi)
                 } else {
                      $message .= " (Gagal mengunggah gambar, cek log)";
                 }
            } else {
                 error_log("Images input used, but no file selected (UPLOAD_ERR_NO_FILE).");
            }

        } else {
            error_log("Images file input not set in POST or was empty.");
        }

    } else {
         error_log("Product ID not available after POST process, cannot save images.");
         $message = "Terjadi kesalahan fatal saat menyimpan produk (ID produk hilang).";
    }

    error_log("--- Finished POST handling, redirecting ---");
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
    exit();
}

// Handle message display after redirects
if (isset($_GET['message'])) {
    $message = htmlspecialchars(urldecode($_GET['message']));
}


// --- Mengambil Data untuk Tampilan ---

// Get all products for listing
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id DESC"); // Ordering by ID DESC might be better for admin
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} else {
     error_log("Error fetching all products: " . $conn->error);
}


// Fetch all images and organize them by product ID
// *** Pastikan nama tabel gambar sesuai, contoh: product_images2 ***
$allProductImages = [];
$imageResult = $conn->query("SELECT product_id, image_filename FROM product_images2 ORDER BY product_id");
if ($imageResult) {
    while ($row = $imageResult->fetch_assoc()) {
        $allProductImages[$row['product_id']][] = $row['image_filename'];
    }
    $imageResult->free();
} else {
    error_log("Error fetching all product images: " . $conn->error);
}


// *** Tidak perlu mengambil varian dari tabel product_variants lagi ***
// $allProductVariants = []; // Dihapus
// $variantResult = $conn->query("SELECT product_id, size_name, stock FROM product_variants ORDER BY product_id, size_name"); // Dihapus
// ... logika pengambilan varian dihapus ...


// Get product data and its images for editing
$editProduct = null;
$editProductImages = []; // Gambar untuk edit form
// *** Tidak perlu mengambil varian untuk edit form lagi ***
// $editProductVariants = []; // Dihapus


if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);

    // Ambil data produk utama
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
     if ($stmt === false) {
         error_log("Error preparing editProduct statement: " . $conn->error);
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $editProduct = $result->fetch_assoc();
        $stmt->close();
    }


    if ($editProduct) {
        // Ambil gambar spesifik untuk produk ini
        // *** Pastikan nama tabel gambar sesuai ***
        $editProductImages = getProductImages($id, $conn);

        // *** Tidak perlu mengambil varian spesifik untuk produk ini lagi ***
        // $editProductVariants = getProductVariants($id, $conn); // Dihapus
    } else {
        // Product with this ID not found, redirect back to list
         header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode("Produk dengan ID tersebut tidak ditemukan."));
         exit();
    }
}

// Handle cancel edit
if (isset($_GET['cancel'])) {
     header("Location: " . $_SERVER['PHP_SELF']);
     exit();
}

// Tutup koneksi database di akhir script
if ($conn) {
    $conn->close();
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Rebelstuff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 1.2rem;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            margin-bottom: 2rem;
        }
        .form-control {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
            border-color: #6c757d;
        }
        .form-label {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .btn-primary {
            background-color: #343a40;
            border: none;
            border-radius: 0.5rem;
            padding: 10px 20px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #495057;
        }
        .btn-primary:active {
            background-color: #212529;
        }
        .btn-logout {
            background-color: #ffc107;
            border: none;
            border-radius: 0.5rem;
            padding: 10px 20px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
             color: #333;
        }
         .btn-logout:hover {
            background-color: #e0a800;
         }
         .btn-logout:active {
            background-color: #d39e00;
         }

        .alert {
             background-color: #28a745;
             color: #fff;
             border-radius: 1rem;
             font-size: 1rem;
             margin-bottom: 1.5rem;
        }
        .footer {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-control-file {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            width: 100%;
        }
        .card-body {
            padding: 2rem;
        }
        .custom-shadow {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        .product-image-thumb {
            max-width: 80px;
            height: auto;
            border-radius: 0.5rem;
            margin-right: 5px;
             margin-bottom: 5px;
             border: 1px solid #ccc;
             padding: 3px;
        }
         .current-images-container img {
             max-width: 100px;
             height: auto;
             margin-right: 10px;
             border-radius: 0.5rem;
              border: 1px solid #ccc;
              padding: 3px;
         }
        .table-responsive {
            overflow-x: auto;
        }
        .btn-edit {
            background-color: #2596be;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-action {
            margin: 0 3px;
            border-radius: 0.5rem;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
         .table-product-images {
             display: flex;
             flex-wrap: wrap;
             gap: 5px;
         }
         /* Style untuk manajemen varian dihapus */

    </style>
</head>
<body>
<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0"><?php echo isset($editProduct) ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h2>
        <div>
             <?php if (isset($editProduct)): ?>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-cancel me-2">Batal Edit</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>

    <div class="card custom-shadow">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <?php if (isset($editProduct)): ?>
                    <input type="hidden" name="update" value="1">
                    <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name" class="form-label">Nama Produk</label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="<?php echo isset($editProduct) ? htmlspecialchars($editProduct['name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" id="price" class="form-control"
                           value="<?php echo isset($editProduct) ? $editProduct['price'] : ''; ?>" required min="0">
                </div>


                <div class="form-group">                           
                    <label for="description" class="form-label">Deskripsi Singkat</label>
                    <textarea name="description" id="description" class="form-control" rows="3">
                        <?php echo isset($editProduct) ? htmlspecialchars($editProduct['description']) : '';
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="ukuran" class="form-label">Ukuran (Pisahkan dengan koma, contoh: S, M, L)</label>
                    <input type="text" name="ukuran" id="ukuran" class="form-control"
                           value="<?php echo isset($editProduct) ? htmlspecialchars($editProduct['ukuran']) : ''; ?>"
                           placeholder="Contoh: S, M, L, XL atau All Size">
                </div>
                <div class="form-group">
                    <label for="bahan" class="form-label">Bahan</label>
                    <textarea name="bahan" id="bahan" class="form-control" rows="2"
                             placeholder="Contoh: Cotton combed 30s"><?php
                         echo isset($editProduct) ? htmlspecialchars($editProduct['bahan']) : '';
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="desain" class="form-label">Penjelasan Desain</label>
                    <textarea name="desain" id="desain" class="form-control" rows="3"><?php
                         echo isset($editProduct) ? htmlspecialchars($editProduct['desain']) : '';
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="images" class="form-label">Unggah Gambar (Pilih banyak file)</label>
                    <input type="file" name="images[]" id="images" class="form-control-file" accept="image/*" multiple>
                    <small class="form-text text-muted">Pilih satu atau lebih file gambar. Mengunggah file baru akan menambahkannya.</small>

                    <?php if (isset($editProduct) && !empty($editProductImages)): ?>
                        <div class="mt-3 current-images-container">
                            <label class="form-label">Gambar Saat Ini:</label><br>
                            <?php foreach ($editProductImages as $imageName): ?>
                                <img src="uploads/<?php echo htmlspecialchars($imageName); ?>"
                                     alt="Gambar Produk" class="product-image-thumb">
                            <?php endforeach; ?>
                             </div>
                    <?php elseif (isset($editProduct)): ?>
                         <div class="mt-2"><small class="text-muted">Belum ada gambar untuk produk ini.</small></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo isset($editProduct) ? 'Update Produk' : 'Tambah Produk'; ?>
                </button>
            </form>
        </div>
    </div>

    <h2 class="section-title">Daftar Produk</h2>

    <div class="card custom-shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Ukuran</th> <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                 <div class="table-product-images">
                                <?php
                                $productImages = $allProductImages[$product['id']] ?? []; // Get images for this product
                                if (!empty($productImages)):
                                    foreach ($productImages as $imageName): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($imageName); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-image-thumb">
                                    <?php endforeach;
                                else: ?>
                                        <span class="text-muted">No image</span>
                                <?php endif; ?>
                                 </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                             <td>
                                <?php
                                // Menampilkan nilai ukuran dari kolom tunggal
                                if (!empty($product['ukuran'])) {
                                    echo nl2br(htmlspecialchars($product['ukuran'])); // Gunakan nl2br jika ingin koma di baris baru
                                } else {
                                    echo '<span class="text-muted">Tidak ada ukuran</span>';
                                }
                                ?>
                            </td>
                             <td>
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-edit btn-action">Edit</a>
                                <a href="?delete=<?php echo $product['id']; ?>"
                                   class="btn btn-delete btn-action"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini dan SEMUA gambarnya?')"
                                   >Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>