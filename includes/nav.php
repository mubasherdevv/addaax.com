<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="elocanto-header">
  <div class="container-custom header-container">
    <!-- Logo -->
    <a href="index.php" class="logo-wrap">
      <div class="logo-icon-box">
        <i class="fas fa-store"></i>
      </div>
      <span class="logo-text">Add<span>aax</span></span>
    </a>
    
    <!-- Navigation Links -->
    <nav class="hidden-md">
      <ul class="nav-links">
        <li><a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
        <li><a href="products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">Explore</a></li>
        <li><a href="categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
        <li><a href="contact.php" class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Support</a></li>
      </ul>
    </nav>

    <!-- Header Actions -->
    <div class="header-actions">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="auth/dashboard.php" class="user-account-link">
          <div class="user-avatar-small">
            <i class="fas fa-user"></i>
          </div>
          <span class="hidden-sm">Account</span>
        </a>
      <?php else: ?>
        <a href="auth/login.php" class="login-link">
          <i class="far fa-user-circle"></i>
          <span class="hidden-sm">Login</span>
        </a>
      <?php endif; ?>

      <a href="post-ad.php" class="btn-post-ad">
        <i class="fas fa-plus-circle"></i>
        <span>Post Ad</span>
      </a>
    </div>
  </div>
</header>
