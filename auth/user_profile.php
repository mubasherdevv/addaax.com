<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once 'get_settings.php'; // Include website settings

// Require login to access this page
requireLogin();

// Get website settings
$website_settings = getWebsiteSettings();
$website_name = $website_settings['website_name'] ?? 'Wholesale E-commerce';
$website_logo = $website_settings['website_logo'] ?? 'logo.svg';
$favicon = $website_settings['favicon'] ?? '';

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if profile_image column exists in users table
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
if ($column_check && $column_check->num_rows === 0) {
    // Add profile_image column if it doesn't exist
    $add_column = $conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg'");
}

// Helper function to format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | <?php echo htmlspecialchars($website_name); ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($favicon)): ?>
    <link rel="icon" href="../images/<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
    /* User Profile Styles */
    .profile-container {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        padding: 0;
        overflow: hidden;
        margin: 20px 0;
    }
    
    /* Fix for sidebar profile image */
    .sidebar .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #3f51b5;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 18px;
        font-weight: 600;
        margin-right: 15px;
    }
    
    .profile-header {
        display: flex;
        padding: 40px;
        background: linear-gradient(135deg, #3f51b5 0%, #2196f3 100%);
        border-bottom: 1px solid #e5e7eb;
        color: white;
        align-items: center;
    }
    
    .profile-avatar {
        margin-right: 40px;
        text-align: center;
        position: relative;
    }
    
    .profile-avatar img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
    }
    
    .profile-avatar img:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }
    
    .avatar-upload {
        margin-top: 15px;
        position: relative;
    }
    
    .avatar-upload label {
        background-color: rgba(255, 255, 255, 0.9);
        color: #3f51b5;
        border: none;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 14px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .avatar-upload label:hover {
        background-color: white;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    .profile-info {
        flex: 1;
    }
    
    .profile-info h2 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 32px;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .profile-info p {
        margin-bottom: 12px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    .profile-info p i {
        width: 24px;
        color: rgba(255, 255, 255, 0.7);
        margin-right: 10px;
        font-size: 18px;
    }
    
    .profile-form-container {
        padding: 40px;
        background-color: #f9fafb;
    }
    
    .profile-form-container h3 {
        font-size: 24px;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
        color: #1e293b;
        font-weight: 600;
    }
    
    .form-row {
        display: flex;
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 500;
        color: #4b5563;
        font-size: 15px;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: white;
    }
    
    .form-group input:focus {
        border-color: #3f51b5;
        box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.2);
        outline: none;
    }
    
    .form-group input:hover {
        border-color: #6b7280;
    }
    
    button[type="submit"] {
        background-color: #3f51b5;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-top: 10px;
    }
    
    button[type="submit"]:hover {
        background-color: #303f9f;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    #profile-update-message {
        margin-top: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        font-size: 15px;
        display: none;
        font-weight: 500;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #profile-update-message.success {
        background-color: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    #profile-update-message.error {
        background-color: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    
    #profile-update-message.info {
        background-color: #e0f2fe;
        color: #0c4a6e;
        border-left: 4px solid #38bdf8;
    }
    
    /* Style form sections with cards */
    .profile-section {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .profile-section:last-child {
        margin-bottom: 0;
    }
    
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            padding: 30px 20px;
        }
        
        .profile-avatar {
            margin-right: 0;
            margin-bottom: 25px;
        }
        
        .profile-avatar img {
            width: 120px;
            height: 120px;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .profile-form-container {
            padding: 25px 20px;
        }
        
        .profile-info h2 {
            font-size: 24px;
        }
    }
    
    /* Dashboard Profile Animations */
    .sidebar-user {
        transition: all 0.3s ease;
        padding: 15px;
        border-radius: 8px;
    }
    
    .sidebar-user:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }
    
    .user-avatar {
        width: 50px !important;
        height: 50px !important;
        border-radius: 50% !important;
        background-color: #3f51b5;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 18px;
        font-weight: 600;
        margin-right: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.5s ease;
        position: relative;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        transition: all 0.3s ease;
    }
    
    .user-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        border-color: rgba(255, 255, 255, 0.5);
    }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <a href="../index.php" class="logo">
                <?php if (!empty($website_logo)): ?>
                <img src="../images/<?php echo htmlspecialchars($website_logo); ?>" alt="<?php echo htmlspecialchars($website_name); ?> Logo">
                <?php else: ?>
                <img src="../images/logo.svg" alt="<?php echo htmlspecialchars($website_name); ?> Logo">
                <?php endif; ?>
                <span><?php echo htmlspecialchars($website_name); ?></span>
            </a>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav id="mainNav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../products.php">Products</a></li>
                    <li><a href="../categories.php">Categories</a></li>
                    <li><a href="../about.php">About Us</a></li>
                    <li><a href="../contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="user-actions">
                <a href="dashboard.php" class="btn btn-icon btn-primary"><i class="fas fa-user"></i> <span>My Account</span></a>
                <a href="logout.php" class="btn btn-icon"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </div>
        </div>
    </header>

    <!-- Dashboard -->
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>My Profile</h1>
            <p>Manage your account information</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="sidebar">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <?php 
                        // Check if profile_image column exists and has a value
                        $has_profile_image = false;
                        $profile_image = '';
                        
                        // Get column information
                        $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
                        if ($column_check && $column_check->num_rows > 0) {
                            // Get user profile image
                            $img_query = "SELECT profile_image FROM users WHERE id = ?";
                            $stmt = $conn->prepare($img_query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result && $row = $result->fetch_assoc()) {
                                if (!empty($row['profile_image'])) {
                                    $has_profile_image = true;
                                    $profile_image = $row['profile_image'];
                                }
                            }
                        }
                        
                        if ($has_profile_image):
                        ?>
                        <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" id="sidebar-profile-img">
                        <?php else: 
                            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                            echo $initials;
                        endif; ?>
                    </div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    </div>
                </div>
                
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="user_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="user_profile.php" class="active"><i class="fas fa-user-circle"></i> My Profile</a></li>
                    <li><a href="wishlist.php"><i class="fas fa-heart"></i> My Wishlist</a></li>
                    <li><a href="user_reviews.php"><i class="fas fa-star"></i> My Reviews</a></li>
                    <li><a href="recent_products.php"><i class="fas fa-box"></i> Recent Products</a></li>
                </ul>
            </div>
            
            <!-- Sidebar Toggle for Mobile -->
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Sidebar Overlay for Mobile -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            
            <div class="dashboard-content">
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                            $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default-avatar.jpg';
                            ?>
                            <img src="../images/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" id="profile-image-preview">
                            <div class="avatar-upload">
                                <label for="profile_image" class="btn btn-small">Change Image</label>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Not specified'); ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-form-container">
                        <div class="profile-section">
                            <h3>Update Profile Information</h3>
                            <form id="update-profile-form" method="post" enctype="multipart/form-data">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            
                                <div class="profile-section">
                                    <h3>Change Password</h3>
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password">
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" id="new_password" name="new_password">
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <div id="profile-update-message"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 Wholesale E-commerce. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Function to update profile image across pages
        function updateProfileImageAcrossPages(imagePath) {
            // Store the updated image path in sessionStorage
            if (imagePath) {
                sessionStorage.setItem('updated_profile_image', imagePath);
                console.log('Stored profile image update in session storage:', imagePath);
            }
        }
        
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden'; // Prevent scrolling when sidebar is open
            } else {
                document.body.style.overflow = '';
            }
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
        
        // Profile image preview with animation
        $('#profile_image').on('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                
                // Show loading state
                $('#profile-image-preview').css('opacity', '0.5');
                
                reader.onload = function(e) {
                    // Animate the image change
                    $('#profile-image-preview').fadeOut(300, function() {
                        $(this).attr('src', e.target.result);
                        $(this).fadeIn(300);
                        $(this).css('opacity', '1');
                    });
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Enhance button effects
        $('.avatar-upload label').on('mousedown', function() {
            $(this).css('transform', 'translateY(1px)');
        }).on('mouseup mouseleave', function() {
            $(this).css('transform', '');
        });
        
        // Trigger file input when button is clicked
        $('.avatar-upload label').on('click', function(e) {
            e.preventDefault();
            $('#profile_image').click();
        });
        
        // Add form field animation on focus
        $('.form-group input').on('focus', function() {
            $(this).closest('.form-group').find('label').css({
                'color': '#3f51b5',
                'transition': 'color 0.3s'
            });
        }).on('blur', function() {
            $(this).closest('.form-group').find('label').css('color', '');
        });
        
        // Submit profile update form with better feedback
        $('#update-profile-form').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading button state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            submitBtn.prop('disabled', true);
            
            // Password validation
            var currentPassword = $('#current_password').val();
            var newPassword = $('#new_password').val();
            var confirmPassword = $('#confirm_password').val();
            
            // If changing password, validate
            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    showProfileMessage('Current password is required to change password', 'error');
                    resetButton();
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showProfileMessage('New passwords do not match', 'error');
                    resetButton();
                    return;
                }
                
                if (newPassword.length < 8) {
                    showProfileMessage('Password must be at least 8 characters long', 'error');
                    resetButton();
                    return;
                }
            }
            
            // Create form data including the file
            var formData = new FormData();
            formData.append('first_name', $('#first_name').val());
            formData.append('last_name', $('#last_name').val());
            formData.append('email', $('#email').val());
            formData.append('phone', $('#phone').val());
            
            // Add password fields if changing password
            if (newPassword) {
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
            }
            
            // Add profile image if selected
            if ($('#profile_image')[0].files.length > 0) {
                formData.append('profile_image', $('#profile_image')[0].files[0]);
            }
            
            // Show loading message
            showProfileMessage('Updating profile...', 'info');
            
            // Send AJAX request
            $.ajax({
                url: 'update_profile.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        // Success animation
                        showProfileMessage('<i class="fas fa-check-circle"></i> ' + response.message, 'success');
                        
                        // Clear password fields
                        $('#current_password').val('');
                        $('#new_password').val('');
                        $('#confirm_password').val('');
                        
                        // Update session name if available
                        if (response.user_name) {
                            // Update sidebar username
                            $('.user-details h3').fadeOut(300, function() {
                                $(this).text(response.user_name).fadeIn(300);
                            });
                            
                            // Update profile header
                            $('.profile-info h2').fadeOut(300, function() {
                                $(this).text(response.user_name).fadeIn(300);
                            });
                        }
                        
                        // Update sidebar profile image if available
                        if (response.profile_image) {
                            console.log('Updating profile image to:', response.profile_image);
                            
                            // Use the function to update across pages
                            updateProfileImageAcrossPages(response.profile_image);
                            
                            // Create a new Image object to ensure the image is loaded before showing it
                            var newImg = new Image();
                            newImg.onload = function() {
                                // Update profile image in the current page
                                $('.profile-avatar img').fadeOut(300, function() {
                                    $(this).attr('src', '../images/' + response.profile_image)
                                           .on('load', function() {
                                                $(this).fadeIn(300);
                                           })
                                           .on('error', function() {
                                                console.error('Failed to load image:', '../images/' + response.profile_image);
                                                // Fallback to default image if load fails
                                                $(this).attr('src', '../images/default-avatar.jpg').fadeIn(300);
                                           });
                                });
                                
                                // Update sidebar avatar if it has an image
                                if ($('.user-avatar img').length) {
                                    $('.user-avatar img').fadeOut(300, function() {
                                        $(this).attr('src', '../images/' + response.profile_image).fadeIn(300);
                                    });
                                } else {
                                    // If sidebar has initials instead of image, replace with image
                                    var initials = $('.user-avatar').text().trim();
                                    $('.user-avatar').html('');
                                    $('<img>', {
                                        id: 'sidebar-profile-img',
                                        src: '../images/' + response.profile_image,
                                        alt: 'Profile',
                                        css: {opacity: 0}
                                    }).appendTo('.user-avatar').animate({opacity: 1}, 300);
                                }
                            };
                            
                            newImg.onerror = function() {
                                console.error('Failed to preload image:', '../images/' + response.profile_image);
                                showProfileMessage('<i class="fas fa-exclamation-circle"></i> Profile updated but image couldn\'t be loaded', 'warning');
                            };
                            
                            // Start loading the image
                            newImg.src = '../images/' + response.profile_image;
                        }
                        
                        // Highlight updated fields with subtle animation
                        $('.form-group input').not('#current_password, #new_password, #confirm_password')
                            .css('background-color', 'rgba(209, 250, 229, 0.3)')
                            .delay(1000)
                            .animate({backgroundColor: 'white'}, 1000);
                    } else {
                        showProfileMessage('<i class="fas fa-exclamation-circle"></i> ' + response.message, 'error');
                    }
                    resetButton();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    try {
                        var response = JSON.parse(xhr.responseText);
                        showProfileMessage('<i class="fas fa-exclamation-triangle"></i> ' + (response.message || 'An error occurred'), 'error');
                    } catch (e) {
                        showProfileMessage('<i class="fas fa-exclamation-triangle"></i> An error occurred while updating profile', 'error');
                    }
                    resetButton();
                }
            });
            
            // Helper function to reset button state
            function resetButton() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
        
        // Function to show profile update messages with animation
        function showProfileMessage(message, type) {
            var messageElement = $('#profile-update-message');
            messageElement.removeClass('success error info');
            messageElement.addClass(type);
            messageElement.html(message);
            
            // Slide down animation
            if (messageElement.is(':hidden')) {
                messageElement.slideDown(300);
            } else {
                messageElement.fadeOut(200, function() {
                    $(this).html(message).fadeIn(200);
                });
            }
            
            // Hide after 5 seconds if success
            if (type === 'success') {
                setTimeout(function() {
                    messageElement.slideUp(300);
                }, 5000);
            }
        }
        
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mainNav = document.getElementById('mainNav');
        
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('active');
                this.classList.toggle('active');
            });
        }
    });
    </script>
</body>
</html> 