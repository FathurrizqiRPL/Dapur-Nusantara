<?php
session_start();
header('Content-Type: application/json');
include "config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Validasi: pastikan reservasi milik user yang sedang login dan status pending
$check = mysqli_query($conn, "
    SELECT * FROM reservations 
    WHERE id = $id AND user_id = {$_SESSION['user_id']} AND status = 'pending'
");

if (!$check || mysqli_num_rows($check) === 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found or not pending']);
    exit;
}

$res = mysqli_fetch_assoc($check);
$createdAt = strtotime($res['created_at']);
$now = time();
$elapsedSec = $now - $createdAt;

// Hanya auto-cancel jika sudah lebih dari 5 menit
if ($elapsedSec < (5 * 60)) {
    echo json_encode(['success' => false, 'message' => '5 minutes have not passed yet']);
    exit;
}

// Update status menjadi cancelled
$update = mysqli_query($conn, "UPDATE reservations SET status = 'cancelled' WHERE id = $id");

if ($update) {
    echo json_encode(['success' => true, 'message' => 'Reservasi dibatalkan otomatis']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation']);
}
exit;
?>
