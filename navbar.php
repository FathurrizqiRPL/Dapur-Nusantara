<nav class="navbar">
  <div class="nav-container">

    <div class="nav-left">
      <img src="../dapur_nusantara/assets/img/logo2hitam.png" class="logo" alt="logo">
      <a href="index.php#home">Home</a>
      <a href="reservasi.php">Reservasi</a>
      <a href="riwayat.php">Riwayat</a>
    </div>

    <div class="nav-right">
      <?php if(isset($_SESSION['user_id'])): ?>
        <span class="nav-user">Hi, <?= $_SESSION['nama'] ?></span>
        <a href="logout.php" class="logout">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Daftar</a>
      <?php endif; ?>
    </div>

  </div>
</nav>
