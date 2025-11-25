<?php
// Konfigurasi koneksi database
$host = "localhost";      // biasanya localhost
$user = "root";           // username MySQL
$pass = "";               // password MySQL (kosong kalau pakai XAMPP/MAMP default)
$db   = "dapur_nusantara"; // nama database kamu

// Membuat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set timezone ke Indonesia
date_default_timezone_set("Asia/Jakarta");
