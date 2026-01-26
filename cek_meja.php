<?php
include "config.php";

$tanggal = $_GET['tanggal'];
$jam_mulai = $_GET['jam_mulai'];

$jam_selesai = date("H:i:s", strtotime($jam_mulai) + 90 * 60);

$query = mysqli_query($conn, "SELECT meja_id FROM reservations 
    WHERE tanggal='$tanggal'
    AND (
        (jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') OR
        (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai')
    )
    AND status != 'cancelled' AND status != 'refunded'");

$disable = [];

while ($row = mysqli_fetch_assoc($query)) {
    $disable[] = $row['meja_id'];
}

echo json_encode($disable);
