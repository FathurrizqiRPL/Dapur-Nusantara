<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = $_GET['id'];
$reservasi = mysqli_query($conn, "SELECT * FROM reservations WHERE id='$id'");
$data = mysqli_fetch_assoc($reservasi);

if (!$data) {
    echo "Reservasi tidak ditemukan!";
    exit;
}

// Update reservasi
if (isset($_POST['update'])) {
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $meja_id = $_POST['meja_id'];
    $nama_pemesan = $_POST['nama_pemesan'];

    $update = mysqli_query($conn, "UPDATE reservations 
        SET tanggal='$tanggal', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai', meja_id='$meja_id', nama_pemesan='$nama_pemesan' 
        WHERE id='$id'");

    if ($update) {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Gagal memperbarui reservasi!";
    }
}

$tables = mysqli_query($conn, "SELECT * FROM tables ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Reservasi - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="admin-container">
    <h2>Edit Reservasi</h2>
    <form method="post" class="form-reservasi">
      <label>Nama Pemesan:</label>
      <input type="text" name="nama_pemesan" value="<?= $data['nama_pemesan'] ?>" required>

      <label>Tanggal:</label>
      <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>" required>

      <label>Jam Mulai:</label>
      <input type="time" name="jam_mulai" value="<?= $data['jam_mulai'] ?>" required>

      <label>Jam Selesai:</label>
      <input type="time" name="jam_selesai" value="<?= $data['jam_selesai'] ?>" required>

      <label>Pilih Meja:</label>
      <select name="meja_id" required>
        <?php while($row = mysqli_fetch_assoc($tables)): ?>
          <option value="<?= $row['id'] ?>" <?= $row['id']==$data['meja_id'] ? 'selected' : '' ?>>
            Meja <?= $row['id'] ?> (<?= $row['kapasitas'] ?> orang)
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit" name="update">Simpan Perubahan</button>
      <a href="admin_dashboard.php" class="btn-cancel">Batal</a>
    </form>
  </div>
</body>
</html>
