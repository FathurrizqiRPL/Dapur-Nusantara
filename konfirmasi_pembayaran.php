<?php
session_start();
include "config.php";
include "navbar.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$reservasi_id = $_GET['id'];

// Ambil data reservasi + kapasitas meja
$query = mysqli_query($conn, "SELECT r.*, t.kapasitas 
    FROM reservations r 
    JOIN tables t ON r.meja_id = t.id 
    WHERE r.id='$reservasi_id' AND r.user_id='".$_SESSION['user_id']."'");

if (!$row = mysqli_fetch_assoc($query)) {
    die("Reservasi tidak ditemukan!");
}

// Hitung DP (misal Rp 10.000 per orang)
$dp = $row['kapasitas'] * 10000;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["bukti"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["bukti"]["tmp_name"], $targetFilePath)) {
        // Update reservasi dengan bukti pembayaran
        $update = "UPDATE reservations SET status='waiting_admin', bukti_pembayaran='$fileName' WHERE id='$reservasi_id'";
        if (mysqli_query($conn, $update)) {
            header("Location: riwayat.php");
            exit;
        } else {
            $error = "Gagal menyimpan konfirmasi!";
        }
    } else {
        $error = "Upload bukti gagal!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konfirmasi Pembayaran - Dapur Nusantara</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>

    .konfirmasi-pembayaran-body{
  background-image:url(../img/woodbackground.jpg );
  background-size:cover;
}
  </style>
</head>
<body class="konfirmasi-pembayaran-body">
  <div class="reservation-form" style="margin-top:120px;">
    <h2>Konfirmasi Pembayaran</h2>

    <p><strong>Nama Pemesan:</strong> <?= $row['nama_pemesan'] ?></p>
    <p><strong>Nomor telepon:</strong> <?= $row['notelp'] ?></p>
    <p><strong>Meja:</strong> <?= $row['meja_id'] ?> (Kapasitas <?= $row['kapasitas'] ?> orang)</p>
    <p><strong>Tanggal:</strong> <?= $row['tanggal'] ?> | <?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?></p>
    <p><strong>Total DP:</strong> Rp <?= number_format($dp, 0, ',', '.') ?></p>
    <p><strong>Nomor Rekening Restoran: <br> 8372 1649 (BCA)  <br> 9000 1234567890 (Mandiri)  <br> 8809 567123 (BNI)  <br> 0381 0100 123456 (BRI) </strong></p>

    <?php if(isset($error)): ?>
      <p style="color:red; text-align:center;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label for="bukti">Upload Bukti Pembayaran (jpg/png):</label>
      <input type="file" name="bukti" id="bukti" accept="image/*" required>
      <button type="submit">Konfirmasi</button>
    </form>
  </div>
</body>
</html>
