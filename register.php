<?php
require_once 'koneksi.php';

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi sederhana
    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users1 (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Registrasi berhasil! Silakan <a href='login.php'>login</a>.</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red;'>Harap isi semua data.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - Rebelstuff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;  /* Latar belakang putih */
            color: #333;                /* Warna teks gelap */
            font-family: 'Arial', sans-serif;
        }
        .card {
            border-radius: 1.2rem;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;  /* Card putih */
        }
        .form-control {
            background-color: #e9ecef;  /* Form abu-abu terang */
            border: 1px solid #ced4da;   /* Border abu-abu */
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #6c757d;       /* Border saat fokus abu-abu gelap */
        }
        .btn-primary {
            background-color: #343a40;   /* Warna tombol hitam */
            border: none;
        }
        .btn-primary:hover {
            background-color: #495057;   /* Warna tombol hitam lebih gelap saat hover */
        }
        .alert {
            background-color: #f8d7da;   /* Latar belakang alert abu-abu muda */
            color: #721c24;              /* Teks merah gelap */
            border-radius: 1rem;
        }
        .alert .btn-close {
            color: #721c24;
        }
        .footer {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Rebelstuff</h3>
                        <p class="text-muted">Silakan isi form berikut untuk registrasi</p>
                    </div>

                    <!-- Menampilkan pesan jika ada error -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Registrasi -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-control" 
                                placeholder="Masukkan username" 
                                required 
                                autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="Masukkan email" 
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="Masukkan password" 
                                required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">Registrasi</button>
                        </div>
                    </form>
                    <div class="text-center">
                        <small class="text-muted">
                            Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>
                        </small>
                    </div>
                </div>
                <p class="text-center text-muted mt-3 footer">&copy; <?= date('Y') ?> Rebelstuff</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
