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

// Handle Form Submission
$success_message = '';
$error_message = '';

if (isset($_POST['action'])) {
    $page_name = trim($_POST['page_name']);
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if (empty($page_name)) {
        $error_message = "Page Name is required.";
    } else {
        if ($_POST['action'] == 'save_seo') {
            if ($id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE seo_settings SET page_name = ?, meta_title = ?, meta_description = ? WHERE id = ?");
                $stmt->bind_param("sssi", $page_name, $meta_title, $meta_description, $id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO seo_settings (page_name, meta_title, meta_description) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $page_name, $meta_title, $meta_description);
            }

            if ($stmt->execute()) {
                $success_message = "SEO settings saved successfully!";
            } else {
                if ($conn->errno == 1062) {
                    $error_message = "Error: SEO settings for this page name already exist.";
                } else {
                    $error_message = "Error saving SEO settings: " . $conn->error;
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

require_once 'includes/admin_layout.php';
renderAdminHeader('SEO Management');
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

    <!-- SEO List Table -->
    <div class="table-container">
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

<!-- SEO Modal -->
<div class="modal-overlay" id="seoModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="modalTitle">Add SEO Settings</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save_seo">
            <input type="hidden" name="id" id="seo_id">
            
            <div class="form-group">
                <label>Select Page</label>
                <select name="page_name_select" id="page_name_select" class="form-control" onchange="checkCustomPage(this.value)" required>
                    <option value="">-- Choose Page --</option>
                    <?php foreach($root_pages as $page): ?>
                        <option value="<?php echo $page; ?>"><?php echo $page; ?></option>
                    <?php endforeach; ?>
                    <option value="custom">-- Custom URL --</option>
                </select>
            </div>

            <div class="form-group" id="custom_page_group" style="display: none;">
                <label>Custom Page Name / URL</label>
                <input type="text" name="page_name_custom" id="page_name_custom" class="form-control" placeholder="e.g. /category/electronics">
            </div>
            
            <!-- Hidden input for the final value -->
            <input type="hidden" name="page_name" id="page_name">
            
            <div class="form-group">
                <label>Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" class="form-control" placeholder="Max 60 characters recommended">
            </div>
            
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" id="meta_description" class="form-control" rows="4" placeholder="Max 160 characters recommended"></textarea>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save SEO Settings</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Robust Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
.modal-overlay.visible {
    display: flex !important;
    opacity: 1;
}
.modal-overlay.visible .modal {
    display: block !important;
    transform: translateY(0);
    opacity: 1 !important;
    visibility: visible !important;
}
.modal {
    background: white !important;
    padding: 30px;
    border-radius: 20px;
    width: 95%;
    max-width: 600px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    transform: translateY(-20px);
    transition: all 0.3s ease;
    position: relative;
    z-index: 10000;
    margin: auto;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
}
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
}
</style>

<script>
const modal = document.getElementById('seoModal');

function showAddForm() {
    document.getElementById('modalTitle').innerText = 'Add SEO Settings';
    document.getElementById('seo_id').value = '';
    document.getElementById('page_name_select').value = '';
    document.getElementById('page_name_custom').value = '';
    document.getElementById('page_name').value = '';
    document.getElementById('meta_title').value = '';
    document.getElementById('meta_description').value = '';
    document.getElementById('custom_page_group').style.display = 'none';
    
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('visible'), 10);
}

function editSeo(data) {
    document.getElementById('modalTitle').innerText = 'Edit SEO Settings';
    document.getElementById('seo_id').value = data.id;
    document.getElementById('meta_title').value = data.meta_title;
    document.getElementById('meta_description').value = data.meta_description;
    
    const select = document.getElementById('page_name_select');
    const customGroup = document.getElementById('custom_page_group');
    const customInput = document.getElementById('page_name_custom');
    
    // Check if the page exists in the dropdown
    let exists = false;
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === data.page_name) {
            select.selectedIndex = i;
            exists = true;
            break;
        }
    }
    
    if (exists) {
        customGroup.style.display = 'none';
        customInput.value = '';
    } else {
        select.value = 'custom';
        customGroup.style.display = 'block';
        customInput.value = data.page_name;
    }
    
    document.getElementById('page_name').value = data.page_name;
    
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('visible'), 10);
}

function checkCustomPage(value) {
    const customGroup = document.getElementById('custom_page_group');
    if (value === 'custom') {
        customGroup.style.display = 'block';
    } else {
        customGroup.style.display = 'none';
        document.getElementById('page_name').value = value;
    }
}

// Before submitting, ensure the hidden page_name is set
document.querySelector('form').onsubmit = function() {
    const selectValue = document.getElementById('page_name_select').value;
    if (selectValue === 'custom') {
        document.getElementById('page_name').value = document.getElementById('page_name_custom').value;
    } else {
        document.getElementById('page_name').value = selectValue;
    }
};

function closeModal() {
    modal.classList.remove('visible');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Close on outside click
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php renderAdminFooter(); ?>
