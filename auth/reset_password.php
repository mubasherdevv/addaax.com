<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/layout_functions.php';

// Initialize variables
$token = "";
$user_id = null;
$errors = [];
$success_message = "";
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
            $errors["token"] = "Password reset link has expired. Please request a new one.";
        } else {
            $token_valid = true;
        }
    } else {
        $errors["token"] = "Invalid password reset link.";
    }
} else {
    $errors["token"] = "No reset token provided.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($password)) {
        $errors["password"] = "Password is required";
    } else if (strlen($password) < 8) {
        $errors["password"] = "Password must be at least 8 characters";
    } else if ($password !== $confirm_password) {
        $errors["password"] = "Passwords do not match";
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            // Send confirmation email
            require_once 'get_settings.php';
            $settings = getWebsiteSettings();
            
            if (!empty($settings['smtp_host'])) {
                require_once '../vendor/phpmailer/phpmailer/Exception.php';
                require_once '../vendor/phpmailer/phpmailer/PHPMailer.php';
                require_once '../vendor/phpmailer/phpmailer/SMTP.php';
                
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = $settings['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $settings['smtp_user'];
                    $mail->Password = $settings['smtp_pass'];
                    $mail->SMTPSecure = ($settings['smtp_encryption'] == 'ssl') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = $settings['smtp_port'];

                    $mail->setFrom($settings['smtp_from_email'], $settings['smtp_from_name']);
                    $mail->addAddress($user['email']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Changed Successfully';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                            <h2 style='color: #ff0000;'>Security Notification</h2>
                            <p>Hello,</p>
                            <p>This is a confirmation that the password for your account <strong>" . $user['email'] . "</strong> has been changed successfully.</p>
                            <p>If you did not make this change, please contact our support team immediately.</p>
                            <br>
                            <p>Regards,<br>ADDAAX Team</p>
                        </div>";

                    $mail->send();
                } catch (Exception $e) {
                    // Silently fail if email fails, as the password IS reset
                }
            }

            $_SESSION['success_message'] = "Your password has been reset successfully. You can now login with your new password.";
            header("Location: login.php");
            exit;
        } else {
            $errors["password"] = "Password reset failed. Please try again.";
        }
    }
}

renderHeader('Reset Password | ADDAAX', 'reset-password');
?>

    <style>
        .auth-page-hero { padding: 140px 0 160px; min-height: 100vh; background: #000; }
        .auth-split-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .auth-info-side h1 { font-size: 56px; font-weight: 900; line-height: 1.1; margin-bottom: 25px; }
        .auth-info-side p { font-size: 18px; color: var(--text-muted); line-height: 1.6; }
        .auth-card { background: rgba(255,255,255,0.02); backdrop-filter: blur(40px); border: 1px solid var(--glass-border); padding: 50px; border-radius: 24px; }
        
        .password-strength-meter { height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 10px; overflow: hidden; }
        .strength-fill { height: 100%; width: 0; transition: all 0.3s ease; }
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }

        @media (max-width: 992px) {
            .auth-split-grid { grid-template-columns: 1fr; gap: 40px; text-align: center; }
            .auth-info-side { display: flex; flex-direction: column; align-items: center; }
            .auth-info-side h1 { font-size: 42px; }
            .auth-info-side p { font-size: 16px; max-width: 600px; }
            .auth-card { padding: 35px 25px; }
        }
    </style>

    <section class="auth-page-hero">
        <div class="container-wide">
            <div class="auth-split-grid">
                <div class="auth-info-side">
                    <h1>Set New <span>Password</span></h1>
                    <p>Enter your new secure password below. Make sure it's something strong and unique to keep your account safe.</p>
                </div>

                <div class="auth-card-wrap">
                    <div class="auth-card">
                        <h2 class="auth-title" style="text-align: left; margin-bottom: 25px;">New Password</h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error" style="color: #ef4444; margin-bottom: 20px; background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 10px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo reset($errors); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($token_valid): ?>
                            <form class="auth-form" method="post">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" id="password" name="password" placeholder="••••••••" required onkeyup="checkStrength(this.value)">
                                    <div class="password-strength-meter">
                                        <div id="strength-bar" class="strength-fill"></div>
                                    </div>
                                    <p id="strength-text" style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Password strength</p>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                                </div>

                                <button type="submit" class="post-ad-btn">Update Password</button>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px 0;">
                                <i class="fas fa-link-slash" style="font-size: 48px; color: var(--text-muted); margin-bottom: 20px; display: block;"></i>
                                <p style="color: var(--text-muted);">The link is invalid or has expired.</p>
                                <a href="forgot_password.php" class="post-ad-btn" style="margin-top: 20px; display: inline-block;">Request New Link</a>
                            </div>
                        <?php endif; ?>

                        <div class="auth-footer-links" style="text-align: left; border-top: 1px solid var(--glass-border); padding-top: 25px; margin-top: 30px;">
                            <p>Back to <a href="login.php" style="color: var(--accent-gold);">Login Page</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function checkStrength(password) {
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');
            
            bar.className = 'strength-fill';
            
            if (password.length === 0) {
                bar.style.width = '0';
                text.innerText = 'Password strength';
            } else if (password.length < 6) {
                bar.classList.add('strength-weak');
                text.innerText = 'Too weak';
                text.style.color = '#ef4444';
            } else if (password.length < 10) {
                bar.classList.add('strength-medium');
                text.innerText = 'Good password';
                text.style.color = '#f59e0b';
            } else {
                bar.classList.add('strength-strong');
                text.innerText = 'Strong password';
                text.style.color = '#10b981';
            }
        }
    </script>

<?php
renderFooter();
?>