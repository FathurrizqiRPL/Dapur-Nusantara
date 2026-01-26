<?php
session_start();
include "../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$aksi = isset($_GET['aksi']) ? strtolower(trim($_GET['aksi'])) : '';

if (!$id || !in_array($aksi, ['approve','reject','accept_cancel','deny_cancel'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$success = false;
mysqli_report(MYSQLI_REPORT_OFF); // jangan tampilkan warning ke user

// gunakan transaksi bila tersedia
$use_tx = function_exists('mysqli_begin_transaction');

if ($use_tx) mysqli_begin_transaction($conn);

if ($aksi === 'approve') {
    // Approve: ubah status reservasi menjadi confirmed
    $q = "UPDATE reservations SET status = 'confirmed' WHERE id = $id";
    $success = mysqli_query($conn, $q);
}

elseif ($aksi === 'reject') {
    // Reject admin (menolak reservasi): tandai reservation rejected, hapus rekening pending requests untuk keamanan
    $ok1 = mysqli_query($conn, "UPDATE reservations SET status = 'cancelled' WHERE id = $id");
    $ok2 = mysqli_query($conn, "
        UPDATE request_cancellations
        SET rekening = NULL,
            status = 'rejected',
            admin_note = CONCAT(IFNULL(admin_note,''), '\nRejected by admin (', NOW(), ')')
        WHERE reservation_id = $id AND status = 'pending'
    ");
    $success = ($ok1 && $ok2);
}

elseif ($aksi === 'accept_cancel') {
    // Admin menerima permintaan cancel: set reservation -> Refunded, tandai request processed, dan free up time slot
    $ok1 = mysqli_query($conn, "UPDATE reservations SET status = 'refunded' WHERE id = $id");
    $ok2 = mysqli_query($conn, "
        UPDATE request_cancellations
        SET status = 'processed',
            admin_note = CONCAT(IFNULL(admin_note,''), '\nAccepted cancellation by admin (', NOW(), ')')
        WHERE reservation_id = $id AND status = 'pending'
    ");
    $success = ($ok1 && $ok2);
}

elseif ($aksi === 'deny_cancel') {
    // Admin menolak request cancel: kembalikan reservation ke confirmed dan reject request, hapus rekening untuk privasi
    $ok1 = mysqli_query($conn, "UPDATE reservations SET status = 'confirmed' WHERE id = $id");
    $ok2 = mysqli_query($conn, "
        UPDATE request_cancellations
        SET rekening = NULL,
            status = 'rejected',
            admin_note = CONCAT(IFNULL(admin_note,''), '\nDenied cancellation by admin (', NOW(), ')')
        WHERE reservation_id = $id AND status = 'pending'
    ");
    $success = ($ok1 && $ok2);
}

if ($use_tx) {
    if ($success) mysqli_commit($conn);
    else mysqli_rollback($conn);
}

// redirect dengan indikator sederhana
if ($success) {
    header("Location: admin_dashboard.php?msg=ok");
} else {
    header("Location: admin_dashboard.php?msg=err");
}
exit;
?>
