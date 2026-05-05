<div class="admin-sidebar">
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <a href="wishlist.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'wishlist.php' ? 'active' : ''; ?>">
        <i class="fas fa-heart"></i> Wishlist
    </a>
    <a href="user_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'user_profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i> My Profile
    </a>
    <a href="logout.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'logout.php' ? 'active' : ''; ?>">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div> 