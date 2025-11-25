<?php
session_start();
include "config.php";
include "navbar.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// Jam buka restoran
$jam_buka = "10:00:00";
$jam_tutup = "22:00:00";

if (isset($_POST['reservasi'])) {
    $nama_pemesan = $_POST['nama_pemesan'];
    $notelp = $_POST['notelp'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $meja_id = $_POST['meja_id'];
    $user_id = $_SESSION['user_id'];

    // Hitung jam selesai otomatis (90 menit setelah jam mulai)
    $jam_selesai = date("H:i:s", strtotime($jam_mulai) + 90 * 60);

    // Validasi: hanya izinkan reservasi untuk hari besok atau setelahnya
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    if ($tanggal < $tomorrow) {
        $error = "Reservasi hanya dapat dilakukan untuk hari besok atau tanggal yang lebih jauh. (Tidak boleh reservasi untuk hari ini).";
    } else {
        // Validasi jam buka
        if ($jam_mulai < $jam_buka || $jam_selesai > $jam_tutup) {
            $error = "Reservasi hanya dapat dilakukan antara jam 10:00 - 22:00.";
        } else {
            // Cek apakah meja sudah dipesan pada jam & tanggal yang sama
            $cek = mysqli_query($conn, "SELECT * FROM reservations 
                WHERE meja_id='$meja_id' AND tanggal='$tanggal'
                AND (
                    (jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') OR
                    (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai')
                ) AND status != 'cancelled'");

            if (mysqli_num_rows($cek) > 0) {
                $error = "Meja ini sudah dipesan pada jam tersebut. Silakan pilih meja atau jam lain.";
            } else {
                $insert = mysqli_query($conn, "INSERT INTO reservations 
                    (user_id, meja_id, nama_pemesan, notelp, tanggal, jam_mulai, jam_selesai, status) 
                    VALUES ('$user_id', '$meja_id', '$nama_pemesan' ,'$notelp', '$tanggal', '$jam_mulai', '$jam_selesai', 'pending')");

                if ($insert) {
                    $success = "Reservasi berhasil dibuat! Silakan konfirmasi pembayaran untuk melanjutkan.";
                } else {
                    $error = "Terjadi kesalahan saat membuat reservasi.";
                }
            }
        }
    }
}

$tables = mysqli_query($conn, "SELECT * FROM tables ORDER BY nomor_meja ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservasi - Dapur Nusantara</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .popup {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      display: flex; justify-content: center; align-items: center;
      z-index: 1000;
    }
    .popup-content {
      background: #fff; padding: 20px; border-radius: 10px; text-align: center;
      min-width: 300px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .popup-content.error { border-left: 6px solid red; }
    .popup-content.success { border-left: 6px solid green; }
    .close {
      position: absolute; top: 10px; right: 15px; font-size: 22px;
      cursor: pointer; color: #888;
    }

    .btn-cek-meja {
      background-color: #2196F3;
      color: white;
      padding: 10px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      margin-top: 8px;
      transition: background-color 0.3s;
    }
    .btn-cek-meja:hover {
      background-color: #0b7dda;
    }
    .btn-cek-meja:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    .meja-feedback {
      margin-top: 8px;
      font-size: 0.9rem;
      padding: 8px;
      border-radius: 6px;
      display: none;
    }
    .meja-feedback.info {
      background: #e3f2fd;
      color: #1976d2;
      display: block;
    }
    .meja-feedback.warning {
      background: #fff3cd;
      color: #856404;
      display: block;
    }
  </style>
</head>
<body class="reservasi-body">
  <div class="page-container">
    <h2>Form Reservasi</h2>

    <?php if ($error): ?>
      <div class="popup" id="popup">
        <div class="popup-content error">
          <span class="close" onclick="closePopup()">&times;</span>
          <p><?= $error ?></p>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="popup" id="popup">
        <div class="popup-content success">
          <span class="close" onclick="closePopup()">&times;</span>
          <p><?= $success ?></p>
          <a href="riwayat.php" class="btn-riwayat">Lihat Riwayat Reservasi</a>
        </div>
      </div>
    <?php endif; ?>

    <div class="reservation-form">
      <form method="post">
        <label>Nama Pemesan:</label>
        <input type="text" name="nama_pemesan" required>

        <label>Nomor telepon:</label>
        <input type="number" name="notelp" required>

        <label>Tanggal:</label>
        <!-- hanya boleh reservasi mulai hari besok -->
        <input type="date" name="tanggal" id="tanggal" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">

        <label>Jam Mulai:</label>
        <input type="time" name="jam_mulai" id="jam_mulai" required min="10:00" max="21:00">
        
        <!-- New: button to check available tables -->
        <button type="button" class="btn-cek-meja" id="btnCekMeja" onclick="cekMeja()">
          Cek Meja yang Tersedia
        </button>
        <div class="meja-feedback" id="mejaFeedback"></div>

        <label>Pilih Meja:</label>
        <select name="meja_id" required id="mejaSelect">
          <option value="">-- Pilih Meja --</option>
          <?php
          mysqli_data_seek($tables, 0);
          while ($row = mysqli_fetch_assoc($tables)):
              $disabled = false;
              if (isset($_POST['tanggal']) && isset($_POST['jam_mulai'])) {
                  $tanggal = $_POST['tanggal'];
                  $jam_mulai = $_POST['jam_mulai'];
                  $jam_selesai = date("H:i:s", strtotime($jam_mulai) + 90 * 60);

                  $cek = mysqli_query($conn, "SELECT * FROM reservations 
                      WHERE meja_id='{$row['id']}' AND tanggal='$tanggal'
                      AND (
                          (jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') OR
                          (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai')
                      ) AND status != 'cancelled'");

                  if (mysqli_num_rows($cek) > 0) $disabled = true;
              }?>
              <option value="<?= $row['id'] ?>" <?= $disabled ? 'disabled style="color:gray;"' : '' ?>>
                Meja <?= $row['nomor_meja'] ?> (<?= $row['kapasitas'] ?> orang)
                <?= $disabled ? ' - (Sudah dipesan)' : '' ?>
              </option>
          <?php endwhile; ?>
        </select>

        <button type="submit" name="reservasi">Buat Reservasi</button>
      </form>
    </div>
  </div>

  <script>
    function closePopup() {
      document.getElementById("popup").style.display = "none";
    }

    
    function cekMeja() {
        let tanggal = document.getElementById("tanggal").value;
        let jam = document.getElementById("jam_mulai").value;
        let feedback = document.getElementById("mejaFeedback");

        // client-side reject jika tanggal < hari besok
        const tomorrowStr = new Date(Date.now() + 24*3600*1000).toISOString().slice(0,10);
        if (tanggal && tanggal < tomorrowStr) {
            feedback.textContent = "Reservasi hanya boleh untuk hari besok atau setelahnya. Silakan pilih tanggal lain.";
            feedback.className = "meja-feedback warning";
            return;
        }

        if (tanggal === "" || jam === "") {
            feedback.textContent = "Silakan pilih tanggal dan jam terlebih dahulu.";
            feedback.className = "meja-feedback warning";
            return;
        }

        fetch("cek_meja.php?tanggal=" + tanggal + "&jam_mulai=" + jam)
            .then(res => res.json())
            .then(disabledList => {
                let select = document.getElementById("mejaSelect");
                let availableCount = 0;
                let unavailableCount = 0;

                for (let opt of select.options) {
                    if (opt.value === "") continue;

                    if (disabledList.includes(opt.value)) {
                        opt.disabled = true;
                        opt.style.color = "gray";
                        if (!opt.textContent.includes("(Sudah dipesan)")) {
                            opt.textContent = opt.textContent.split(" (Tersedia)")[0] + " (Sudah dipesan)";
                        }
                        unavailableCount++;
                    } else {
                        opt.disabled = false;
                        opt.style.color = "black";
                        if (!opt.textContent.includes("(Tersedia)")) {
                            opt.textContent = opt.textContent.split(" (Sudah dipesan)")[0] + " (Tersedia)";
                        }
                        availableCount++;
                    }
                }

                // Show feedback
                if (availableCount > 0) {
                    feedback.textContent = `✓ Ada ${availableCount} meja tersedia pada jam ${jam}. ${unavailableCount > 0 ? `(${unavailableCount} meja sudah dipesan)` : ''}`;
                    feedback.className = "meja-feedback info";
                } else {
                    feedback.textContent = `✗ Semua meja sudah dipesan pada jam ${jam}. Silakan pilih jam lain.`;
                    feedback.className = "meja-feedback warning";
                }
            })
            .catch(err => {
                feedback.textContent = "Gagal mengecek ketersediaan meja.";
                feedback.className = "meja-feedback warning";
            });
    }

    // Auto-check when date or time changes
    document.getElementById("tanggal").addEventListener("change", cekMeja);
    document.getElementById("jam_mulai").addEventListener("change", cekMeja);
  </script>

  <style>
    .btn-reservasi {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-riwayat {
      display: inline-block;
      margin-top: 10px;
      background-color: #000000ff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      transition:transform 0.2s ease-in-out;
    }

  .btn-riwayat:hover {
    transform: scale(1.05);
  }
  </style>
</body>
</html>
