<?php
session_start();
include "../config.php";
include "navbar_admin.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$uploadDir = __DIR__ . '/../assets/img/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* Handle POST actions: add / edit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_menu'] ?? '');
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi_menu'] ?? '');
        $gambarFile = '';

        if (!empty($_FILES['gambar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $gambarFile = 'menu_'.time().'_'.rand(100,999).'.'.$ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir.$gambarFile);
            }
        }

        $sql = "INSERT INTO menuandalan (gambar, nama_menu, deskripsi_menu) VALUES ('{$gambarFile}', '{$nama}', '{$desc}')";
        mysqli_query($conn, $sql);
        header("Location: admin_menuandalan.php");
        exit;
    }

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_menu'] ?? '');
        $desc = mysqli_real_escape_string($conn, $_POST['deskripsi_menu'] ?? '');

        // get existing to possibly remove old file
        $resOld = mysqli_query($conn, "SELECT gambar FROM menuandalan WHERE menu_id = $id");
        $old = mysqli_fetch_assoc($resOld);

        $gambarFile = $old['gambar'] ?? '';

        if (!empty($_FILES['gambar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $newName = 'menu_'.time().'_'.rand(100,999).'.'.$ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir.$newName)) {
                    // remove old
                    if (!empty($gambarFile) && file_exists($uploadDir.$gambarFile)) {
                        @unlink($uploadDir.$gambarFile);
                    }
                    $gambarFile = $newName;
                }
            }
        }

        $sql = "UPDATE menuandalan SET nama_menu = '{$nama}', deskripsi_menu = '{$desc}', gambar = '{$gambarFile}' WHERE menu_id = $id";
        mysqli_query($conn, $sql);
        header("Location: admin_menuandalan.php");
        exit;
    }
}

/* Handle delete via GET ?delete=ID */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT gambar FROM menuandalan WHERE menu_id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['gambar']) && file_exists($uploadDir . $row['gambar'])) {
            @unlink($uploadDir . $row['gambar']);
        }
    }
    mysqli_query($conn, "DELETE FROM menuandalan WHERE menu_id = $id");
    header("Location: admin_menuandalan.php");
    exit;
}

/* Data untuk tampil */
$list = mysqli_query($conn, "SELECT * FROM menuandalan ORDER BY menu_id DESC");

/* Jika edit mode: load data */
$editItem = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM menuandalan WHERE menu_id = $eid LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $editItem = mysqli_fetch_assoc($q);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Menu Andalan - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="has-sidebar">
  <div class="admin-container">
    <h2>Kelola Menu Andalan</h2>

    <!-- Form Add / Edit -->
    <?php if ($editItem): ?>
      <h3>Edit Menu</h3>
      <form method="post" enctype="multipart/form-data" class="menuandalan-form">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?= $editItem['menu_id'] ?>">
        <label>Nama Menu</label>
        <input type="text" name="nama_menu" value="<?= htmlspecialchars($editItem['nama_menu']) ?>" required>
        <label>Deskripsi</label>
        <textarea name="deskripsi_menu" rows="4" required><?= htmlspecialchars($editItem['deskripsi_menu']) ?></textarea>
        <label>Gambar (kosongkan jika tidak ingin mengganti)</label>
        <?php if (!empty($editItem['gambar'])): ?>
          <div><img src="../assets/img/<?= htmlspecialchars($editItem['gambar']) ?>" alt="" style="max-width:150px;border-radius:8px;margin-bottom:8px;"></div>
        <?php endif; ?>
        <input type="file" name="gambar" accept="image/*">
        <button type="submit">Simpan Perubahan</button>
        <a href="admin_menuandalan.php" style="margin-left:8px;">Batal</a>
      </form>
    <?php else: ?>
      <h3>Tambah Menu Baru</h3>
      <form method="post" enctype="multipart/form-data" class="menuandalan-form">
        <input type="hidden" name="action" value="add">
        <label>Nama Menu</label>
        <input type="text" name="nama_menu" required>
        <label>Deskripsi</label>
        <textarea name="deskripsi_menu" rows="4" required></textarea>
        <label>Gambar</label>
        <input type="file" name="gambar" accept="image/*" required>
        <button type="submit">Tambah</button>
      </form>
    <?php endif; ?>

    <hr style="margin:20px 0;">

    <!-- Daftar Menu -->
    <h3>Daftar Menu Andalan</h3>
    <div class="menuandalan-list">
      <?php while ($row = mysqli_fetch_assoc($list)): ?>
        <div class="card">
          <div class="thumb">
            <?php if (!empty($row['gambar']) && file_exists($uploadDir . $row['gambar'])): ?>
              <img src="../assets/img/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_menu']) ?>">
            <?php else: ?>
              <div class="no-thumb">No Image</div>
            <?php endif; ?>
          </div>
          <div class="info">
            <h4><?= htmlspecialchars($row['nama_menu']) ?></h4>
            <p><?= nl2br(htmlspecialchars($row['deskripsi_menu'])) ?></p>
            <div class="actions">
              <a class="btn-edit" href="admin_menuandalan.php?edit=<?= $row['menu_id'] ?>">Edit</a>
              <a class="btn-delete" href="admin_menuandalan.php?delete=<?= $row['menu_id'] ?>" onclick="return confirm('Hapus menu ini?')">Hapus</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>