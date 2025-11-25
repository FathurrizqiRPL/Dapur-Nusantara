<?php
session_start();
header('Content-Type: application/json');
include "config.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Tidak terautentikasi']);
    exit;
}

$user_id = $_SESSION['user_id'];
$reservation_id = intval($_POST['reservation_id'] ?? 0);
$cancel_type = ($_POST['cancel_type'] ?? 'no_refund') === 'refund' ? 'refund' : 'no_refund';
$nama = trim($_POST['nama'] ?? '');
$notelp = trim($_POST['notelp'] ?? '');
$rekening = trim($_POST['rekening'] ?? '');

// basic validation
if (!$reservation_id || $nama === '' || $notelp === '') {
    echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']);
    exit;
}

// load reservation and validate ownership + status
$resQ = mysqli_query($conn, "SELECT * FROM reservations WHERE id = $reservation_id LIMIT 1");
if (!$resQ || mysqli_num_rows($resQ) === 0) {
    echo json_encode(['success'=>false,'message'=>'Reservasi tidak ditemukan']);
    exit;
}
$res = mysqli_fetch_assoc($resQ);
if ((int)$res['user_id'] !== (int)$user_id) {
    echo json_encode(['success'=>false,'message'=>'Bukan reservasi Anda']);
    exit;
}
if ($res['status'] !== 'confirmed') {
    echo json_encode(['success'=>false,'message'=>'Reservasi tidak dapat dibatalkan (status bukan confirmed)']);
    exit;
}

// server-side re-check time window for refund permission
$resDateTime = $res['tanggal'] . ' ' . ($res['jam_mulai'] ?? '00:00:00');
$timestampRes = strtotime($resDateTime);
$diffSeconds = $timestampRes - time();
$server_allow_refund = ($diffSeconds >= 24*3600);

// if cancel_type is refund but not allowed, override to no_refund
if ($cancel_type === 'refund' && !$server_allow_refund) {
    $cancel_type = 'no_refund';
}

// if refund requested ensure rekening provided
if ($cancel_type === 'refund' && $rekening === '') {
    echo json_encode(['success'=>false,'message'=>'Nomor rekening diperlukan untuk refund']);
    exit;
}

// sanitize before insert
$nama_safe = mysqli_real_escape_string($conn, $nama);
$notelp_safe = mysqli_real_escape_string($conn, $notelp);
$rekening_safe = mysqli_real_escape_string($conn, $rekening);
$cancel_type_safe = mysqli_real_escape_string($conn, $cancel_type);
$now = date('Y-m-d H:i:s');

// insert into request_cancellations
$insSql = "INSERT INTO request_cancellations (reservation_id, user_id, cancel_type, nama_pemesan, notelp, rekening, requested_at, status)
           VALUES ($reservation_id, $user_id, '{$cancel_type_safe}', '{$nama_safe}', '{$notelp_safe}', '{$rekening_safe}', '{$now}', 'pending')";
if (!mysqli_query($conn, $insSql)) {
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan request']);
    exit;
}

// update reservation status
$upd = mysqli_query($conn, "UPDATE reservations SET status = 'request_cancel' WHERE id = $reservation_id");
if (!$upd) {
    echo json_encode(['success'=>false,'message'=>'Gagal mengubah status reservasi']);
    exit;
}

echo json_encode(['success'=>true]);
exit;
?>
