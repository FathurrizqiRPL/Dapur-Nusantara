<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah dipakai
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama','$email','$password','user')";
        if (mysqli_query($conn, $query)) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Gagal mendaftar, coba lagi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - Dapur Nusantara</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
     .login-link{
      color: #007bff;
      text-decoration: underline;
    }

    .login-link:hover{
      color: #0056b3;
    }
  </style>
</head>
<body class="register-body">
  <img src="../dapur_nusantara/assets/img/logo2putih2.png" alt="">
  <div class="auth-container">
    <form class="auth-form" method="POST" action="">
      <h2>Daftar</h2>

      <?php if(isset($error)): ?>
        <p style="color:red; text-align:center;"><?= $error ?></p>
      <?php endif; ?>

      <input type="text" name="nama" placeholder="Nama Lengkap" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Daftar</button>

      <p style="text-align:center; margin-top:10px;">
        Sudah punya akun? <a href="login.php" class="login-link">Login</a>
      </p>
    </form>
  </div>
</body>
</html>
