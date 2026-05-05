<?php
require_once 'session.php';
require_once 'db_connect.php';

// Initialize variables
$token = "";
$user_id = null;
$message = "";
$message_type = "";
$token_valid = false;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token
    $sql = "SELECT id, email, reset_token_expiry FROM users WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Check if token is expired
        $expiry_time = strtotime($user['reset_token_expiry']);
        if (time() > $expiry_time) {
            $message = "Password reset link has expired. Please request a new one.";
            $message_type = "error";
        } else {
            $token_valid = true;
        }
    } else {
        $message = "Invalid password reset link.";
        $message_type = "error";
    }
} else {
    $message = "No reset token provided.";
    $message_type = "error";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($password)) {
        $message = "Password is required";
        $message_type = "error";
    } else if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters";
        $message_type = "error";
    } else if ($password !== $confirm_password) {
        $message = "Passwords do not match";
        $message_type = "error";
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Your password has been reset successfully. You can now login with your new password.";
            header("Location: login.php");
            exit;
        } else {
            $message = "Password reset failed. Please try again.";
            $message_type = "error";
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Wholesale E-commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .auth-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 16px;
        }
        
        .auth-form input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        .auth-form .error {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .auth-form .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .auth-links {
            margin-top: 20px;
            text-align: center;
        }
        
        .auth-links a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        
        .alert-error {
            background-color: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }
        
        .password-strength-meter {
            height: 5px;
            background-color: #e5e7eb;
            border-radius: var(--radius-full);
            margin-top: 5px;
            overflow: hidden;
        }
        
        .password-strength-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.3s ease;
        }
        
        .strength-weak {
            width: 25%;
            background-color: #ef4444;
        }
        
        .strength-medium {
            width: 50%;
            background-color: #f59e0b;
        }
        
        .strength-strong {
            width: 75%;
            background-color: #10b981;
        }
        
        .strength-very-strong {
            width: 100%;
            background-color: #047857;
        }
        
        .password-strength-text {
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <a href="../index.php" class="logo">
                <img src="../images/logo.svg" alt="Wholesale Logo">
                <span>Wholesale</span>
            </a>
        </div>
    </header>

    <!-- Reset Password Form -->
    <main>
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Reset Password</h1>
                    <p>Create a new password for your account</p>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($token_valid): ?>
                    <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo htmlspecialchars($token); ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required minlength="8">
                            <div class="password-strength-meter">
                                <div class="password-strength-fill"></div>
                            </div>
                            <div class="password-strength-text"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit">Reset Password</button>
                    </form>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="forgot_password.php">Request a new password reset link</a>
                    </div>
                <?php endif; ?>
                
                <div class="auth-links">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer style="margin-top: 80px;">
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 Wholesale E-commerce. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script>
        // Password strength meter
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.querySelector('.password-strength-fill');
        const strengthText = document.querySelector('.password-strength-text');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                
                // Remove all classes
                strengthMeter.classList.remove('strength-weak', 'strength-medium', 'strength-strong', 'strength-very-strong');
                
                // Check strength
                if (value.length === 0) {
                    strengthMeter.style.width = '0';
                    strengthText.textContent = '';
                } else if (value.length < 8) {
                    strengthMeter.classList.add('strength-weak');
                    strengthText.textContent = 'Weak';
                    strengthText.style.color = '#ef4444';
                } else if (value.length >= 8 && !(/[A-Z]/.test(value) || /[0-9]/.test(value) || /[^A-Za-z0-9]/.test(value))) {
                    strengthMeter.classList.add('strength-medium');
                    strengthText.textContent = 'Medium';
                    strengthText.style.color = '#f59e0b';
                } else if (value.length >= 8 && ((/[A-Z]/.test(value) && /[0-9]/.test(value)) || (/[A-Z]/.test(value) && /[^A-Za-z0-9]/.test(value)) || (/[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value)))) {
                    strengthMeter.classList.add('strength-strong');
                    strengthText.textContent = 'Strong';
                    strengthText.style.color = '#10b981';
                } else if (value.length >= 10 && /[A-Z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value)) {
                    strengthMeter.classList.add('strength-very-strong');
                    strengthText.textContent = 'Very Strong';
                    strengthText.style.color = '#047857';
                }
            });
        }
    </script>
</body>
</html> 