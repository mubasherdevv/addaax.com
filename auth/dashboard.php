<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/layout_functions.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

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

// Get Ads
$my_ads = [];
if ($active_tab === 'ads') {
    $sql_ads = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql_ads);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $my_ads[] = $row; }
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

renderHeader('User Dashboard | ADAAX Premium', 'dashboard');
?>

    <main class="container-wide dashboard-page">
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="dash-user-profile">
                    <div class="dash-avatar">
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--accent-gold); color:#000; font-size:32px; font-weight:900;">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                    </div>
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
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
                <?php if ($message): ?>
                    <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid #22c55e; color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($active_tab === 'overview'): ?>
                    <div class="dash-section-header">
                        <h2>Dashboard Overview</h2>
                        <a href="/main/post-ad.php" class="post-ad-btn" style="padding: 10px 20px; font-size: 13px;">+ New Ad</a>
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
                                            <td>
                                                <div class="dash-ad-item">
                                                    <img src="<?php echo !empty($ad['image']) ? '/main/' . $ad['image'] : '/main/images/placeholder.png'; ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                                                    <div>
                                                        <a href="/main/product_details.php?id=<?php echo $ad['id']; ?>" class="dash-ad-title"><?php echo htmlspecialchars($ad['name']); ?></a>
                                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo $ad['city'] ?? 'Location N/A'; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($ad['category_name'] ?? 'Escorts'); ?></td>
                                            <td>
                                                <span class="dash-status status-<?php echo $ad['status'] == 1 ? 'active' : ($ad['status'] == 0 ? 'pending' : 'expired'); ?>">
                                                    <?php echo $ad['status'] == 1 ? 'Active' : ($ad['status'] == 0 ? 'Pending' : 'Expired'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($ad['created_at'])); ?></td>
                                            <td>
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
                        <a href="/main/post-ad.php" class="post-ad-btn" style="padding: 10px 20px; font-size: 13px;">+ Post New Ad</a>
                    </div>
                    <div class="dash-table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Ad Detail</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($my_ads)): ?>
                                    <tr><td colspan="5" style="text-align:center; padding: 40px; color: var(--text-muted);">You haven't posted any ads yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($my_ads as $ad): ?>
                                        <tr>
                                            <td>
                                                <div class="dash-ad-item">
                                                    <img src="<?php echo !empty($ad['image']) ? '/main/' . $ad['image'] : '/main/images/placeholder.png'; ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                                                    <div>
                                                        <a href="/main/product_details.php?id=<?php echo $ad['id']; ?>" class="dash-ad-title"><?php echo htmlspecialchars($ad['name']); ?></a>
                                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo $ad['city'] ?? 'Location N/A'; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($ad['category_name'] ?? 'Escorts'); ?></td>
                                            <td>
                                                <span class="dash-status status-<?php echo $ad['status'] == 1 ? 'active' : ($ad['status'] == 0 ? 'pending' : 'expired'); ?>">
                                                    <?php echo $ad['status'] == 1 ? 'Active' : ($ad['status'] == 0 ? 'Pending' : 'Expired'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($ad['views'] ?? 0); ?></td>
                                            <td>
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

                <?php elseif ($active_tab === 'profile'): ?>
                    <div class="dash-section-header">
                        <h2>Profile Settings</h2>
                    </div>
                    <form class="auth-form" style="max-width: 600px;">
                        <div class="responsive-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>First Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Last Name</label>
                                <input type="text" placeholder="Last Name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" disabled style="opacity: 0.5; cursor: not-allowed;">
                        </div>
                        <button type="submit" class="auth-btn" style="width: auto; padding: 0 40px;">Update Profile</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>

<?php
renderFooter();
?>