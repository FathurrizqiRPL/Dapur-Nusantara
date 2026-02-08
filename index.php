<?php
session_start();
include "config.php";
include "navbar.php";

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/admin_dashboard.php");
    exit;
    }




// ambil sampai 4 item menu andalan terbaru
$menuAndalanSql = "SELECT * FROM menuandalan ORDER BY menu_id DESC LIMIT 4";
$menuAndalan = mysqli_query($conn, $menuAndalanSql);

// ambil data rating dari database
$ratingSql = "SELECT * FROM ratings ORDER BY created_at DESC LIMIT 10";
$ratings = mysqli_query($conn, $ratingSql);


// HITUNG RATING KESELURUHAN 
$overallRatingSql = "SELECT 
    COUNT(*) as total_ratings,
    AVG(food_rating) as avg_food,
    AVG(service_rating) as avg_service,
    (AVG(food_rating) + AVG(service_rating)) / 2 as avg_overall
    FROM ratings";
$overallResult = mysqli_query($conn, $overallRatingSql);
$overallData = mysqli_fetch_assoc($overallResult);

$totalRatings = $overallData['total_ratings'] ?? 0;
$avgFood = $overallData['avg_food'] ?? 0;
$avgService = $overallData['avg_service'] ?? 0;
$avgOverall = $overallData['avg_overall'] ?? 0;

// Format angka
$avgFood = number_format($avgFood, 1);
$avgService = number_format($avgService, 1);
$avgOverall = number_format($avgOverall, 1);

// ambil data user jika sudah login
$current_user_name = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = mysqli_query($conn, "SELECT nama FROM users WHERE id = '$user_id'");
    if ($user_row = mysqli_fetch_assoc($user_query)) {
        $current_user_name = $user_row['nama'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dapur Nusantara - Reservasi Restoran</title>
  <link rel="stylesheet" href="assets/css/style.css">
<script>
    // Fungsi untuk modal rating
    function openRatingModal() {
      document.getElementById('ratingModal').style.display = 'flex';
    }

    function closeRatingModal() {
      document.getElementById('ratingModal').style.display = 'none';
    }

    // Fungsi untuk set rating bintang - VERSI SUPER SIMPLE
    function setRating(type, rating) {
      const stars = document.querySelectorAll(`.rating-form .rating-input:nth-child(${type === 'food' ? 2 : 4}) .star`);
      const input = document.getElementById(`${type}_rating`);
      
      // Reset semua bintang
      stars.forEach(star => {
        star.classList.remove('filled');
      });
      
      // Isi bintang sampai rating yang dipilih
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.add('filled');
        }
      });
      
      input.value = rating;
    }

    // Fungsi untuk hover effect - VERSI SIMPLE
    function initStarHover() {
      const ratingInputs = document.querySelectorAll('.rating-input');
      
      ratingInputs.forEach(input => {
        const stars = input.querySelectorAll('.star');
        const type = input.previousElementSibling.textContent.includes('Makanan') ? 'food' : 'service';
        
        stars.forEach((star, index) => {
          const starIndex = index + 1;
          
          // Hover masuk - isi bintang sampai yang dihover
          star.addEventListener('mouseenter', () => {
            stars.forEach((s, i) => {
              if (i < starIndex) {
                s.style.color = '#ffdb70';
              } else {
                s.style.color = '#ddd';
              }
            });
          });
          
          // Hover keluar - kembalikan ke state asli
          star.addEventListener('mouseleave', () => {
            const currentRating = document.getElementById(`${type}_rating`).value;
            stars.forEach((s, i) => {
              if (currentRating > 0 && i < currentRating) {
                s.style.color = '#ffc107';
              } else {
                s.style.color = '#ddd';
              }
            });
          });
        });
      });
    }

    // Fungsi untuk scroll carousel
    function scrollCarousel(direction) {
      const carousel = document.querySelector('.rating-carousel');
      const scrollAmount = 300;
      
      if (direction === 'left') {
        carousel.scrollLeft -= scrollAmount;
      } else {
        carousel.scrollLeft += scrollAmount;
      }
    }

    // Tutup modal jika klik di luar
    window.onclick = function(event) {
      const modal = document.getElementById('ratingModal');
      if (event.target === modal) {
        closeRatingModal();
      }
    }

    // Inisialisasi saat modal dibuka
    document.addEventListener('DOMContentLoaded', function() {
      const ratingModal = document.getElementById('ratingModal');
      
      const modalObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            if (ratingModal.style.display === 'flex') {
              initStarHover();
            }
          }
        });
      });
      
      modalObserver.observe(ratingModal, {
        attributes: true,
        attributeFilter: ['style']
      });
    });

    // Tampilkan pesan sukses jika ada
    <?php if (isset($_GET['rating_success'])): ?>
      alert('Terima kasih! Ulasan Anda telah berhasil dikirim.');
      window.history.replaceState({}, document.title, window.location.pathname);
    <?php endif; ?>
  </script>
</head>
<body>
  <!-- Hero -->
  <section class="hero" id="home">
    <h1>Selamat Datang di Dapur Nusantara</h1>
    <p>Cita rasa autentik dari berbagai daerah di Indonesia</p>
  </section>

  <!-- ===== ABOUT SECTION ===== -->
<section class="about-section">
    <div class="about-card">

        <!-- Bagian Gambar -->
        <div class="about-image">
            <img src="assets/img/restoran.jpg" alt="Tentang Dapur Nusantara">
        </div>

        <!-- Bagian Teks -->
        <div class="about-content">
            <h2>Tentang Dapur Nusantara</h2>
            <p>
                Dapur Nusantara hadir sebagai tempat yang menyajikan cita rasa autentik dari 
                berbagai daerah di Indonesia. Kami berkomitmen menjaga keaslian bumbu, teknik 
                memasak tradisional, dan kualitas bahan agar setiap hidangan membawa pengalaman 
                kuliner yang hangat dan penuh kenangan.
            </p>

            <div class="about-info">
                <h3>Jam Operasional</h3>
                <p>Setiap Hari — <strong>10.00 Pagi hingga 21.00 Malam</strong></p>

                <h3>Lokasi</h3>
                <p>
                    Jl. Anggrek Raya No. 18,  
                    Kompleks Kuliner Surya Mandala,  
                    Jakarta Timur
                </p>
            </div>
        </div>

    </div>
</section>

  <!-- Menu -->
  <section class="menu-section" id="menu">
       <h2 class="menu-utama-title"style="text-align:center; margin-top:20px;">Menu Andalan Kami</h2>
    <div class="menu-andalan">
      <div class="menu-andalan-items">
        <?php if ($menuAndalan && mysqli_num_rows($menuAndalan) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($menuAndalan)): ?>
            <?php
              $imgFile = $row['gambar'] ?? '';
              $imgPathFS = __DIR__ . '/assets/img/' . $imgFile;
              $imgWebPath = 'assets/img/' . $imgFile;
            ?>
            <div class="item">
              <?php if (!empty($imgFile) && file_exists($imgPathFS)): ?>
                <div class="thumb"><img src="<?= htmlspecialchars($imgWebPath) ?>" alt="<?= htmlspecialchars($row['nama_menu']) ?>"></div>
              <?php else: ?>
                <div class="thumb no-thumb">No Image</div>
              <?php endif; ?>
              <h4 class="item-title"><?= htmlspecialchars($row['nama_menu']) ?></h4>
              <p class="item-desc"><?= htmlspecialchars($row['deskripsi_menu']) ?></p>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="text-align:center; width:100%;">Belum ada menu andalan.</p>
        <?php endif; ?>
      </div>
    </div>
  <div class="menu-utama-container">
    <h2 class="menu-utama-title">Menu Kami</h2>
    <div class="menu-utama">
      <div class="menu-item">
        <h3>Makanan</h3>
        <a href="assets/dokumen/menuutama.pdf" target="_blank" rel="noopener noreferrer">
          <img src="assets/img/menuutama.jpg" alt="Menu Utama">
        </a>
      </div>
      <div class="menu-item">
        <h3>Minuman</h3>
        <a href="assets/dokumen/menuminuman.pdf" target="_blank" rel="noopener noreferrer">
          <img src="assets/img/menuminuman.jpg" alt="Menu Minuman">
        </a>
      </div>
    </div>
  </div>

  </section>
  <!-- Rating Section -->
  <section class="rating-section" id="rating">
    <div class="rating-container">
      <!-- RATING OVERALL - DITAMBAHKAN -->
      <div class="rating-overall">
        <div class="overall-header">
          <h2 class="rating-title">Rating Dapur Nusantara</h2>
          <div class="overall-stats">
            <div class="overall-score">
              <div class="score-number"><?= $avgOverall ?></div>
              <div class="score-stars">
                <div class="stars">
                  <?php 
                  $overallRounded = round($avgOverall);
                  for($i = 1; $i <= 5; $i++): 
                  ?>
                    <span class="star <?= $i <= $overallRounded ? 'filled' : '' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <div class="score-text">dari 5.0</div>
              </div>
            </div>
            <div class="rating-details">
              <div class="detail-item">
                <span class="detail-label">Makanan</span>
                <span class="detail-stars">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= round($avgFood) ? 'filled' : '' ?>">★</span>
                  <?php endfor; ?>
                  <span class="detail-score">(<?= $avgFood ?>)</span>
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Pelayanan</span>
                <span class="detail-stars">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= $i <= round($avgService) ? 'filled' : '' ?>">★</span>
                  <?php endfor; ?>
                  <span class="detail-score">(<?= $avgService ?>)</span>
                </span>
              </div>
              <div class="total-ratings">
                <span class="total-text">Berdasarkan <?= $totalRatings ?> ulasan</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="rating-header">
        <h3 class="rating-subtitle">Apa Kata Pelanggan Kami</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
          <button class="btn-tulis-ulasan" onclick="openRatingModal()">Tulis Ulasan</button>
        <?php else: ?>
          <a href="login.php" class="btn-tulis-ulasan">Login untuk Beri Ulasan</a>
        <?php endif; ?>
      </div>
      
      <div class="rating-carousel-container">
        <button class="carousel-btn prev" onclick="scrollCarousel('left')">‹</button>
        <div class="rating-carousel">
          <?php if ($ratings && mysqli_num_rows($ratings) > 0): ?>
            <?php while($rating = mysqli_fetch_assoc($ratings)): ?>
              <div class="rating-card">
                <div class="user-info">
                  <div class="user-avatar"><?= strtoupper(substr($rating['user_name'], 0, 1)) ?></div>
                  <div class="user-details">
                    <h4><?= htmlspecialchars($rating['user_name']) ?></h4>
                    <div class="rating-date"><?= date('d M Y', strtotime($rating['created_at'])) ?></div>
                  </div>
                </div>
                <div class="rating-stars">
                  <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                      <span class="star <?= $i <= $rating['food_rating'] ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                  </div>
                  <div class="rating-category">
                    <span>Makanan</span>
                    <span><?= $rating['food_rating'] ?>/5</span>
                  </div>
                  <div class="rating-category">
                    <span>Pelayanan</span>
                    <span><?= $rating['service_rating'] ?>/5</span>
                  </div>
                </div>
                <p class="user-comment">"<?= htmlspecialchars($rating['comment']) ?>"</p>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-ratings">
              <p>Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>
            </div>
          <?php endif; ?>
        </div>
        <button class="carousel-btn next" onclick="scrollCarousel('right')">›</button>
      </div>
    </div>
  </section>
  <!-- Rating Modal -->
  <div class="rating-modal" id="ratingModal">
    <div class="rating-modal-content">
      <span class="close-modal" onclick="closeRatingModal()">&times;</span>
      <h3 style="margin-bottom: 20px;">Beri Ulasan untuk Dapur Nusantara</h3>
      <form class="rating-form" action="rating_submit.php" method="POST">
        <input type="hidden" name="user_name" value="<?= htmlspecialchars($current_user_name) ?>">
        
        <label>Rating Makanan:</label>
        <div class="rating-input">
          <?php for($i = 1; $i <= 5; $i++): ?>
            <span class="star" onclick="setRating('food', <?= $i ?>)">★</span>
          <?php endfor; ?>
          <input type="hidden" name="food_rating" id="food_rating" required>
        </div>
        
        <label>Rating Pelayanan:</label>
        <div class="rating-input">
          <?php for($i = 1; $i <= 5; $i++): ?>
            <span class="star" onclick="setRating('service', <?= $i ?>)">★</span>
          <?php endfor; ?>
          <input type="hidden" name="service_rating" id="service_rating" required>
        </div>
        
        <label for="comment">Komentar:</label>
        <textarea name="comment" id="comment" placeholder="Bagikan pengalaman Anda..." required></textarea>
        
        <button type="submit">Kirim Ulasan</button>
      </form>
    </div>
  </div>

  <section class="reservation-section" id="reservation">
    <h2>Reservasi Meja</h2>
    <p>Pesan meja Anda sekarang dan nikmati hidangan lezat kami!</p>
    <a href="reservasi.php" class="btn">Buat Reservasi</a>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-container">

        <div class="footer-section">
            <h3>Contact Us</h3>
            <p>Email: <a href="mailto:info@dapurnusantara.com">info@dapurnusantara.com</a></p>
            <p>Telepon: <a href="tel:+62081234567890">+62 812-3456-7890</a></p>
           
        </div>

        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="footer-social">
                <a href="#">Instagram</a>
                <a href="#">Facebook</a>
                <a href="#">TikTok</a>
            </div>
        </div>

        <div class="footer-section">
            <h3>Support</h3>
            <ul>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Kebijakan Privasi</a></li>
                <li><a href="#">Syarat & Ketentuan</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© 2025 Dapur Nusantara. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
