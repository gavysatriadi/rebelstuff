<?php
// index.php (atau home.php)

// Koneksi ke database
$host = 'localhost';
$db = 'rebelstuff';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// --- Mengambil Data Produk ---
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result) {
     $products = $result->fetch_all(MYSQLI_ASSOC);
     $result->free();
}

// --- Mengambil Semua Data Gambar dan Mengorganisir per Produk ---
// Menggunakan tabel product_images2 sesuai kode admin terakhir Anda
$allProductImages = [];
$imageResult = $conn->query("SELECT product_id, image_filename FROM product_images2 ORDER BY product_id"); // Ambil semua gambar
if ($imageResult) {
    while ($imgRow = $imageResult->fetch_assoc()) {
        $allProductImages[$imgRow['product_id']][] = $imgRow['image_filename']; // Kelompokkan berdasarkan product_id
    }
    $imageResult->free();
}

// Tutup koneksi database jika tidak digunakan lagi setelah pengambilan data
// $conn->close(); // Mungkin perlu dibiarkan terbuka jika ada operasi lain setelah ini

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rebelstuff - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">


    <style>
        /* Styles for the main Hero Carousel */
        .carousel-image {
            height: 90vh; /* Set to 50% of the viewport height */
            background-size: cover;
            background-position: center;
        }
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5);
            padding: 1.5rem;
            border-radius: 10px;
        }

        /* Styles for Product Cards */
        .product-card {
             height: 100%; /* Make cards fill equal height */
        }
        /* Style for images inside the product card carousel */
        .product-card .carousel-item img {
            height: 200px; /* Fixed height for product thumbnail */
            object-fit: cover; /* Ensure image covers the area without distortion */
            cursor: pointer;
        }
         /* Style for placeholder image in product card */
         .product-card img.card-img-top {
             height: 200px;
             object-fit: cover;
         }
         /* Add margin bottom to card-body if carousel is above it */
         .product-card .carousel + .card-body {
             margin-top: 10px; /* Space between carousel and card body */
         }


        /* Style for truncating product description text */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Limit to 2 lines */
            -webkit-box-orient: vertical;
        }

        /* Styles for images inside the Modal Carousel */
        .modal-body .carousel-item img {
            max-height: 400px; /* Max height for modal image */
            object-fit: contain; /* Contain image within area */
        }

        /* Style for disabled WhatsApp button */
        .btn-success.disabled,
        .btn-secondary.disabled { /* Juga terapkan ke tombol secondary (Add to cart) */
            pointer-events: none; /* Disable click events */
            opacity: 0.65; /* Indicate disabled state */
        }

        /* Style for icons inside buttons */
        .btn i {
            margin-right: 5px; /* Add space between icon and text */
        }

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">REBELSTUFF</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-white" href="#produk">Koleksi Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#tentang">Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#alamat">Alamat Toko Kami</a>
                </li>
                <li class="nav-item">
                     <a class="nav-link text-white" href="login.php">Admin</a>
                    </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="cart.php">
                        <i class="bi bi-cart-fill"></i> Keranjang
                        <?php
                        // Menampilkan jumlah item dalam keranjang
                        $totalCartQuantityDisplay = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                if (isset($item['qty']) && is_numeric($item['qty'])) {
                                    $totalCartQuantityDisplay += $item['qty'];
                                }
                            }
                        }
                        if ($totalCartQuantityDisplay > 0) {
                            echo ' (' . $totalCartQuantityDisplay . ')';
                        }
                        ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="aboutCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="carousel-image" style="background-image: url('2.jpeg');"></div>
      <div class="carousel-caption d-none d-md-block">
        <h3>Tentang Rebelstuff</h3>
        <p>Brand lokal streetwear dengan desain orisinal & kualitas terbaik.</p>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-image" style="background-image: url('3.jpeg');"></div>
      <div class="carousel-caption d-none d-md-block">
        <h3>Filosofi Desain</h3>
        <p>Desain kami membawa pesan kebebasan berekspresi dan gaya hidup urban. Desain Rebelstuff Project menggabungkan elemen-elemen estetika yang berani dengan filosofi yang mendalam.
        Setiap desain tidak hanya berfokus pada tampilan visual, tetapi juga membawa pesan yang kuat tentang pemberontakan, kebebasan, dan ekspresi diri. Dengan sentuhan modern, desain ini
        memadukan tren kontemporer dan inovasi, menciptakan karya yang relevan dengan gaya hidup generasi saat ini yang ingin tampil berbeda dan autentik.</p>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-image" style="background-image: url('4.jpg');"></div>
      <div class="carousel-caption d-none d-md-block">
        <h3>Komitmen Kualitas</h3>
        <p>Rebelstuff Project berkomitmen untuk selalu menghadirkan produk dengan kualitas terbaik, 
            menggunakan bahan-bahan unggulan yang tidak hanya tahan lama tetapi juga nyaman digunakan.
            Kami percaya bahwa kualitas adalah bagian dari setiap langkah kreatif, oleh karena itu setiap item yang kami buat dipilih dengan cermat menggunakan material yang premium dan ramah di kulit.
            Kami memastikan setiap jahitan, detail, dan tekstur produk kami memberikan kenyamanan ekstra untuk pemakainya, agar Anda dapat tampil penuh percaya diri tanpa mengorbankan kenyamanan. Dengan perpaduan 
            antara desain berani dan kualitas bahan yang superior, Rebelstuff hadir untuk mendukung gaya hidup bebas, kreatif, dan penuh makna</p>
        </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<section class="bg-light py-5" id="tentang">
    <div class="container" data-aos="fade-up">
        <!-- Gambar di atas teks -->
        <div class="text-center mb-4">
            <img src="11.jpg" alt="Rebelstuff Logo" class="img-fluid" style="max-width: 600px;">
        </div>
        
        <h2 class="text-center mb-4">Tentang Kami</h2>
        <p class="text-center mx-auto" style="max-width: 1000px;">
            Rebelstuff adalah brand streetwear lokal yang berdiri sejak tahun 2019 di Kota Depok.
            Rebelstuff Project adalah brand yang mengusung semangat pemberontakan, kreativitas tanpa batas, dan autentisitas. 
            Dengan fokus pada ekspresi diri yang bebas, brand ini mengajak individu untuk berani berpikir dan bertindak
            secara kreatif. Selain itu, Rebelstuff Project juga bisa mencerminkan upaya untuk menciptakan perubahan sosial atau budaya
            melalui karya-karya kreatif, menginspirasi orang untuk menjalani hidup dengan lebih berani dan tanpa takut pada penilaian orang lain.
            Kami berkomitmen menghadirkan produk fashion berkualitas tinggi dengan desain orisinal
            yang merepresentasikan semangat kebebasan dan gaya hidup urban anak muda Indonesia. 
            Kami menggunakan bahan-bahan berkualitas tinggi untuk memastikan produk kami tahan lama dan nyaman digunakan.
        </p>
    </div>
</section>




<section class="py-5" id="alamat">
    <div class="container" data-aos="fade-up"> <h2 class="text-center mb-4">Alamat Toko Kami</h2>
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <p><strong>Rebelstuff Store</strong></p>
                <p>Kp. Pitara Rangkapanjaya Kota Depok</p>
                <p>Jam Operasional: Senin - Sabtu, 10:00 - 21:00 WIB</p>
                <p>No. Telepon: 087874872257</p>
                 <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3944.6628362578583!2d106.85926701535618!3d-6.232786795906335!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a1df6dbfabb57%3A0x6ad40263f343e4b4!2sTaja%20Kopi!5e0!3m2!1sen!2sid!4v1637316279929!5m2!1sen!2sid"
                    width="100%"
                    height="300"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
                 <small class="d-block text-muted mt-2">Ganti URL embed map di atas dengan lokasi toko Anda yang sebenarnya dari Google Maps Embed API.</small>
            </div>
        </div>
    </div>
</section>


<div class="container mt-5" id="produk">
    <h2 class="mb-4 text-center" data-aos="fade-up">Koleksi Produk Kami</h2> <div class="row g-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
                 <?php
                 // --- Data untuk Card & Modal ---
                 $productId = $row['id'];
                 $productName = htmlspecialchars($row['name']);
                 $productPrice = $row['price'];
                 $productDescription = htmlspecialchars($row['description']);
                 $productUkuranString = $row['ukuran']; // Ambil string ukuran dari DB
                 $productBahan = htmlspecialchars($row['bahan']);
                 $productDesain = htmlspecialchars($row['desain']);


                 // Dapatkan array filename untuk produk ini
                 $productImages = $allProductImages[$productId] ?? [];

                 // ID unik untuk carousel di kartu dan modal
                 $cardCarouselId = 'cardCarousel-' . $productId;
                 $modalCarouselId = 'modalCarousel-' . $productId;

                 // --- Pecah string ukuran menjadi array untuk dropdown di Modal ---
                 $availableSizes = [];
                 if (!empty($productUkuranString)) {
                     $sizesArray = explode(',', $productUkuranString);
                     foreach ($sizesArray as $size) {
                         $trimmedSize = trim($size);
                         if (!empty($trimmedSize)) {
                             $availableSizes[] = $trimmedSize;
                         }
                     }
                 }
                 // --- End Pecah string ukuran ---
                 ?>

                <div class="col-sm-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo ($productId % 4) * 100; ?>"> <div class="card product-card h-100 shadow-sm">

                        <?php if (!empty($productImages)): ?>
                            <div id="<?php echo $cardCarouselId; ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php $isActive = true; ?>
                                    <?php foreach ($productImages as $imageFilename): ?>
                                        <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                                            <img src="uploads/<?php echo htmlspecialchars($imageFilename); ?>"
                                                 class="d-block w-100"
                                                 alt="<?php echo $productName; ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        </div>
                                        <?php $isActive = false; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($productImages) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $cardCarouselId; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $cardCarouselId; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                             <img src="uploads/placeholder.jpg" class="card-img-top" alt="Tidak ada gambar" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                       <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo $productName; ?></h5>
    
                <p class="card-text text-primary fw-semibold">
                    Rp <span class="product-price-value"><?php echo number_format($productPrice, 0, ',', '.'); ?></span>
                </p>

                <?php if (!empty($productUkuranString)): ?>
                    <p class="card-text text-muted">Ukuran: <?php echo htmlspecialchars($productUkuranString); ?></p>
                <?php endif; ?>

                <p class="card-text text-muted truncate"><?php echo $productDescription; ?></p>

                <button
                    class="btn btn-sm btn-success mt-auto"
                data-bs-toggle="modal"
             data-bs-target="#orderModal<?php echo $productId; ?>">
             <i class="bi bi-cart"></i> Pesan Sekarang
             </button>
                </div>
                    </div>
                </div>
                <div class="modal fade" id="orderModal<?php echo $productId; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $productId; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-dark text-white">
                                <h5 class="modal-title" id="orderModalLabel<?php echo $productId; ?>">Detail Produk: <?php echo $productName; ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                     <div class="col-md-5">
                                        <?php if (!empty($productImages)): ?>
                                             <div id="<?php echo $modalCarouselId; ?>" class="carousel slide" data-bs-ride="carousel">
                                                 <div class="carousel-inner">
                                                     <?php $isActive = true; ?>
                                                     <?php foreach ($productImages as $imageFilename): ?>
                                                         <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                                                             <img src="uploads/<?php echo htmlspecialchars($imageFilename); ?>"
                                                                  class="d-block w-100"
                                                                  alt="<?php echo $productName; ?>" style="max-height: 300px; object-fit: contain;">
                                                         </div>
                                                         <?php $isActive = false; ?>
                                                     <?php endforeach; ?>
                                                 </div>
                                                 <?php if (count($productImages) > 1): ?>
                                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $modalCarouselId; ?>" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $modalCarouselId; ?>" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </button>
                                                <?php endif; ?>
                                             </div>
                                         <?php else: ?>
                                              <img src="uploads/placeholder.jpg" class="img-fluid" alt="Tidak ada gambar" style="max-height: 300px; object-fit: contain;">
                                         <?php endif; ?>
                                     </div>
                                    <div class="col-md-7">
                                        <h5><?php echo $productName; ?></h5>
                                         <span class="product-name" data-name="<?php echo $productName; ?>" style="display:none;"></span>
                                         <span class="product-id" data-id="<?php echo $productId; ?>" style="display:none;"></span>


                                        <p><strong>Harga:</strong> <span class="text-primary fw-semibold">Rp <span class="modal-product-price-value"><?php echo number_format($productPrice, 0, ',', '.'); ?></span></span></p>
                                         <span class="modal-product-price-raw" data-price="<?php echo $productPrice; ?>" style="display:none;"></span>
                                        
                                        <p><strong>Bahan:</strong> <?php echo nl2br($productBahan); ?></p>
                                        <p><strong>Deskripsi Singkat:</strong> <?php echo nl2br($productDescription); ?></p>
                                         <p><strong>Penjelasan Desain:</strong> <?php echo nl2br($productDesain); ?></p>

                                        <?php if (!empty($availableSizes)): ?>
                                            <div class="mb-3">
                                                <label for="size-<?php echo $productId; ?>" class="form-label">Ukuran</label>
                                                <select id="size-<?php echo $productId; ?>" class="form-select modal-size-select" required>
                                                    <option value="" selected disabled>-- Pilih Ukuran --</option>
                                                    <?php foreach ($availableSizes as $size): ?>
                                                        <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php else: ?>
                                            <p><strong>Ukuran:</strong> <span class="text-muted">Tidak ada info ukuran tersedia</span></p>
                                            <?php endif; ?>

                                        <div class="mb-3">
                                             <label for="qty-<?php echo $productId; ?>" class="form-label">Jumlah</label>
                                             <input type="number" class="form-control modal-qty" id="qty-<?php echo $productId; ?>" min="1" value="1">
                                         </div>                                              

                                        <p class="fw-bold">Total: Rp <span class="modal-total-price"><?php echo number_format($productPrice, 0, ',', '.'); ?></span></p>

                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-secondary modal-add-to-cart-btn"
                                                    <?php if(empty($availableSizes)) echo 'disabled'; // Disable if no sizes available ?>>
                                                <i class="bi bi-cart-plus"></i> Tambahkan ke Keranjang
                                            </button>

                                            <a href="#" target="_blank" class="btn btn-success modal-wa-link disabled flex-grow-1">
                                                 <?php echo !empty($availableSizes) ? 'Pilih Ukuran Dulu' : 'Tidak Ada Ukuran Tersedia'; ?>
                                            </a>
                                        </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted">Belum ada produk yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
      duration: 800, // animation duration
      once: true // whether animation should only happen once while scrolling down
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const whatsappNumber = '6287874872257'; // Ganti dengan nomor WhatsApp Anda

    // Add event listener to all modals
    const orderModals = document.querySelectorAll('.modal.fade');

    orderModals.forEach(modal => {
        // Get elements specific to THIS modal instance (use querySelector within the modal)
        const productNameElement = modal.querySelector('.product-name');
        const productIdElement = modal.querySelector('.product-id');
        const rawPriceElement = modal.querySelector('.modal-product-price-raw');
        const qtyInput = modal.querySelector('.modal-qty');
        const sizeSelect = modal.querySelector('.modal-size-select'); // Get the size select element
        const totalSpan = modal.querySelector('.modal-total-price');
        const waLink = modal.querySelector('.modal-wa-link');
        const addToCartButton = modal.querySelector('.modal-add-to-cart-btn'); // Get the Add to Cart button


        // Function to update total, WA link state, AND Add to Cart button state
        const updateModalState = () => {
            // Check if all essential elements are found
            if (!productNameElement || !productIdElement || !rawPriceElement || !qtyInput || !totalSpan || !waLink) {
                console.error("Some essential modal elements not found for WA link update.");
                // Disable both buttons if any essential element is missing
                if(waLink) waLink.classList.add('disabled');
                if(addToCartButton) addToCartButton.classList.add('disabled');
                return; // Stop the function execution
            }

            // Check if sizeSelect exists AND if a size is selected (value is not '')
            // If sizeSelect exists but no value is selected (still on default disabled option)
            if (sizeSelect && sizeSelect.value === '') {
                 // Disable both buttons if size is not selected
                 waLink.classList.add('disabled');
                 waLink.innerText = 'Pilih Ukuran Dulu';
                 if(addToCartButton) addToCartButton.classList.add('disabled');
                 return; // Stop the function execution
            }

            // If all checks pass (elements found, and size selected if dropdown exists), enable buttons and proceed
            waLink.classList.remove('disabled');
            waLink.innerText = 'Lanjutkan Pemesanan';
            if(addToCartButton) addToCartButton.classList.remove('disabled'); // Enable Add to Cart button


            const productId = productIdElement.getAttribute('data-id');
            const productName = productNameElement.getAttribute('data-name');
            const rawPrice = parseInt(rawPriceElement.getAttribute('data-price'));
            const qty = parseInt(qtyInput.value) || 1;
            const selectedSize = sizeSelect ? sizeSelect.value : ''; // Get selected size value, empty string if no select element


            const total = rawPrice * qty;
            totalSpan.innerText = total.toLocaleString('id-ID'); // Format total price

            // Build the WhatsApp message
            let message = `Halo saya ingin memesan ${productName} sebanyak ${qty}`;

            if (selectedSize) { // Add size to the message ONLY if a size was selected from the dropdown
                message += ` ukuran ${selectedSize}`;
            }

             message += ` dengan total harga Rp ${total.toLocaleString('id-ID')}`;

            // Set the WhatsApp link href
            const link = `https://wa.me/${6287874872257}?text=${encodeURIComponent(message)}`;
            waLink.href = link;
        };

        // --- Event Listeners ---

        // Update state when the modal is fully shown
        modal.addEventListener('shown.bs.modal', function () {
             updateModalState(); // Perform initial update when modal opens
        });

        // Update state when quantity changes
        if (qtyInput) {
            qtyInput.addEventListener('input', updateModalState); // 'input' event for real-time updates while typing
        }

        // Update state when the size selection changes
        if (sizeSelect) {
            sizeSelect.addEventListener('change', updateModalState);
        }

        // <<< Add listener for the new Add to Cart button (NEW)
        if (addToCartButton) { // Check if button exists in this modal
            addToCartButton.addEventListener('click', function() {
                // Capture the data from the modal inputs (validation handled by updateModalState before button is enabled)
                const productId = productIdElement.getAttribute('data-id');
                const productName = productNameElement.getAttribute('data-name');
                const rawPrice = parseInt(rawPriceElement.getAttribute('data-price'));
                const qty = parseInt(qtyInput.value) || 1;
                const selectedSize = sizeSelect ? sizeSelect.value : '';

                // --- Kirim data ke add_to_cart.php menggunakan Fetch API ---
                const itemData = {
                    product_id: productId,
                    size: selectedSize,
                    qty: qty
                };

                fetch('add_to_cart.php', { // Pastikan file add_to_cart.php sudah Anda buat dan taruh di lokasi yang sama
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(itemData) // Kirim data sebagai JSON
                })
                .then(response => {
                    if (!response.ok) {
                        console.error('Network response was not ok', response.status, response.statusText);
                         // Coba baca body response untuk detail error dari server jika ada
                         return response.json().catch(() => { throw new Error('Failed to parse error response JSON'); });
                    }
                    return response.json(); // Parse respons JSON
                })
                .then(data => {
                    // Tangani respons dari server add_to_cart.php
                    if (data.success) {
                        alert('Produk berhasil ditambahkan ke keranjang! Total item di keranjang: ' + data.cart_total_quantity); // Beri feedback sukses
                        console.log('Cart updated:', data);

                        // --- LANGKAH SELANJUTNYA: PERBARUI TAMPILAN JUMLAH ITEM KERANJANG ---
                        // Jika Anda memiliki elemen di navbar untuk menampilkan jumlah item keranjang, perbarui di sini
                        // Contoh: const cartCountElement = document.getElementById('cart-count');
                        // if (cartCountElement) {
                        //     cartCountElement.innerText = data.cart_total_quantity;
                        // }

                        // Tutup modal setelah berhasil menambahkan ke keranjang
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                         if (modalInstance) {
                             modalInstance.hide();
                         }


                    } else {
                        // Gagal menambahkan produk (misalnya karena validasi di server add_to_cart.php)
                        alert('Gagal menambahkan produk ke keranjang: ' + (data.message || 'Terjadi kesalahan.'));
                        console.error('Add to cart failed:', data.message);
                    }
                })
                .catch(error => {
                    // Tangani error selama permintaan (misalnya jaringan mati, file PHP tidak ditemukan)
                    alert('Terjadi kesalahan saat menambahkan ke keranjang. Cek konsol.');
                    console.error('Error during fetch:', error);
                });

            }); // End of add to cart button listener
        }

        // Note: updateModalState is called by various events, handling button enablement/disabling
        // based on size selection (if sizeSelect exists and value is empty).

    }); // End of orderModals.forEach

}); // End of DOMContentLoaded
</script>


<footer class="text-center text-muted py-4 mt-5 bg-light">
    © <?= date('Y') ?> Rebelstuff. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>