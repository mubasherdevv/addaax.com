<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get wishlist count
$wishlist_count = getWishlistCount($user_id);
?>

<div class="sidebar">
    <div class="sidebar-user">
        <div class="user-avatar">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="../images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" id="sidebar-profile-img">
            <?php else: ?>
                <?php 
                $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                echo $initials;
                ?>
            <?php endif; ?>
        </div>
        <div class="user-details">
            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="wishlist.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">
                <i class="bi bi-heart"></i>
                <span>Wishlist</span>
                <?php if ($wishlist_count > 0): ?>
                    <span class="badge"><?php echo $wishlist_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
            </a>
        </li>

        <li>
            <a href="change_password.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'active' : ''; ?>">
                <i class="bi bi-key"></i>
                <span>Change Password</span>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: 20px;
    position: fixed;
    height: calc(100vh - 40px);
    overflow-y: auto;
}

.sidebar-user {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #4d61d8;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    margin-right: 15px;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-details h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

.user-details p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #666;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.sidebar-menu a:hover {
    background: #f8f9fa;
    color: #4d61d8;
}

.sidebar-menu a.active {
    background: #4d61d8;
    color: #fff;
}

.sidebar-menu i {
    margin-right: 10px;
    font-size: 18px;
}

.sidebar-menu .badge {
    background: #4d61d8;
    color: #fff;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 12px;
    margin-left: auto;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        height: auto;
        margin-bottom: 20px;
    }
}
</style> 