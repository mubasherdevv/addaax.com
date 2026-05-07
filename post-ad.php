<?php
require_once 'auth/session.php';
require_once 'auth/db_connect.php';
require_once 'includes/website_settings.php';
require_once 'includes/layout_functions.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php?return_to=post-ad.php");
    exit;
}

$states = $conn->query("SELECT * FROM states WHERE status = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);
// Initially empty, will be populated by AJAX
$cities = []; 

renderHeader('Post Your Ad | ADDAAX', 'post-ad');
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

        @media (max-width: 768px) {
            .post-ad-wrapper { padding-top: 100px; padding-bottom: 100px; }
            .post-form-card { padding: 30px 20px; border-radius: 16px; margin: 0 15px; }
            .form-progress { gap: 30px; margin-bottom: 30px; }
            .step-number { width: 35px; height: 35px; font-size: 14px; }
            .step-label { font-size: 10px; }
            h1 { font-size: 28px !important; }
            .form-group { margin-bottom: 20px; }
            .image-upload-area { padding: 30px 15px; }
            .post-ad-btn { padding: 15px; font-size: 14px; }
            .image-preview-grid { grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 10px; }
        }

        @media (max-width: 480px) {
            .form-grid-2 { grid-template-columns: 1fr !important; gap: 0 !important; }
        }

        .post-ad-btn {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        

        .post-ad-btn:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(201, 168, 76, 0.3); }
        .post-ad-btn:disabled { opacity: 0.7; cursor: not-allowed; }

        .step-container { display: none; animation: fadeIn 0.5s ease; }
        .step-container.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-top: 25px; }
        .preview-item { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--glass-border); }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .preview-remove { position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; border: none; }
        .form-navigation { display: flex; gap: 20px; }
        @media (max-width: 480px) {
            .form-navigation { flex-direction: column-reverse; gap: 15px; }
            .form-navigation button { width: 100%; }
        }
    </style>

    <main class="post-ad-wrapper">
        <div class="container-wide">
            <div class="post-form-card">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="font-size: 28px; font-family: 'Outfit', sans-serif; font-weight: 900; margin-bottom: 8px;">Post Your <span style="color: var(--accent-gold);">Ad</span></h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Fill in the details below to reach premium clients.</p>
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

                <form action="auth/process_ad.php" method="POST" enctype="multipart/form-data" id="postAdForm">
                    <!-- Step 1 -->
                    <div class="step-container active" id="step1">
                        <div class="form-group">
                            <label>Ad Title *</label>
                            <input type="text" name="title" placeholder="e.g. Premium VIP Service" required>
                        </div>
                        <div class="form-group">
                            <label>Price (PKR) *</label>
                            <input type="number" name="price" placeholder="Enter amount" required>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" rows="6" placeholder="Describe your service in detail..." required></textarea>
                        </div>
                        <button type="button" class="post-ad-btn" onclick="nextStep()"> Next Step <i class="fas fa-arrow-right"></i></button>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-container" id="step2">
                        <div class="form-group">
                            <label>Gallery Photos</label>
                            <div class="image-upload-area" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: var(--accent-gold); margin-bottom: 15px; display: block;"></i>
                                <p style="font-weight: 700;">Drag & Drop or Click to Upload</p>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">High quality images attract 5x more clients</p>
                                <input type="file" id="fileInput" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(this)">
                            </div>
                            <div class="image-preview-grid" id="previewGrid"></div>
                        </div>

                        <div class="form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Province *</label>
                                <select name="province_id" id="provinceSelect" required onchange="loadCities(this.value)">
                                    <option value="">-- Select Province --</option>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?php echo $state['id']; ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>City *</label>
                                <select name="city_id" id="citySelect" required>
                                    <option value="">-- Select City --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Contact Phone Number *</label>
                            <input type="tel" name="phone" placeholder="+92 3XX XXXXXXX" required>
                        </div>

                        <div class="form-group">
                            <label>Your Display Name *</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-navigation" style="display: flex; gap: 20px; margin-top: 40px;">
                            <button type="button" onclick="prevStep()" style="background: rgba(255,255,255,0.05); color: white; border: none; padding: 18px 30px; border-radius: 16px; font-weight: 700; cursor: pointer;"><i class="fas fa-arrow-left"></i></button>
                            <button type="submit" class="post-ad-btn">Publish Ad <i class="fas fa-check-circle"></i></button>
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

        // Pre-load watermark for instant preview
        const watermarkImg = new Image();
        watermarkImg.src = 'images/watermark.png';

        function previewImages(input) {
            const grid = document.getElementById('previewGrid');
            grid.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            
                            // Set canvas dimensions
                            canvas.width = img.width;
                            canvas.height = img.height;
                            
                            // Draw original image
                            ctx.drawImage(img, 0, 0);
                            
                            // 1. Add darkened background (Full Cover Black - 85% opacity)
                            ctx.fillStyle = 'rgba(0, 0, 0, 0.85)';
                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                            
                            // 2. Draw Watermark
                            if (watermarkImg.complete) {
                                const targetWWidth = canvas.width * 0.5;
                                const targetWHeight = (watermarkImg.height / watermarkImg.width) * targetWWidth;
                                const destX = (canvas.width - targetWWidth) / 2;
                                const destY = (canvas.height - targetWHeight) / 2;
                                ctx.drawImage(watermarkImg, destX, destY, targetWWidth, targetWHeight);
                            }
                            
                            // Create preview item
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

        document.getElementById('postAdForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Finalizing Publication...';
        });

        function loadCities(stateId) {
            const citySelect = document.getElementById('citySelect');
            citySelect.innerHTML = '<option value="">-- Loading... --</option>';
            
            if (!stateId) {
                citySelect.innerHTML = '<option value="">-- Select City --</option>';
                return;
            }

            fetch(`auth/get_cities.php?state_id=${stateId}`)
                .then(response => response.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">-- Select City --</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading cities:', error);
                    citySelect.innerHTML = '<option value="">-- Error loading cities --</option>';
                });
        }
    </script>

<?php
renderFooter();
?>
