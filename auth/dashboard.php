<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/layout_functions.php';
require_once '../includes/image_utils.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Auto-migrate: ensure profile_pic column exists to prevent SQL errors
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) DEFAULT NULL AFTER phone");

// Get User Stats
$sql_total = "SELECT COUNT(*) as count FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_ads = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$sql_active = "SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND status = 1";
$stmt = $conn->prepare($sql_active);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_ads = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

$sql_pending = "SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND status = 0";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_ads = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get Ads with Pagination
$my_ads = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($active_tab === 'ads') {
    $sql_ads = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql_ads);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $my_ads[] = $row; }
    
    $total_pages = ceil($total_ads / $limit);
}

$recent_ads = [];
if ($active_tab === 'overview') {
    $sql_recent = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC LIMIT 5";
    $stmt = $conn->prepare($sql_recent);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $recent_ads[] = $row; }
}

$message = $_GET['msg'] ?? '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_tab === 'profile') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Handle Profile Picture Upload
    $profile_pic = $user_data['profile_pic'] ?? null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $temp_path = $_FILES['profile_pic']['tmp_name'];
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $target_path = $upload_dir . $new_filename;
            
            // Compress and convert to WebP (no watermark for profile pics)
            $final_name = compressImage($temp_path, $target_path, 80, false);
            if ($final_name) {
                $profile_pic = 'uploads/profiles/' . $final_name;
            }
        }
    }

    if (!empty($first_name) && !empty($last_name)) {
        $update_sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, profile_pic = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $first_name, $last_name, $phone, $profile_pic, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            header("Location: dashboard.php?tab=profile&msg=Profile updated successfully!");
            exit;
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
    } else {
        $message = "First and Last Name are required.";
    }
}

// Get Latest User Details
$sql_user = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($sql_user);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

renderHeader('User Dashboard | ADDAAX', 'dashboard');
?>
<link rel="stylesheet" href="/css/dashboard-responsive.css">

    <div class="dash-sidebar-overlay" id="dashOverlay"></div>

    <main class="container-wide dashboard-page">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="dash-user-profile">
                    <div class="dash-avatar">
                        <?php if (!empty($user_data['profile_pic'])): ?>
                            <img src="/<?php echo htmlspecialchars($user_data['profile_pic']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--accent-gold); color:#fff; font-size:32px; font-weight:900; border-radius:50%;">
                                <?php echo strtoupper(substr($user_data['first_name'] ?? $user_name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')); ?></h3>
                    <p>Verified Member</p>
                </div>

                <ul class="dash-menu">
                    <li><a href="dashboard.php?tab=overview" class="<?php echo $active_tab == 'overview' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="dashboard.php?tab=ads" class="<?php echo $active_tab == 'ads' ? 'active' : ''; ?>"><i class="fas fa-ad"></i> My Ads</a></li>
                    <li><a href="dashboard.php?tab=profile" class="<?php echo $active_tab == 'profile' ? 'active' : ''; ?>"><i class="fas fa-user-edit"></i> Profile Settings</a></li>
                    <li style="margin-top: 20px; border-top: 1px solid var(--glass-border); padding-top: 10px;">
                        <a href="logout.php" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </aside>

            <!-- Content Area -->
            <section class="dash-content">
                <!-- Mobile Toggle -->
                <div class="dash-mobile-toggle" id="dashToggle">
                    <i class="fas fa-bars"></i>
                    <span>Dashboard Menu</span>
                </div>



                <?php if ($active_tab === 'overview'): ?>
                    <div class="dash-section-header">
                        <h2>Dashboard Overview</h2>
                        <a href="/post-ad.php" class="post-ad-btn" style="padding: 10px 20px; font-size: 13px;">+ New Ad</a>
                    </div>

                    <div class="dash-stats-grid">
                        <div class="dash-stat-card">
                            <i class="fas fa-layer-group"></i>
                            <div class="value"><?php echo $total_ads; ?></div>
                            <div class="label">Total Ads</div>
                        </div>
                        <div class="dash-stat-card">
                            <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                            <div class="value"><?php echo $active_ads; ?></div>
                            <div class="label">Active Ads</div>
                        </div>
                        <div class="dash-stat-card">
                            <i class="fas fa-clock" style="color: #eab308;"></i>
                            <div class="value"><?php echo $pending_ads; ?></div>
                            <div class="label">Pending</div>
                        </div>
                    </div>

                    <div class="dash-section-header" style="margin-top: 40px;">
                        <h2>Recent Ads</h2>
                        <a href="dashboard.php?tab=ads" style="color: var(--accent-gold); font-size: 13px; font-weight: 700; text-decoration: none;">View All</a>
                    </div>

                    <div class="dash-table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Ad Detail</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_ads)): ?>
                                    <tr><td colspan="5" style="text-align:center; padding: 40px; color: var(--text-muted);">No ads found. Post your first ad today!</td></tr>
                                <?php else: ?>
                                    <?php foreach($recent_ads as $ad): ?>
                                        <tr>
                                            <td data-label="Ad Detail">
                                                <div class="dash-ad-item">
                                                    <img src="<?php echo !empty($ad['image']) ? '/' . $ad['image'] : '/images/placeholder.png'; ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;" loading="lazy" decoding="async">
                                                    <div>
                                                        <a href="<?php echo getProductUrl($ad['id'], $ad['name']); ?>" class="dash-ad-title">
                                                            <?php echo htmlspecialchars($ad['name']); ?>
                                                            <?php if(isset($ad['is_featured']) && $ad['is_featured']): ?>
                                                                <span style="font-size: 9px; background: var(--accent-gold); color: #000; padding: 2px 5px; border-radius: 4px; margin-left: 5px; font-weight: 800;">FEATURED</span>
                                                            <?php endif; ?>
                                                        </a>
                                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo $ad['city'] ?? 'Location N/A'; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Category"><?php echo htmlspecialchars(formatCategoryName($ad['category_name'] ?? '')); ?></td>
                                            <td data-label="Status">
                                                <span class="dash-status status-<?php echo $ad['status'] == 1 ? 'active' : ($ad['status'] == 0 ? 'pending' : ($ad['status'] == 2 ? 'hidden' : 'expired')); ?>">
                                                    <?php echo $ad['status'] == 1 ? 'Active' : ($ad['status'] == 0 ? 'Pending' : ($ad['status'] == 2 ? 'Hidden' : 'Expired')); ?>
                                                </span>
                                            </td>
                                            <td data-label="Date"><?php echo date('M d, Y', strtotime($ad['created_at'])); ?></td>
                                            <td data-label="Actions">
                                                <div class="dash-actions">
                                                    <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="dash-action-btn" title="Edit" style="color: var(--accent-gold);"><i class="fas fa-edit"></i></a>
                                                    <a href="delete_ad.php?id=<?php echo $ad['id']; ?>" class="dash-action-btn" title="Delete" style="color: #ef4444;" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($active_tab === 'ads'): ?>
                    <div class="dash-section-header">
                        <h2>My Advertisements</h2>
                        <a href="/post-ad.php" class="post-ad-btn" style="padding: 10px 20px; font-size: 13px;">+ Post New Ad</a>
                    </div>
                    <div class="dash-ads-list">
                        <?php if (empty($my_ads)): ?>
                            <div style="text-align:center; padding: 60px; background: var(--glass); border-radius: 20px; border: 1px solid var(--glass-border);">
                                <i class="fas fa-ad" style="font-size: 40px; color: var(--accent-gold); margin-bottom: 20px; display: block;"></i>
                                <p style="color: var(--text-muted);">You haven't posted any ads yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($my_ads as $ad): ?>
                                <div class="dash-ad-card">
                                    <div class="dash-ad-card-main">
                                        <a href="<?php echo getProductUrl($ad['id'], $ad['name']); ?>" class="dash-ad-img">
                                            <img src="<?php echo !empty($ad['image']) ? '/' . $ad['image'] : '/images/placeholder.png'; ?>" alt="" loading="lazy" decoding="async">
                                            <span class="dash-status-badge status-<?php echo $ad['status'] == 1 ? 'active' : ($ad['status'] == 0 ? 'pending' : 'expired'); ?>">
                                                <?php echo $ad['status'] == 1 ? 'Active' : ($ad['status'] == 0 ? 'Pending' : 'Expired'); ?>
                                            </span>
                                        </a>
                                        <div class="dash-ad-info">
                                            <div class="dash-ad-header">
                                                <h4><a href="<?php echo getProductUrl($ad['id'], $ad['name']); ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($ad['name']); ?></a></h4>
                                                <p class="dash-ad-short-desc"><?php echo mb_strimwidth(strip_tags($ad['description'] ?? ''), 0, 100, "..."); ?></p>
                                            </div>
                                            <div class="dash-ad-price-row">
                                                <span class="dash-ad-price">PKR <?php echo number_format($ad['price'] ?? 0); ?></span>
                                                <?php if(isset($ad['is_featured']) && $ad['is_featured']): ?>
                                                    <span class="dash-featured-tag">FEATURED</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dash-ad-footer">
                                        <div class="dash-ad-actions">
                                            <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="dash-ad-btn edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="delete_ad.php?id=<?php echo $ad['id']; ?>" class="dash-ad-btn delete" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></a>
                                        </div>
                                        <div class="dash-ad-meta">
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($ad['views'] ?? 0); ?> Views</span>
                                            <span><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($ad['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="margin-top: 30px; display: flex; justify-content: center; gap: 10px;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="dashboard.php?tab=ads&page=<?php echo $i; ?>" 
                                   style="padding: 8px 15px; border-radius: 8px; background: <?php echo $page == $i ? 'var(--accent-gold)' : 'var(--glass-bg)'; ?>; 
                                          color: <?php echo $page == $i ? '#000' : 'var(--text-main)'; ?>; text-decoration: none; font-weight: 700; border: 1px solid var(--glass-border);">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                <?php elseif ($active_tab === 'profile'): ?>
                    <div class="dash-section-header">
                        <h2>Profile Settings</h2>
                    </div>
                    <form class="auth-form" method="POST" enctype="multipart/form-data" style="max-width: 600px;">
                        <div class="responsive-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Profile Picture</label>
                            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
                                <div id="profile-preview" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 2px solid var(--accent-gold); background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                    <?php if(!empty($user_data['profile_pic'])): ?>
                                        <img src="/<?php echo htmlspecialchars($user_data['profile_pic']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user" style="font-size: 30px; color: #cbd5e1;"></i>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <?php if(!empty($user_data['profile_pic'])): ?>
                                <p style="font-size: 12px; color: var(--text-muted);">Current: <?php echo basename($user_data['profile_pic']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user_data['email'] ?? $_SESSION['user_email']); ?>" disabled style="opacity: 0.5; cursor: not-allowed;">
                        </div>
                        <button type="submit" class="auth-btn" style="width: auto; padding: 0 40px;">Update Profile</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('profile-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        const dashToggle = document.getElementById('dashToggle');
        const dashSidebar = document.querySelector('.dashboard-sidebar');
        const dashOverlay = document.getElementById('dashOverlay');

        if (dashToggle && dashSidebar && dashOverlay) {
            dashToggle.addEventListener('click', () => {
                dashSidebar.classList.add('active');
                dashOverlay.classList.add('active');
                document.body.classList.add('no-scroll');
            });

            dashOverlay.addEventListener('click', () => {
                dashSidebar.classList.remove('active');
                dashOverlay.classList.remove('active');
                document.body.classList.remove('no-scroll');
            });
        }
    </script>

<?php
renderFooter();
?>