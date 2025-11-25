<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $food_rating = intval($_POST['food_rating']);
    $service_rating = intval($_POST['service_rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Validasi rating
    if ($food_rating < 1 || $food_rating > 5 || $service_rating < 1 || $service_rating > 5) {
        die("Rating harus antara 1-5");
    }

    // Cek apakah user sudah memberikan rating sebelumnya
    $check_query = "SELECT id FROM ratings WHERE user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Update rating yang sudah ada
        $query = "UPDATE ratings SET user_name = '$user_name', food_rating = '$food_rating', service_rating = '$service_rating', comment = '$comment', created_at = NOW() WHERE user_id = '$user_id'";
    } else {
        // Insert rating baru
        $query = "INSERT INTO ratings (user_id, user_name, food_rating, service_rating, comment) VALUES ('$user_id', '$user_name', '$food_rating', '$service_rating', '$comment')";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: index.php?rating_success=1");
        exit;
    } else {
        die("Error: " . mysqli_error($conn));
    }
}
?>  