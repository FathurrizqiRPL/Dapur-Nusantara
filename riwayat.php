<?php
session_start();
include "config.php";
include "navbar.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$reservasi = mysqli_query($conn, "SELECT r.*, t.kapasitas 
    FROM reservations r
    JOIN tables t ON r.meja_id = t.id
    WHERE r.user_id='$user_id' ORDER BY r.tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Reservasi - Dapur Nusantara</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Modal sederhana */
    .rc-modal { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(0,0,0,0.4); z-index: 2000; }
    .rc-modal .rc-card { background:#fff; padding:18px; border-radius:8px; width: 420px; max-width:95%; }
    .rc-modal h3 { margin-bottom:8px; }
    .rc-modal label { display:block; margin:8px 0 4px; font-weight:600; }
    .rc-modal input[type="text"], .rc-modal input[type="tel"], .rc-modal textarea {
      width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
    }
    .rc-actions { margin-top:12px; display:flex; gap:10px; justify-content:flex-end; }
    .rc-actions button { padding:8px 12px; border-radius:6px; border:none; cursor:pointer; }
    .rc-cancel { background:#ccc; color:#000; }
    .rc-submit { background:#e74c3c; color:#fff; }
    .rc-note { font-size:0.9rem; color:#555; margin-top:6px; }
    
    /* Status badges styling */
    .riwayat .badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 6px;
      color: #fff;
      font-weight: 700;
      font-size: 0.85rem;
      text-align: center;
    }
    .riwayat .badge.pending { background: #ff9800; }
    .riwayat .badge.confirmed { background: #4caf50; }
    .riwayat .badge.rejected { background: #f44336; }

    /* Countdown styling */
    .countdown {
      font-size: 0.85rem;
      color: #d32f2f;
      font-weight: 700;
      margin-top: 4px;
    }
  </style>
</head>
<body class="riwayat-body">
  <div class="riwayat">
    <h2 style="text-align:center; margin-bottom:20px;">Riwayat Reservasi</h2>
    <table>
      <tr>
        <th>Nama Pemesan</th>
        <th>Nomor Telepon</th>
        <th>Meja</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Total Harga</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($reservasi)): ?>
        <?php
          $resDateTime = $row['tanggal'] . ' ' . ($row['jam_mulai'] ?? '00:00:00');
          $timestampRes = strtotime($resDateTime);
          $diffSeconds = $timestampRes - time();
          $allow_refund = ($diffSeconds >= 24 * 3600) ? 1 : 0;

          // hitung countdown untuk pending
          $createdAt = strtotime($row['created_at']);
          $now = time();
          $elapsedSec = $now - $createdAt;
          $remainingSec = max(0, (5 * 60) - $elapsedSec); // 5 menit = 300 detik
        ?>
        <tr>
          <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
          <td><?= htmlspecialchars($row['notelp']) ?></td>
          <td>
            <div class="meja-info">
              <span>Meja <?= $row['meja_id'] ?> (<?= $row['kapasitas'] ?> orang)</span>
            </div>
          </td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?></td>
          <td><span class="harga-badge">Rp <?= isset($row['harga_total']) && $row['harga_total'] ? number_format($row['harga_total'], 0, ',', '.') : '0' ?></span></td>
          <td>
            <?php if ($row['status'] == 'pending'): ?>
              <span class="badge pending">Pending</span>
              <?php if ($remainingSec > 0): ?>
                <div class="countdown" id="countdown-<?= $row['id'] ?>">
                  ⏱️ Waktu tersisa: <span id="timer-<?= $row['id'] ?>">05:00</span>
                </div>
              <?php endif; ?>
            <?php elseif ($row['status'] == 'confirmed'): ?>
              <span class="badge confirmed">Confirmed</span>
            <?php elseif ($row['status'] == 'waiting_admin'):?>
               <span class="badge pending">Waiting admin</span>
             <?php elseif ($row['status'] == 'request_cancel'):?>
               <span class="badge pending">Request Cancel</span>
            <?php elseif ($row['status'] == 'refunded'):?>
               <span class="badge refunded">Refunded</span>
            <?php else:?>
              <span class="badge rejected">Cancelled</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($row['status'] == 'pending'): ?>
              <a href="konfirmasi_pembayaran.php?id=<?= $row['id'] ?>" class="btn-konfirmasi">Konfirmasi</a>
            <?php elseif ($row['status'] == 'confirmed'): ?>
              <?php if ($allow_refund): ?>
                <button class="btn"
                  onclick="openRequestCancelModal(<?= $row['id'] ?>, 1, '<?= addslashes(htmlspecialchars($row['nama_pemesan'])) ?>', '<?= addslashes(htmlspecialchars($row['notelp'])) ?>')"
                  style="background:#f4b400; padding:6px; border-radius:6px; border:none; font-weight:600;color:white;">
                  Request Cancel
                </button>
              <?php else: ?>
                <button class="btn"
                  onclick="sendNoRefundRequest(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['nama_pemesan'])) ?>', '<?= addslashes(htmlspecialchars($row['notelp'])) ?>')"
                  style="background:#f4b400; padding:6px; border-radius:6px; border:none; font-weight:600;color:white;">
                  Request Cancel
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php if ($row['status'] == 'pending' && $remainingSec > 0): ?>
          <script>
            (function() {
              let remaining = <?= $remainingSec ?>;
              let resId = <?= $row['id'] ?>;
              
              function updateTimer() {
                let mins = Math.floor(remaining / 60);
                let secs = remaining % 60;
                let display = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
                let timerEl = document.getElementById('timer-' + resId);
                if (timerEl) timerEl.textContent = display;

                if (remaining <= 0) {
                  // auto-cancel
                  fetch('auto_cancel_pending.php?id=' + resId)
                    .then(r => r.json())
                    .then(res => {
                      if (res.success) {
                        alert('Reservasi Anda telah dibatalkan otomatis karena tidak dikonfirmasi dalam 5 menit.');
                        window.location.reload();
                      }
                    });
                  return;
                }

                remaining--;
                setTimeout(updateTimer, 1000);
              }

              updateTimer();
            })();
          </script>
        <?php endif; ?>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Modal Request Cancel -->
  <div id="rcModal" class="rc-modal" aria-hidden="true">
    <div class="rc-card" role="dialog" aria-modal="true">
      <h3>Ajukan Pembatalan Reservasi</h3>
      <form id="rcForm">
        <input type="hidden" name="reservation_id" id="rc_reservation_id" value="">
        <input type="hidden" name="cancel_type" id="rc_cancel_type" value="no_refund">
        <label>Nama Pemesan</label>
        <input type="text" name="nama" id="rc_nama" required>
        <label>Nomor HP</label>
        <input type="tel" name="notelp" id="rc_notelp" required>
        <div id="rc_bank_block" style="display:none;">
          <label>Nomor Rekening (untuk refund)</label>
          <input type="text" name="rekening" id="rc_rek" placeholder="No. Rekening untuk pengembalian">
          <div class="rc-note">Pastikan nomor rekening sesuai untuk proses refund.</div>
        </div>
        <div class="rc-actions">
          <button type="button" class="rc-cancel" onclick="closeRcModal()">Batal</button>
          <button type="submit" class="rc-submit">Kirim Request</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openRequestCancelModal(resId, allowRefund, name, phone) {
      document.getElementById('rc_reservation_id').value = resId;
      // set cancel_type and show bank block if refund allowed
      if (allowRefund == 1) {
        document.getElementById('rc_cancel_type').value = 'refund';
        document.getElementById('rc_bank_block').style.display = 'block';
      } else {
        document.getElementById('rc_cancel_type').value = 'no_refund';
        document.getElementById('rc_bank_block').style.display = 'none';
      }
      // prefill name/phone
      document.getElementById('rc_nama').value = name || '';
      document.getElementById('rc_notelp').value = phone || '';
      document.getElementById('rcModal').style.display = 'flex';
      document.getElementById('rcModal').setAttribute('aria-hidden', 'false');
    }
    function closeRcModal() {
      document.getElementById('rcModal').style.display = 'none';
      document.getElementById('rcModal').setAttribute('aria-hidden', 'true');
    }

    document.getElementById('rcForm').addEventListener('submit', function(e){
      e.preventDefault();
      var form = e.target;
      var data = new FormData(form);
      fetch('request_cancel.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      }).then(r => r.json()).then(function(res){
        if (res.success) {
          alert('Request pembatalan terkirim. Status reservasi akan berubah menjadi "Request Cancel".');
          closeRcModal();
          window.location.reload();
        } else {
          alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
        }
      }).catch(function(){
        alert('Gagal mengirim request.');
      });
    });

    // Kirim request cancel tanpa modal (no refund)
    function sendNoRefundRequest(resId, name, phone) {
      if (!confirm('Anda yakin ingin mengajukan pembatalan tanpa refund untuk reservasi ini?')) return;

      var data = new FormData();
      data.append('reservation_id', resId);
      data.append('cancel_type', 'no_refund');
      data.append('nama', name || '');
      data.append('notelp', phone || '');

      fetch('request_cancel.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      }).then(r => r.json())
        .then(function(res){
        if (res.success) {
          alert('Request pembatalan terkirim. Status reservasi akan berubah menjadi "Request Cancel".');
          window.location.reload();
        } else {
          alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
        }
      }).catch(function(){
        alert('Gagal mengirim request.');
      });
    }
  </script>
</body>
</html>
