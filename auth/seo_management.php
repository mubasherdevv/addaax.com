<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// Ensure footer_content column exists
$conn->query("ALTER TABLE seo_settings ADD COLUMN IF NOT EXISTS footer_content LONGTEXT AFTER meta_description");

// Handle Form Submission
$success_message = '';
$error_message = '';

if (isset($_POST['action'])) {
    $page_name = trim($_POST['page_name']);
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $footer_content = $_POST['footer_content']; // Raw HTML from TinyMCE
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if (empty($page_name)) {
        $error_message = "Page Name is required.";
    } else {
        if ($_POST['action'] == 'save_seo') {
            if ($id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE seo_settings SET page_name = ?, meta_title = ?, meta_description = ?, footer_content = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $page_name, $meta_title, $meta_description, $footer_content, $id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO seo_settings (page_name, meta_title, meta_description, footer_content) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $page_name, $meta_title, $meta_description, $footer_content);
            }

            try {
                if ($stmt->execute()) {
                    $success_message = "SEO settings saved successfully!";
                } else {
                    $error_message = "Error saving SEO settings: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $error_message = "Error: SEO settings for this page name already exist.";
                } else {
                    $error_message = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM seo_settings WHERE id = $id");
    $success_message = "SEO settings deleted successfully!";
}

// Get all SEO settings
$seo_list = $conn->query("SELECT * FROM seo_settings ORDER BY page_name ASC")->fetch_all(MYSQLI_ASSOC);

// Get available pages from root directory
$root_pages = [];
$files = scandir('../');
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
        $root_pages[] = $file;
    }
}
sort($root_pages);

// Fetch States and Cities for Location-based SEO
$all_states = $conn->query("SELECT id, name FROM states WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_cities = $conn->query("SELECT id, name, state_id FROM cities WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

require_once 'includes/admin_layout.php';
renderAdminHeader('SEO Management');
?>
<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/1vxh163mkcmuu0c82seo7eyc3ihpi172k71utpwgfqqwvinq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  function initTinyMCE() {
    if (tinymce.get('footer_content')) {
        tinymce.get('footer_content').remove();
    }
    tinymce.init({
      selector: '#footer_content',
      plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
      height: 400,
      skin: 'oxide-dark',
      content_css: 'dark',
      promotion: false,
      branding: false,
      setup: function (editor) {
        editor.on('change', function () {
          tinymce.triggerSave();
        });
      }
    });
  }
</script>
<?php
renderAdminSidebar('seo');
?>

<main class="admin-content">
    <div class="admin-header">
        <div>
            <h1>SEO & Meta Management</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Manage Meta Titles and Descriptions for your website pages to improve SEO.</p>
        </div>
        <div class="admin-actions">
            <button class="btn btn-primary" onclick="showAddForm()"><i class="fas fa-plus"></i> Add New Page SEO</button>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success" style="padding: 15px; background: rgba(34, 197, 94, 0.1); color: var(--success); border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(34, 197, 94, 0.2);">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" style="padding: 15px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- SEO Form Section (Hidden by default) -->
    <div id="seoFormSection" class="admin-card" style="display: none; margin-bottom: 30px; background: #1e293b; border: 1px solid #334155;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h2 id="formTitle" style="color: white; margin: 0;">Add SEO Settings</h2>
            <button class="btn btn-secondary" onclick="hideForm()"><i class="fas fa-times"></i> Cancel</button>
        </div>
        <div class="card-body" style="padding: 30px;">
            <form method="POST">
                <input type="hidden" name="action" value="save_seo">
                <input type="hidden" name="id" id="seo_id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Target Type</label>
                        <select name="page_type" id="page_type" class="form-control" onchange="toggleTargetType(this.value)" required>
                            <option value="file">Internal File (.php)</option>
                            <option value="location">Location Page (City)</option>
                            <option value="custom">Custom URL / Path</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Final Page URL Path</label>
                        <input type="text" name="page_name" id="page_name" class="form-control" readonly style="background: #0f172a; cursor: not-allowed; opacity: 0.8;" required>
                        <small style="color: var(--text-muted);">This is the path used for SEO mapping.</small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- 1. File Selection -->
                    <div class="form-group" id="file_target_group">
                        <label>Select Page</label>
                        <select name="page_name_select" id="page_name_select" class="form-control">
                            <option value="">-- Choose File --</option>
                            <?php foreach($root_pages as $page): ?>
                                <option value="<?php echo $page; ?>"><?php echo $page; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Location Selection -->
                    <div id="location_target_group" style="display: none; gap: 20px;">
                        <div class="form-group">
                            <label>Select Province</label>
                            <select id="state_select" class="form-control" onchange="filterCities(this.value)">
                                <option value="">-- Choose Province --</option>
                                <?php foreach($all_states as $state): ?>
                                    <option value="<?php echo $state['id']; ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select City</label>
                            <select id="city_select" class="form-control" onchange="generateCityUrl(this.options[this.selectedIndex].text)">
                                <option value="">-- Choose City --</option>
                            </select>
                        </div>
                    </div>

                    <!-- 3. Custom Path -->
                    <div class="form-group" id="custom_target_group" style="display: none;">
                        <label>Custom Page Name / URL</label>
                        <input type="text" name="page_name_custom" id="page_name_custom" class="form-control" placeholder="e.g. /category/electronics">
                    </div>
                    
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control" placeholder="Max 60 characters recommended">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Meta Description</label>
                    <textarea name="meta_description" id="meta_description" class="form-control" rows="3" placeholder="Max 160 characters recommended"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label>Footer SEO Content (Rich Text Editor)</label>
                    <textarea name="footer_content" id="footer_content" class="form-control" rows="10"></textarea>
                    <small style="color: var(--text-muted);">This content will be displayed right above the footer on the selected page.</small>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 10px;">Save SEO Settings</button>
                    <button type="button" class="btn btn-secondary" onclick="hideForm()" style="padding: 12px 30px; border-radius: 10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SEO List Table -->
    <div class="table-container" id="listSection">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Page Name</th>
                    <th>Meta Title</th>
                    <th>Meta Description</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($seo_list)): ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px;">No SEO settings found. Click "Add New" to begin.</td></tr>
                <?php endif; ?>
                <?php foreach($seo_list as $seo): ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($seo['page_name']); ?></td>
                        <td><?php echo htmlspecialchars($seo['meta_title']); ?></td>
                        <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-muted);">
                            <?php echo htmlspecialchars($seo['meta_description']); ?>
                        </td>
                        <td style="text-align: right;">
                            <button onclick='editSeo(<?php echo json_encode($seo); ?>)' class="btn-user-dash" style="background:none; border:none; cursor:pointer; color: var(--primary);" title="Edit"><i class="fas fa-edit"></i></button>
                            <a href="?delete=<?php echo $seo['id']; ?>" class="btn-user-dash" onclick="return confirm('Are you sure you want to delete SEO settings for this page?')" style="color: var(--danger); margin-left: 10px;" title="Delete"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<style>
.form-group label { color: #cbd5e1; font-weight: 600; margin-bottom: 8px; display: block; }
.form-control { 
    background: #0f172a !important; 
    border: 1px solid #334155 !important; 
    color: white !important; 
    border-radius: 10px;
    padding: 10px 15px;
}
.btn-secondary {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
}
</style>

<script>
// Data from PHP
const allCities = <?php echo json_encode($all_cities); ?>;

// Move functions to global scope explicitly
window.showAddForm = function() {
    document.getElementById('formTitle').innerText = 'Add SEO Settings';
    document.getElementById('seo_id').value = '';
    document.getElementById('page_type').value = 'file';
    document.getElementById('page_name_select').value = '';
    document.getElementById('page_name_custom').value = '';
    document.getElementById('page_name').value = '';
    document.getElementById('meta_title').value = '';
    document.getElementById('meta_description').value = '';
    if (tinymce.get('footer_content')) {
        tinymce.get('footer_content').setContent('');
    }
    
    toggleTargetType('file');
    
    document.getElementById('listSection').style.display = 'none';
    document.getElementById('seoFormSection').style.display = 'block';
    initTinyMCE();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.hideForm = function() {
    document.getElementById('seoFormSection').style.display = 'none';
    document.getElementById('listSection').style.display = 'block';
};

window.toggleTargetType = function(type) {
    document.getElementById('file_target_group').style.display = (type === 'file') ? 'block' : 'none';
    document.getElementById('location_target_group').style.display = (type === 'location') ? 'flex' : 'none';
    document.getElementById('custom_target_group').style.display = (type === 'custom') ? 'block' : 'none';
    
    // Clear final page name when switching
    if (type !== 'file' || document.getElementById('page_name_select').value === '') {
        document.getElementById('page_name').value = '';
    }
};

window.filterCities = function(stateId) {
    const citySelect = document.getElementById('city_select');
    citySelect.innerHTML = '<option value="">-- Choose City --</option>';
    
    if (!stateId) return;
    
    const filtered = allCities.filter(c => c.state_id == stateId);
    filtered.forEach(city => {
        const opt = document.createElement('option');
        opt.value = city.id;
        opt.text = city.name;
        citySelect.add(opt);
    });
};

window.generateCityUrl = function(cityName) {
    if (!cityName || cityName.includes('--')) {
        document.getElementById('page_name').value = '';
        return;
    }
    const slug = cityName.toLowerCase().trim().replace(/ /g, '-');
    document.getElementById('page_name').value = '/escorts/' + slug;
};

window.editSeo = function(data) {
    document.getElementById('formTitle').innerText = 'Edit SEO Settings';
    document.getElementById('seo_id').value = data.id;
    document.getElementById('meta_title').value = data.meta_title;
    document.getElementById('meta_description').value = data.meta_description;
    document.getElementById('page_name').value = data.page_name;
    
    // Show form section
    document.getElementById('listSection').style.display = 'none';
    document.getElementById('seoFormSection').style.display = 'block';
    
    initTinyMCE();
    setTimeout(() => {
        if (tinymce.get('footer_content')) {
            tinymce.get('footer_content').setContent(data.footer_content || '');
        }
    }, 100);

    // Determine type
    if (data.page_name.startsWith('/escorts/')) {
        document.getElementById('page_type').value = 'location';
        toggleTargetType('location');
        document.getElementById('page_name_custom').value = data.page_name; 
        document.getElementById('page_type').value = 'custom'; // Treat as custom on edit
        toggleTargetType('custom');
    } else if (data.page_name.endsWith('.php')) {
        document.getElementById('page_type').value = 'file';
        toggleTargetType('file');
        document.getElementById('page_name_select').value = data.page_name;
    } else {
        document.getElementById('page_type').value = 'custom';
        toggleTargetType('custom');
        document.getElementById('page_name_custom').value = data.page_name;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Form submit handler
document.addEventListener('DOMContentLoaded', function() {
    // Listen for file select changes
    const fileSelect = document.getElementById('page_name_select');
    if (fileSelect) {
        fileSelect.onchange = function() {
            document.getElementById('page_name').value = this.value;
        };
    }
    
    // Listen for custom input changes
    const customInput = document.getElementById('page_name_custom');
    if (customInput) {
        customInput.oninput = function() {
            document.getElementById('page_name').value = this.value;
        };
    }
});
</script>

<?php renderAdminFooter(); ?>
