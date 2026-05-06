<?php
require_once '../config/database.php';  // Database constants
require_once '../config/constants.php'; // Application constants
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php'; // Include website settings

// Check if user is logged in and is an admin
if (!isLoggedIn() || $_SESSION["user_role"] !== "admin") {
    // Redirect to login page if not admin
    header("Location: login.php");
    exit;
}

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Fetch all users from the database with ad counts
$users = [];
$users_sql = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name, u.email, u.role, 
                    IF(u.is_verified = 1, 'Active', 'Inactive') AS status, 
                    u.created_at AS last_login,
                    (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as total_ads
               FROM users u ORDER BY u.id";
$users_result = $conn->query($users_sql);
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get admin details
$admin_id = $_SESSION["user_id"];
$sql = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<?php
require_once 'includes/admin_layout.php';
renderAdminHeader('User Management');
renderAdminSidebar('users');
?>
<?php
// Main Content
?>
<style>
/* Modal visibility fixes */
.modal-overlay.visible { display: flex !important; }
.modal-overlay.visible .modal { display: block !important; }
</style>
        
        <!-- Main Content -->
        <main class="admin-content">
            <!-- User Management Section -->
            <div class="admin-header">
                <div>
                    <h1>User & Admin Management</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Manage system administrators and regular users.</p>
                </div>
                <div class="admin-actions">
                    <button class="btn btn-primary" id="addUserBtn"><i class="fas fa-plus"></i> Add New User</button>
                </div>
            </div>
            
            <!-- User filters and search -->
            <div class="user-filters" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: #fff; padding: 15px; border-radius: 12px; box-shadow: var(--card-shadow);">
                <div style="display: flex; gap: 15px;">
                    <select id="role_filter" class="form-control" style="width: 180px;">
                        <option value="">All Roles</option>
                        <option value="admin">Super Admin</option>
                        <option value="product_manager">Product Manager</option>
                        <option value="order_manager">Order Manager</option>
                        <option value="user">Regular User</option>
                    </select>
                </div>
                <div style="position: relative; width: 300px;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="user_search" class="form-control" placeholder="Search users..." style="padding-left: 40px;">
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Total Ad</th>
                            <th>Last Login</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No users found</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($users as $user): ?>
                                <tr data-user-id="<?php echo $user['id']; ?>">
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php
                                        $role_display = [
                                            'admin' => 'Super Admin',
                                            'product_manager' => 'Product Manager',
                                            'order_manager' => 'Order Manager',
                                            'user' => 'Regular User'
                                        ][$user['role']] ?? 'Unknown';
                                        ?>
                                        <span class="badge" style="background: <?php 
                                            echo $user['role'] === 'admin' ? 'rgba(99, 102, 241, 0.1)' : 
                                                ($user['role'] === 'user' ? 'rgba(100, 116, 139, 0.1)' : 'rgba(6, 182, 212, 0.1)'); 
                                            ?>; color: <?php 
                                            echo $user['role'] === 'admin' ? 'var(--primary)' : 
                                                ($user['role'] === 'user' ? 'var(--secondary)' : 'var(--info)'); 
                                            ?>; font-weight: 700;">
                                            <?php echo $role_display; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] === 'Active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo htmlspecialchars($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['total_ads'] > 0): ?>
                                            <a href="product_management.php?seller_id=<?php echo $user['id']; ?>" 
                                               style="text-decoration: none; color: var(--primary); font-weight: 700; background: rgba(99, 102, 241, 0.1); padding: 4px 10px; border-radius: 6px;">
                                                <?php echo $user['total_ads']; ?> Ads
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No Ads</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td>
                                    <td class="user-actions" style="text-align: right;">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="btn-user-dash" style="background:none; border:none; cursor:pointer; color: var(--primary);" title="Edit"><i class="fas fa-edit"></i></button>
                                        <?php if ($user['total_ads'] > 0): ?>
                                            <a href="product_management.php?seller_id=<?php echo $user['id']; ?>" class="btn-user-dash" style="background:none; border:none; cursor:pointer; color: var(--info);" title="View Ads"><i class="fas fa-ad"></i></a>
                                        <?php endif; ?>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn-user-dash" style="background:none; border:none; cursor:pointer; color: var(--danger);" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div style="margin-top: 30px; display: flex; justify-content: center; gap: 8px;">
                <a href="#" class="btn btn-outline" style="padding: 8px 16px;">Previous</a>
                <a href="#" class="btn btn-primary" style="padding: 8px 16px;">1</a>
                <a href="#" class="btn btn-outline" style="padding: 8px 16px;">2</a>
                <a href="#" class="btn btn-outline" style="padding: 8px 16px;">3</a>
                <a href="#" class="btn btn-outline" style="padding: 8px 16px;">Next</a>
            </div>
        </main>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal">
        <div class="modal" id="addUserForm">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="modal-close" id="closeAddUserModal">&times;</button>
            </div>
            
            <form class="settings-form" id="add-user-form">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="new_first_name">First Name</label>
                        <input type="text" id="new_first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_last_name">Last Name</label>
                        <input type="text" id="new_last_name" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_email">Email Address</label>
                    <input type="email" id="new_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_role">Role</label>
                    <select id="new_role" name="role" class="form-control" required>
                        <option value="admin">Super Admin</option>
                        <option value="product_manager">Product Manager</option>
                        <option value="order_manager">Order Manager</option>
                        <option value="user" selected>Regular User</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="new_password">Password</label>
                        <input type="password" id="new_password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_confirm_password">Confirm Password</label>
                        <input type="password" id="new_confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create User Account</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editUserModal">
        <div class="modal" id="editUserForm">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button class="modal-close" id="closeEditUserModal">&times;</button>
            </div>
            
            <form class="settings-form" id="edit-user-form">
                <input type="hidden" id="edit_user_id" name="id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select id="edit_role" name="role" class="form-control" required>
                            <option value="admin">Super Admin</option>
                            <option value="product_manager">Product Manager</option>
                            <option value="order_manager">Order Manager</option>
                            <option value="user">Regular User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="is_verified" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }
            
            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 991) {
                    if (!sidebar.contains(event.target) && 
                        !sidebarToggle.contains(event.target) && 
                        sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            });
            
            // Modal functionality
            const addUserBtn = document.getElementById('addUserBtn');
            const addUserModal = document.getElementById('addUserModal');
            const closeAddUserModal = document.getElementById('closeAddUserModal');
            const editUserModal = document.getElementById('editUserModal');
            const closeEditUserModal = document.getElementById('closeEditUserModal');
            const addUserForm = document.getElementById('add-user-form');
            const editUserForm = document.getElementById('edit-user-form');
            
            // Show Add User Modal
            if (addUserBtn) {
                addUserBtn.addEventListener('click', function() {
                    addUserModal.style.display = 'flex';
                    const innerModal = addUserModal.querySelector('.modal');
                    if (innerModal) innerModal.style.display = 'block';
                    setTimeout(() => {
                        addUserModal.classList.add('visible');
                    }, 10);
                });
            }
            
            // Close Add User Modal
            if (closeAddUserModal) {
                closeAddUserModal.addEventListener('click', function() {
                    addUserModal.classList.remove('visible');
                    setTimeout(() => {
                        addUserModal.style.display = 'none';
                    }, 300);
                });
            }
            
            // Close Edit User Modal
            if (closeEditUserModal) {
                closeEditUserModal.addEventListener('click', function() {
                    editUserModal.classList.remove('visible');
                    setTimeout(() => {
                        editUserModal.style.display = 'none';
                    }, 300);
                });
            }
            
            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === addUserModal) {
                    addUserModal.classList.remove('visible');
                    setTimeout(() => {
                        addUserModal.style.display = 'none';
                    }, 300);
                }
                if (event.target === editUserModal) {
                    editUserModal.classList.remove('visible');
                    setTimeout(() => {
                        editUserModal.style.display = 'none';
                    }, 300);
                }
            });
            
            // Handle Add User Form Submission
            if (addUserForm) {
                addUserForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate passwords match
                    const password = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('new_confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        alert('Passwords do not match');
                        return;
                    }
                    
                    const formData = new FormData(this);
                    
                    fetch('add_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showFancyPopup('User added successfully');
                            addUserModal.classList.remove('visible');
                            setTimeout(() => {
                                addUserModal.style.display = 'none';
                                window.location.reload();
                            }, 300);
                        } else {
                            showFancyPopup('Failed to add user: ' + data.message, 'error');
                        }
                    })
                        .catch(error => {
                            console.error('Error adding user:', error);
                            showFancyPopup('An error occurred while adding user: ' + error.message, 'error');
                        });
                });
            }
            
            // Handle Edit User Form Submission
            if (editUserForm) {
                editUserForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate passwords match if provided
                    const password = document.getElementById('edit_password').value;
                    const confirmPassword = document.getElementById('edit_confirm_password').value;
                    
                    if (password && password !== confirmPassword) {
                        alert('Passwords do not match');
                        return;
                    }
                    
                    const formData = new FormData(this);
                    
                    fetch('update_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showFancyPopup('User updated successfully');
                            editUserModal.classList.remove('visible');
                            setTimeout(() => {
                                editUserModal.style.display = 'none';
                                window.location.reload();
                            }, 300);
                        } else {
                            showFancyPopup('Failed to update user: ' + data.message, 'error');
                        }
                    })
                        .catch(error => {
                            console.error('Error updating user:', error);
                            showFancyPopup('An error occurred while updating user: ' + error.message, 'error');
                        });
                });
            }
        });
        
        // Edit User Function
        function editUser(userId) {
            fetch(`get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('edit_user_id').value = user.id;
                        document.getElementById('edit_first_name').value = user.first_name;
                        document.getElementById('edit_last_name').value = user.last_name;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_role').value = user.role;
                        document.getElementById('edit_status').value = user.is_verified;
                        
                        // Show the modal
                        const editUserModal = document.getElementById('editUserModal');
                        editUserModal.style.display = 'flex';
                        const innerModal = editUserModal.querySelector('.modal');
                        if (innerModal) innerModal.style.display = 'block';
                        setTimeout(() => {
                            editUserModal.classList.add('visible');
                        }, 10);
                    } else {
                        alert('Failed to load user data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    alert('An error occurred while fetching user data: ' + error.message);
                });
        }
        
        // Delete User Function
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFancyPopup('User deleted successfully');
                        // Delay the page reload to allow the popup to be visible
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showFancyPopup('Failed to delete user: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting user:', error);
                    showFancyPopup('An error occurred while deleting user', 'error');
                });
            }
        }
        
        // Search functionality
        document.getElementById('user_search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Role filter functionality
        document.getElementById('role_filter').addEventListener('change', function(e) {
            const filterValue = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            if (!filterValue) {
                rows.forEach(row => row.style.display = '');
                return;
            }
            
            rows.forEach(row => {
                const role = row.querySelector('td:nth-child(4) span').textContent.toLowerCase();
                
                if (role.includes(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
    
    <!-- Fancy Popup Modal -->
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-modal">
            <button class="popup-close" onclick="hidePopup()">&times;</button>
            <div class="popup-icon" id="popupIcon"></div>
            <h3 class="popup-title" id="popupTitle"></h3>
            <p class="popup-message" id="popupMessage"></p>
            <button class="popup-button" onclick="hidePopup()">OK</button>
        </div>
    </div>
    
    <script>
        function showFancyPopup(message, type = 'success') {
            const popup = document.createElement('div');
            popup.className = `fancy-popup ${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            popup.innerHTML = `
                <i class="fas ${icon}"></i>
                <span class="message">${message}</span>
                <div class="progress-bar"></div>
            `;
            
            document.body.appendChild(popup);
            
            // Show popup
            setTimeout(() => popup.classList.add('show'), 100);
            
            // Remove popup after animation
            setTimeout(() => {
                popup.classList.remove('show');
                setTimeout(() => popup.remove(), 300);
            }, 3000);
        }
        
        function hidePopup() {
            const overlay = document.getElementById('popupOverlay');
            overlay.classList.remove('active');
        }
        
        // Close popup when clicking outside
        document.getElementById('popupOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                hidePopup();
            }
        });
        
        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hidePopup();
            }
        });
    </script>
<?php renderAdminFooter(); ?>
 