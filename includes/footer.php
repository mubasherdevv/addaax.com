<?php
// Fetch footer data
$footer_cats = $conn->query("SELECT * FROM categories WHERE status = 1 LIMIT 6")->fetch_all(MYSQLI_ASSOC);
?>
<footer class="footer-elocanto">
  <div class="container-custom">
    <div class="footer-grid">
      <!-- Brand & Mission -->
      <div class="footer-col">
        <a href="index.php" class="footer-logo">
          <div class="logo-box">A</div>
          <span class="logo-text">Add<span>aax</span></span>
        </a>
        <p class="footer-desc">
          <?php echo isset($WEBSITE_SETTINGS['default_meta_description']) ? htmlspecialchars($WEBSITE_SETTINGS['default_meta_description']) : "Pakistan's most trusted classified marketplace. Buy, sell, and find everything from cars and property to jobs and services."; ?>
        </p>
        <div class="footer-socials">
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- Popular Categories -->
      <div class="footer-col">
        <h4 class="footer-title">Popular Categories</h4>
        <ul class="footer-links">
          <?php foreach ($footer_cats as $cat): ?>
            <li>
              <a href="products.php?category=<?php echo $cat['id']; ?>">
                <span>→</span> <?php echo htmlspecialchars($cat['name']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Top Cities -->
      <div class="footer-col">
        <h4 class="footer-title">Top Cities</h4>
        <ul class="footer-links">
          <?php 
          // If cities table doesn't exist, use common Pakistan cities
          $pak_cities = ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan'];
          foreach ($pak_cities as $city): 
          ?>
            <li>
              <a href="products.php?city=<?php echo urlencode($city); ?>">
                <span>→</span> <?php echo $city; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Contact Us -->
      <div class="footer-col">
        <h4 class="footer-title">Contact Us</h4>
        <ul class="footer-contact">
          <li>
            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
            <a href="tel:+923001234567">+92 300 123 4567</a>
          </li>
          <li>
            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
            <a href="mailto:contactadmin@addaax.com">contactadmin@addaax.com</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-disclaimer">
      <p>
        <strong>Legal Disclaimer:</strong> The content on our platform is intended only for an adult audience. All advertisements on our pages have been posted independently by the advertiser and are therefore under their exclusive responsibility. Addaax is not responsible for the truthfulness, legality, or respect for property rights of such content.
      </p>
    </div>

    <div class="footer-bottom">
      <p class="copyright">
        <span class="brand">Addaax</span> © <?php echo date('Y'); ?>
      </p>
      <div class="bottom-links">
        <a href="pages/about.php">About</a>
        <a href="pages/contact.php">Contact</a>
        <a href="pages/terms.php">Terms</a>
        <a href="pages/privacy.php">Privacy</a>
        <a href="pages/anti-scam.php">Anti-Scam</a>
        <a href="pages/copyright-policy.php">Copyright</a>
      </div>
    </div>
  </div>
</footer>
<!-- Bottom Navigation Bar (Mobile) -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="bottom-nav">
  <a href="index.php" class="bottom-nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
    <i class="fas fa-home"></i>
    <span>Home</span>
  </a>
  <a href="products.php" class="bottom-nav-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
    <i class="fas fa-search"></i>
    <span>Explore</span>
  </a>
  <a href="post-ad.php" class="bottom-nav-item post-ad-mobile">
    <div class="plus-icon"><i class="fas fa-plus"></i></div>
  </a>
  <a href="categories.php" class="bottom-nav-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
    <i class="fas fa-th-large"></i>
    <span>Categories</span>
  </a>
  <a href="auth/login.php" class="bottom-nav-item <?php echo strpos($_SERVER['REQUEST_URI'], 'auth/') !== false ? 'active' : ''; ?>">
    <i class="fas fa-user-circle"></i>
    <span>Account</span>
  </a>
</div>
