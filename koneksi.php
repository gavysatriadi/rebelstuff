<?php
$host = 'localhost';     // host database
$user = 'root';          // username database
$pass = '123456';        // password database (kosongkan jika default XAMPP)
$db   = 'rebelstuff';    // nama database kamu

$conn = new mysqli($host, $user, $pass, $db);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>
