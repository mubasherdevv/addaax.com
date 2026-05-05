<?php
require_once 'session.php';
require_once 'db_connect.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$ad_id = intval($_GET['id']);

// Check ownership
$check_sql = $is_admin ? "SELECT * FROM products WHERE id = ?" : "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($check_sql);
if ($is_admin) {
    $stmt->bind_param("i", $ad_id);
} else {
    $stmt->bind_param("ii", $ad_id, $user_id);
}
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if (!$ad) {
    header("Location: dashboard.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $update_sql = "UPDATE products SET name = ?, price = ?, description = ?, phone = ?, updated_at = NOW() WHERE id = ?";
    $up_stmt = $conn->prepare($update_sql);
    $up_stmt->bind_param("sdssi", $title, $price, $description, $phone, $ad_id);
    
    if ($up_stmt->execute()) {
        header("Location: dashboard.php?tab=ads&msg=Ad updated successfully");
        exit;
    }
}

$cities = $conn->query("SELECT * FROM cities WHERE status = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ad | Adaax Premium</title>
    <link rel="stylesheet" href="/main/css/modern-directory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        .post-ad-wrapper { padding-top: 140px; padding-bottom: 160px; min-height: 100vh; background-color: #000; }
        .post-form-card { max-width: 850px; margin: 0 auto; background: rgba(245, 233, 200, 0.02); backdrop-filter: blur(40px); border: 1px solid var(--glass-border); border-radius: var(--radius); padding: 50px; box-shadow: 0 40px 100px rgba(0,0,0,0.8); }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: var(--white); margin-bottom: 10px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 12px; color: var(--white); outline: none; transition: 0.3s; }
        .form-group input:focus { border-color: var(--accent-gold); background: rgba(255,255,255,0.08); }
    </style>
</head>
<body>
    <header class="premium-header">
        <div class="container-wide header-inner">
            <a href="/main/index.php" class="logo">Adaa<span>x</span></a>
            <div class="header-actions">
                <a href="dashboard.php" class="user-profile-link"><i class="fas fa-user-circle"></i> <span>Dashboard</span></a>
            </div>
        </div>
    </header>

    <main class="post-ad-wrapper">
        <div class="container-wide">
            <div class="post-form-card">
                <h1 style="font-size: 30px; color: var(--white); margin-bottom: 30px;">Edit Your <span>Ad</span></h1>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Ad Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($ad['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Price (PKR)</label>
                        <input type="number" name="price" value="<?php echo htmlspecialchars($ad['price']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="6" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($ad['phone']); ?>" required>
                    </div>

                    <div style="display: flex; gap: 20px; margin-top: 40px;">
                        <button type="submit" class="post-ad-btn" style="flex: 1; padding: 15px;">Update Ad Details</button>
                        <a href="dashboard.php?tab=ads" class="nav-link" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px; text-decoration: none; display: inline-block; text-align: center;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
