<?php
session_start();
include "../config.php";
include "navbar_admin.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* replaced query to also fetch latest request_cancellations info (rekening, cancel_type, rc_status) */
$reservasi = mysqli_query($conn, "
SELECT r.*, u.nama, t.kapasitas,
  (SELECT rc.rekening FROM request_cancellations rc WHERE rc.reservation_id = r.id ORDER BY rc.requested_at DESC LIMIT 1) AS refund_rekening,
  (SELECT rc.cancel_type FROM request_cancellations rc WHERE rc.reservation_id = r.id ORDER BY rc.requested_at DESC LIMIT 1) AS rc_cancel_type,
  (SELECT rc.status FROM request_cancellations rc WHERE rc.reservation_id = r.id ORDER BY rc.requested_at DESC LIMIT 1) AS rc_status
FROM reservations r
JOIN users u ON r.user_id = u.id
JOIN tables t ON r.meja_id = t.id
ORDER BY r.tanggal DESC, r.jam_mulai DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Dapur Nusantara</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="has-sidebar">
  <div class="admin-container">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <?= $_SESSION['nama'] ?>!</p>

    <h3 style="margin:20px 0;">Daftar Reservasi</h3>
    <table class="admin-table">
      <tr>
        <th>Nama Pemesan</th>
        <th>Meja</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Total Harga</th>
        <th>Status</th>
        <th>Rekening Refund</th>
        <th>Bukti</th>
        <th>Aksi</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($reservasi)): ?>
        <?php 
          // normalisasi status dan sediakan flag tampil tombol
          $status = strtolower(trim($row['status'] ?? ''));
          $is_waiting_admin = ($status === 'waiting_admin' || $status === 'waiting-admin' || strpos($status, 'waiting') !== false);
          $is_confirmed_like = in_array($status, ['confirmed','approved','approved_admin','active','confirmed_admin'], true) || $status === 'approved';
        ?>
        <tr>
          <td><?= htmlspecialchars($row['nama_pemesan'] ?? $row['nama']) ?></td>
          <td>
            <div class="meja-info">
              <span>Meja <?= htmlspecialchars($row['meja_id']) ?> (<?= htmlspecialchars($row['kapasitas']) ?> orang)</span>
            </div>
          </td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?></td>
          <td><span class="harga-badge">Rp <?= isset($row['harga_total']) && $row['harga_total'] ? number_format($row['harga_total'], 0, ',', '.') : '0' ?></span></td>
          <td>
            <?php
              // tampilkan status dengan badge styling
              $badge_class = 'pending'; // default
              if ($status === 'confirmed' || $status === 'approved') {
                $badge_class = 'confirmed';
              } elseif ($status === 'cancelled' || $status === 'rejected') {
                $badge_class = 'rejected';
              } elseif ($status === 'request_cancel') {
                $badge_class = 'request_cancel';
              } elseif ($status === 'refunded') {
                $badge_class = 'refunded';
              }
            ?>
            <span class="badge <?= $badge_class ?>">
              <?= ucfirst(htmlspecialchars($row['status'])) ?>
            </span>
          </td>
          <td>
            <?php
              $rek = isset($row['refund_rekening']) && $row['refund_rekening'] !== '' ? htmlspecialchars($row['refund_rekening']) : '-';
              echo $rek;
            ?>
          </td>
          <td>
            <?php if (!empty($row['bukti_pembayaran'])): ?>
              <a class="btn-view" href="../uploads/<?= htmlspecialchars($row['bukti_pembayaran']) ?>" target="_blank" rel="noopener noreferrer">Lihat</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <?php
              // tampilkan Approve + Reject saat menunggu admin
              if ($is_waiting_admin) : ?>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=approve" class="btn-approve">Approve</a>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=reject" class="btn-reject">Reject</a>

              <?php
              // jika status pending: tampilkan tombol Reject untuk reject manual oleh admin
              elseif ($status === 'pending') : ?>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=reject" class="btn-reject">Reject</a>

              <?php
              // jika user mengajukan request_cancel: tampilkan Terima (accept_cancel) + Tolak (deny_cancel)
              elseif ($status === 'request_cancel') : ?>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=accept_cancel" class="btn-approve">Terima</a>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=deny_cancel" class="btn-reject">Tolak</a>

              <?php
              // jika sudah confirmed/approved tampilkan opsi Reject
              elseif ($is_confirmed_like) : ?>
                <a href="admin_konfirmasi.php?id=<?= $row['id'] ?>&aksi=reject" class="btn-reject">Reject</a>

              <?php else: ?>
                -
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
