<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Dapur Nusantara</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .register-link{
      color: #007bff;
      text-decoration: underline;
    }

    .register-link:hover{
      color: #0056b3;
    }

  </style>
</head>
<body class="login-body">
  <img src="../dapur_nusantara/assets/img/logo2putih2.png" alt="">
  <div class="auth-container">
    <form class="auth-form" method="POST" action="">
      <h2>Login</h2>

      <?php if(isset($error)): ?>
        <p style="color:red; text-align:center;"><?= $error ?></p>
      <?php endif; ?>

      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>

      <p style="text-align:center; margin-top:10px;">
        Belum punya akun? <a href="register.php" class="register-link">Daftar</a>
      </p>
    </form>
  </div>
</body>
</html>
