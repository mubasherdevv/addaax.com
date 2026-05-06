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

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'ADDAAX';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Initialize variables
$success_message = '';
$error_message = '';

// Helper function to generate a unique slug
function generateUniqueCitySlug($conn, $name, $ignore_id = 0) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $original_slug = $slug;
    $counter = 1;
    
    while (true) {
        if ($ignore_id > 0) {
            $stmt = $conn->prepare("SELECT id FROM cities WHERE slug = ? AND id != ?");
            $stmt->bind_param("si", $slug, $ignore_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM cities WHERE slug = ?");
            $stmt->bind_param("s", $slug);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

// Handle State Actions
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add_state') {
        $name = trim($_POST['state_name']);
        if (!empty($name)) {
            try {
                $stmt = $conn->prepare("INSERT INTO states (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                if ($stmt->execute()) {
                    $success_message = "State added successfully!";
                } else {
                    $error_message = "Error adding state: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] == 'edit_state') {
        $id = intval($_POST['state_id']);
        $name = trim($_POST['state_name']);
        if (!empty($name)) {
            try {
                $stmt = $conn->prepare("UPDATE states SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $id);
                if ($stmt->execute()) {
                    $success_message = "State updated successfully!";
                } else {
                    $error_message = "Error updating state: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] == 'add_city') {
        $name = trim($_POST['city_name']);
        $state_id = intval($_POST['state_id']);
        $slug = generateUniqueCitySlug($conn, $name);
        
        if (!empty($name) && $state_id > 0) {
            try {
                $stmt = $conn->prepare("INSERT INTO cities (name, state_id, slug) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $name, $state_id, $slug);
                if ($stmt->execute()) {
                    $success_message = "City added successfully!";
                } else {
                    $error_message = "Error adding city: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] == 'edit_city') {
        $id = intval($_POST['city_id']);
        $name = trim($_POST['city_name']);
        $state_id = intval($_POST['state_id']);
        $slug = generateUniqueCitySlug($conn, $name, $id);
        
        if (!empty($name) && $state_id > 0) {
            try {
                $stmt = $conn->prepare("UPDATE cities SET name = ?, state_id = ?, slug = ? WHERE id = ?");
                $stmt->bind_param("sisi", $name, $state_id, $slug, $id);
                if ($stmt->execute()) {
                    $success_message = "City updated successfully!";
                } else {
                    $error_message = "Error updating city: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] == 'bulk_add_cities') {
        $cities_text = trim($_POST['cities_text']);
        $state_id = intval($_POST['state_id']);
        
        if (!empty($cities_text) && $state_id > 0) {
            $city_list = preg_split('/[,\n\r]+/', $cities_text);
            $added_count = 0;
            $stmt = $conn->prepare("INSERT INTO cities (name, state_id, slug) VALUES (?, ?, ?)");
            
            foreach ($city_list as $city_name) {
                $city_name = trim($city_name);
                if (!empty($city_name)) {
                    $slug = generateUniqueCitySlug($conn, $city_name);
                    $stmt->bind_param("sis", $city_name, $state_id, $slug);
                    try {
                        if ($stmt->execute()) {
                            $added_count++;
                        }
                    } catch (mysqli_sql_exception $e) {
                        // Skip duplicate or failing cities in bulk
                        continue;
                    }
                }
            }
            if ($added_count > 0) {
                $success_message = "$added_count cities added successfully!";
            } else {
                $error_message = "No cities were added.";
            }
        }
    } elseif ($_POST['action'] == 'bulk_delete_cities') {
        $city_ids = $_POST['city_ids'] ?? [];
        if (!empty($city_ids)) {
            $ids = implode(',', array_map('intval', $city_ids));
            if ($conn->query("DELETE FROM cities WHERE id IN ($ids)")) {
                $success_message = count($city_ids) . " cities deleted successfully!";
            } else {
                $error_message = "Error deleting cities: " . $conn->error;
            }
        }
    }
}

// Handle Delete Actions
if (isset($_GET['delete_state'])) {
    $id = intval($_GET['delete_state']);
    // Check if state has cities
    $check = $conn->query("SELECT id FROM cities WHERE state_id = $id");
    if ($check->num_rows > 0) {
        $error_message = "Cannot delete state: It has cities assigned to it.";
    } else {
        $conn->query("DELETE FROM states WHERE id = $id");
        $success_message = "State deleted successfully!";
    }
}

if (isset($_GET['delete_city'])) {
    $id = intval($_GET['delete_city']);
    $conn->query("DELETE FROM cities WHERE id = $id");
    $success_message = "City deleted successfully!";
}

// Get all states
$states = $conn->query("SELECT * FROM states ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Get all cities with state names
$cities = $conn->query("SELECT c.*, s.name as state_name FROM cities c LEFT JOIN states s ON c.state_id = s.id ORDER BY s.name ASC, c.name ASC")->fetch_all(MYSQLI_ASSOC);

require_once 'includes/admin_layout.php';
renderAdminHeader('City & State Management');
renderAdminSidebar('cities');
?>

<main class="admin-content">
    <div class="admin-header">
        <div>
            <h1>City & State Management</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Manage provinces/states and their corresponding cities for ad listings.</p>
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

    <div class="management-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
        
        <!-- States Section -->
        <section class="states-section">
            <div class="settings-section">
                <div class="section-header">
                    <h2><i class="fas fa-map-marker-alt" style="color: var(--primary);"></i> States / Provinces</h2>
                </div>
                
                <form method="POST" class="state-form" style="margin-bottom: 30px;">
                    <input type="hidden" name="action" value="add_state" id="stateAction">
                    <input type="hidden" name="state_id" value="" id="stateId">
                    <div class="form-group">
                        <label>State Name</label>
                        <input type="text" name="state_name" id="stateName" class="form-control" placeholder="e.g. Punjab" required>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" id="stateSubmitBtn">Add State</button>
                        <button type="button" onclick="resetStateForm()" class="btn btn-outline" id="stateCancelBtn" style="display: none;">Cancel</button>
                    </div>
                </form>

                <div class="table-container" style="margin-bottom: 0; box-shadow: none; border: 1px solid var(--border);">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($states)): ?>
                                <tr><td colspan="2" style="text-align: center; color: var(--text-muted); padding: 20px;">No states added.</td></tr>
                            <?php endif; ?>
                            <?php foreach($states as $state): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($state['name']); ?></td>
                                    <td style="text-align: right;">
                                        <button onclick="editState(<?php echo $state['id']; ?>, '<?php echo addslashes($state['name']); ?>')" class="btn-user-dash" style="background:none; border:none; cursor:pointer;" title="Edit"><i class="fas fa-edit"></i></button>
                                        <a href="?delete_state=<?php echo $state['id']; ?>" class="btn-user-dash" onclick="return confirm('Delete this state?')" style="color: var(--danger); margin-left: 10px;" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Cities Section -->
        <section class="cities-section">
            <div class="settings-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><i class="fas fa-city" style="color: var(--primary);"></i> Cities</h2>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="toggleBulkAdd()" class="btn btn-outline" style="padding: 5px 12px; font-size: 0.8rem;"><i class="fas fa-layer-group"></i> Bulk Add</button>
                    </div>
                </div>

                <!-- Floating Bulk Actions Bar -->
                <div id="cityBulkActions" style="display: none; background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px; position: sticky; top: 10px; z-index: 100; margin-bottom: 20px; align-items: center; justify-content: space-between; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span id="selectedCityCount" style="font-weight: 600; font-size: 0.9rem;">0 cities selected</span>
                    </div>
                    <button type="button" onclick="confirmBulkDelete()" class="btn" style="background: #ef4444; color: white; border: none; padding: 6px 15px; font-size: 0.85rem; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-trash" style="margin-right: 5px;"></i> Delete Selected
                    </button>
                </div>
                
                <form method="POST" class="city-form" id="regularCityForm" style="margin-bottom: 30px;">
                    <input type="hidden" name="action" value="add_city" id="cityAction">
                    <input type="hidden" name="city_id" value="" id="cityId">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>City Name</label>
                            <input type="text" name="city_name" id="cityName" class="form-control" placeholder="e.g. Lahore">
                        </div>
                        <div class="form-group">
                            <label>Select State</label>
                            <select name="state_id" id="cityStateId" class="form-control">
                                <option value="">-- State --</option>
                                <?php foreach($states as $state): ?>
                                    <option value="<?php echo $state['id']; ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <button type="submit" class="btn btn-primary" id="citySubmitBtn">Add City</button>
                        <button type="button" onclick="resetCityForm()" class="btn btn-outline" id="cityCancelBtn" style="display: none;">Cancel</button>
                    </div>
                </form>

                <!-- Bulk Add Cities Form -->
                <form method="POST" class="city-form" id="bulkCityForm" style="margin-bottom: 30px; display: none; background: #fdf2f2; padding: 15px; border-radius: 12px; border: 1px dashed var(--danger);">
                    <input type="hidden" name="action" value="bulk_add_cities">
                    <div class="form-group">
                        <label>City Names (Comma or New line separated)</label>
                        <textarea name="cities_text" class="form-control" rows="4" placeholder="Lahore, Karachi, Islamabad..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Select State for all cities</label>
                        <select name="state_id" class="form-control" required>
                            <option value="">-- Select State --</option>
                            <?php foreach($states as $state): ?>
                                <option value="<?php echo $state['id']; ?>"><?php echo htmlspecialchars($state['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="background: var(--danger); border-color: var(--danger);">Bulk Add Cities</button>
                        <button type="button" onclick="toggleBulkAdd()" class="btn btn-outline">Cancel</button>
                    </div>
                </form>

                <div class="table-container" style="margin-bottom: 0; box-shadow: none; border: 1px solid var(--border); max-height: 450px; overflow-y: auto;">
                    <form id="bulkDeleteForm" method="POST">
                        <input type="hidden" name="action" value="bulk_delete_cities">
                        <table class="admin-table">
                            <thead>
                                <tr style="position: sticky; top: 0; z-index: 10;">
                                    <th style="width: 40px;"><input type="checkbox" id="selectAllCities" onclick="toggleSelectAll(this)"></th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cities)): ?>
                                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No cities added.</td></tr>
                                <?php endif; ?>
                                <?php foreach($cities as $city): ?>
                                    <tr>
                                        <td><input type="checkbox" name="city_ids[]" value="<?php echo $city['id']; ?>" class="city-checkbox" onclick="toggleDeleteBtn()"></td>
                                        <td style="font-weight: 600;"><?php echo htmlspecialchars($city['name']); ?></td>
                                        <td><span class="badge" style="background: var(--light); color: var(--text-main);"><?php echo htmlspecialchars($city['state_name'] ?? 'Unassigned'); ?></span></td>
                                        <td style="text-align: right;">
                                            <button type="button" onclick="editCity(<?php echo $city['id']; ?>, '<?php echo addslashes($city['name']); ?>', <?php echo $city['state_id']; ?>)" class="btn-user-dash" style="background:none; border:none; cursor:pointer;" title="Edit"><i class="fas fa-edit"></i></button>
                                            <a href="?delete_city=<?php echo $city['id']; ?>" class="btn-user-dash" onclick="return confirm('Delete this city?')" style="color: var(--danger); margin-left: 10px;" title="Delete"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.city-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    toggleDeleteBtn();
}

function toggleDeleteBtn() {
    const checkboxes = document.querySelectorAll('.city-checkbox:checked');
    const bulkBar = document.getElementById('cityBulkActions');
    const countDisplay = document.getElementById('selectedCityCount');
    
    if (checkboxes.length > 0) {
        bulkBar.style.display = 'flex';
        countDisplay.innerText = checkboxes.length + ' city(s) selected';
    } else {
        bulkBar.style.display = 'none';
        document.getElementById('selectAllCities').checked = false;
    }
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.city-checkbox:checked');
    if (confirm(`Are you sure you want to delete ${checkboxes.length} selected cities?`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

function editState(id, name) {
    document.getElementById('stateAction').value = 'edit_state';
    document.getElementById('stateId').value = id;
    document.getElementById('stateName').value = name;
    document.getElementById('stateSubmitBtn').innerText = 'Update State';
    document.getElementById('stateCancelBtn').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetStateForm() {
    document.getElementById('stateAction').value = 'add_state';
    document.getElementById('stateId').value = '';
    document.getElementById('stateName').value = '';
    document.getElementById('stateSubmitBtn').innerText = 'Add State';
    document.getElementById('stateCancelBtn').style.display = 'none';
}

function editCity(id, name, stateId) {
    document.getElementById('cityAction').value = 'edit_city';
    document.getElementById('cityId').value = id;
    document.getElementById('cityName').value = name;
    document.getElementById('cityStateId').value = stateId;
    document.getElementById('citySubmitBtn').innerText = 'Update City';
    document.getElementById('cityCancelBtn').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetCityForm() {
    document.getElementById('cityAction').value = 'add_city';
    document.getElementById('cityId').value = '';
    document.getElementById('cityName').value = '';
    document.getElementById('cityStateId').value = '';
    document.getElementById('citySubmitBtn').innerText = 'Add City';
    document.getElementById('cityCancelBtn').style.display = 'none';
}

function toggleBulkAdd() {
    const regularForm = document.getElementById('regularCityForm');
    const bulkForm = document.getElementById('bulkCityForm');
    
    if (bulkForm.style.display === 'none') {
        bulkForm.style.display = 'block';
        regularForm.style.display = 'none';
    } else {
        bulkForm.style.display = 'none';
        regularForm.style.display = 'block';
    }
}
</script>

<?php renderAdminFooter(); ?>
