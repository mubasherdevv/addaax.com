<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/website_settings.php';
require_once '../includes/layout_functions.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Auto-migrate: ensure seller_display_name column exists to prevent SQL errors
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS seller_display_name VARCHAR(255) DEFAULT NULL AFTER phone");

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

// Get images
$img_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id");
$img_stmt->bind_param("i", $ad_id);
$img_stmt->execute();
$existing_images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $display_name = $conn->real_escape_string($_POST['name']);
    
    $update_sql = "UPDATE products SET name = ?, price = ?, description = ?, phone = ?, seller_display_name = ?, updated_at = NOW() WHERE id = ?";
    $up_stmt = $conn->prepare($update_sql);
    $up_stmt->bind_param("sdsssi", $title, $price, $description, $phone, $display_name, $ad_id);
    
    if ($up_stmt->execute()) {
        // Handle new images if any
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            require_once '../includes/image_utils.php';
            
            // Get city for folder (if changed or keep existing)
            $city_name = $ad['city'] ?? 'all';
            $city_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $city_name)));
            if (empty($city_slug)) $city_slug = 'all';
            
            $upload_dir = '../uploads/products/' . $city_slug . '/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (empty($tmp_name)) continue;

                $filename_orig = $_FILES['images']['name'][$key];
                $ext = strtolower(pathinfo($filename_orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($ext, $allowed)) {
                    $unique_name = $slug . '_' . uniqid() . '_' . time() . '.' . $ext;
                    $target_path = $upload_dir . $unique_name;

                    $saved_filename = compressImage($tmp_name, $target_path, 75, true);
                    
                    if ($saved_filename) {
                        $db_path = 'uploads/products/' . $city_slug . '/' . $saved_filename;
                        $img_sql = "INSERT INTO product_images (product_id, image_path, is_primary, filename, created_at) 
                                    VALUES (?, ?, 0, ?, NOW())";
                        $img_stmt = $conn->prepare($img_sql);
                        $img_stmt->bind_param("iss", $ad_id, $db_path, $filename_orig);
                        $img_stmt->execute();
                    }
                }
            }
        }

        header("Location: dashboard.php?tab=ads&msg=Ad updated successfully");
        exit;
    }
}

$states = $conn->query("SELECT * FROM states WHERE status = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

renderHeader('Edit Ad | ADDAAX', 'dashboard');
?>

<style>
    :root {
        --accent-gold: #c9a84c;
        --accent-hover: #e0bc5a;
        --glass-bg: rgba(245, 233, 200, 0.02);
        --glass-border: rgba(255, 255, 255, 0.08);
        --text-muted: #a0a0a0;
        --white: #ffffff;
        --radius: 24px;
    }

    .post-ad-wrapper {
        padding-top: 100px;
        padding-bottom: 80px;
        min-height: 100vh;
        background-image: 
            radial-gradient(circle at top right, rgba(201, 168, 76, 0.05), transparent 40%),
            radial-gradient(circle at bottom left, rgba(201, 168, 76, 0.03), transparent 40%);
    }

    .post-form-card {
        max-width: 700px;
        margin: 0 auto;
        background: var(--glass-bg);
        backdrop-filter: blur(40px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius);
        padding: 35px 40px;
        box-shadow: 0 40px 100px rgba(0,0,0,0.8);
    }

    .form-progress {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-bottom: 35px;
        position: relative;
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        z-index: 2;
    }

    .step-number {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        border: 2px solid var(--glass-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .progress-step.active .step-number {
        background: var(--accent-gold);
        border-color: var(--accent-gold);
        box-shadow: 0 0 20px rgba(201, 168, 76, 0.4);
        color: #000;
    }

    .progress-step.completed .step-number {
        background: #22c55e;
        border-color: #22c55e;
        color: #fff;
    }

    .step-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
    .progress-step.active .step-label { color: var(--accent-gold); }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.9); }
    
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 12px 16px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        color: var(--white);
        font-family: inherit;
        font-size: 14px;
        transition: 0.3s;
        outline: none;
    }

    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
        background: rgba(255,255,255,0.06);
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.1);
    }

    .image-upload-area {
        border: 2px dashed var(--glass-border);
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
        background: rgba(255,255,255,0.01);
    }

    .image-upload-area:hover {
        border-color: var(--accent-gold);
        background: rgba(201, 168, 76, 0.03);
    }

    .post-ad-btn {
        background: var(--accent-gold);
        color: #000;
        border: none;
        padding: 15px 30px;
        border-radius: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
    }

    .post-ad-btn:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(201, 168, 76, 0.3); }

    .step-container { display: none; animation: fadeIn 0.5s ease; }
    .step-container.active { display: block; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-top: 25px; }
    .preview-item { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--glass-border); }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .preview-remove { position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: none; }
    .form-navigation { display: flex; gap: 20px; }
</style>

<main class="post-ad-wrapper">
    <div class="container-wide">
        <div class="post-form-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="font-size: 28px; font-family: 'Outfit', sans-serif; font-weight: 900; margin-bottom: 8px;">Edit Your <span style="color: var(--accent-gold);">Ad</span></h1>
                <p style="color: var(--text-muted); font-size: 14px;">Update your ad details for better visibility.</p>
            </div>

            <div class="form-progress">
                <div class="progress-step active" id="pStep1">
                    <div class="step-number">1</div>
                    <div class="step-label">Ad Details</div>
                </div>
                <div class="progress-step" id="pStep2">
                    <div class="step-number">2</div>
                    <div class="step-label">Media & Contact</div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="editAdForm">
                <!-- Step 1 -->
                <div class="step-container active" id="step1">
                    <div class="form-group">
                        <label>Ad Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($ad['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price (PKR) *</label>
                        <input type="number" name="price" value="<?php echo htmlspecialchars($ad['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="6" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                    </div>
                    <button type="button" class="post-ad-btn" onclick="nextStep()"> Next Step <i class="fas fa-arrow-right"></i></button>
                </div>

                <!-- Step 2 -->
                <div class="step-container" id="step2">
                    <div class="form-group">
                        <label>Gallery Photos (Add more)</label>
                        <div class="image-upload-area" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: var(--accent-gold); margin-bottom: 15px; display: block;"></i>
                            <p style="font-weight: 700;">Click to Upload More Images</p>
                            <input type="file" id="fileInput" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(this)">
                        </div>
                        <div class="image-preview-grid" id="previewGrid">
                            <?php foreach($existing_images as $img): ?>
                                <div class="preview-item">
                                    <img src="/<?php echo htmlspecialchars($img['image_path']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contact Phone Number *</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($ad['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Display Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($ad['seller_display_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-navigation" style="display: flex; gap: 20px; margin-top: 40px;">
                        <button type="button" onclick="prevStep()" style="background: rgba(255,255,255,0.05); color: white; border: none; padding: 18px 30px; border-radius: 16px; font-weight: 700; cursor: pointer;"><i class="fas fa-arrow-left"></i></button>
                        <button type="submit" class="post-ad-btn">Update Ad <i class="fas fa-check-circle"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function nextStep() {
        const inputs = document.querySelectorAll('#step1 [required]');
        let valid = true;
        inputs.forEach(input => {
            if(!input.value) {
                input.style.borderColor = '#ef4444';
                valid = false;
            } else {
                input.style.borderColor = 'var(--glass-border)';
            }
        });
        if(valid) {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.getElementById('pStep1').classList.add('completed');
            document.getElementById('pStep1').classList.remove('active');
            document.getElementById('pStep2').classList.add('active');
            window.scrollTo({ top: 100, behavior: 'smooth' });
        }
    }

    function prevStep() {
        document.getElementById('step2').classList.remove('active');
        document.getElementById('step1').classList.add('active');
        document.getElementById('pStep2').classList.remove('active');
        document.getElementById('pStep1').classList.add('active');
        document.getElementById('pStep1').classList.remove('completed');
        window.scrollTo({ top: 100, behavior: 'smooth' });
    }

    const watermarkImg = new Image();
    watermarkImg.src = '/images/watermark.png';

    function previewImages(input) {
        const grid = document.getElementById('previewGrid');
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);
                        
                        ctx.fillStyle = 'rgba(0, 0, 0, 0.95)';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        
                        if (watermarkImg.complete) {
                            const targetWWidth = canvas.width * 0.75;
                            const targetWHeight = (watermarkImg.height / watermarkImg.width) * targetWWidth;
                            const destX = (canvas.width - targetWWidth) / 2;
                            const destY = (canvas.height - targetWHeight) / 2;
                            ctx.drawImage(watermarkImg, destX, destY, targetWWidth, targetWHeight);
                        }
                        
                        const item = document.createElement('div');
                        item.className = 'preview-item';
                        const previewUrl = canvas.toDataURL('image/jpeg', 0.8);
                        item.innerHTML = `<img src="${previewUrl}"><button type="button" class="preview-remove" onclick="this.parentElement.remove()">×</button>`;
                        grid.appendChild(item);
                    };
                    img.src = e.target.result;
                }
                reader.readAsDataURL(file);
            });
        }
    }

    document.getElementById('editAdForm').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    });
</script>

<?php renderFooter(); ?>
