<?php
session_start();
include "../config.php";
include "navbar_admin.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Tambah meja
if (isset($_POST['tambah'])) {
    $kapasitas = $_POST['kapasitas'];
    if (!empty($kapasitas)) {
        mysqli_query($conn, "INSERT INTO tables (kapasitas) VALUES ('$kapasitas')");
        header("Location: admin_meja.php");
        exit;
    }
}

// Hapus meja
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tables WHERE id='$id'");
    header("Location: admin_meja.php");
    exit;
}

$meja = mysqli_query($conn, "SELECT * FROM tables ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Meja - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="has-sidebar">
  <div class="admin-container">
    <h2>Kelola Meja</h2>
    <form method="post" class="form-meja">
      <label>Kapasitas Meja:</label>
      <input type="number" name="kapasitas" min="1" required>
      <button type="submit" name="tambah">Tambah Meja</button>
    </form>

    <h3>Daftar Meja</h3>
    <table class="admin-table">
      <tr>
        <th>ID</th>
        <th>Kapasitas</th>
        <th>Aksi</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($meja)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['kapasitas'] ?> orang</td>
          <td>
            <a href="admin_meja.php?hapus=<?= $row['id'] ?>" 
               onclick="return confirm('Yakin hapus meja ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
