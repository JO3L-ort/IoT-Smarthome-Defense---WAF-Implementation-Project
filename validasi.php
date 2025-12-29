<?php
session_start();

// Koneksi database
$koneksi = new mysqli("localhost", "root", "", "smarthome_hackthecity");

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil data dari form
// $username = $koneksi->real_escape_string($_POST['username']);
// $password = $koneksi->real_escape_string($_POST['password']);
$username = $_POST['username'];
$password = $_POST['password'];

// Query cek user
$sql = "SELECT * FROM tb_user WHERE username='$username' AND password=MD5('$password')";
$result = $koneksi->query($sql);

if ($result->num_rows > 0) {
    // Jika login benar
    $_SESSION['username'] = $username;
    header("Location: index.php"); // halaman setelah login
    exit();
} else {
    // Jika login salah
    echo "<script>alert('Username atau password salah!');window.location='login.php';</script>";
}
?>
