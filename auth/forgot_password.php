<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/layout_functions.php';
require_once 'get_settings.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (Manual loading since composer might not be available or working)
if (file_exists('../vendor/phpmailer/phpmailer/autoload.php')) {
    require_once '../vendor/phpmailer/phpmailer/autoload.php';
}

// Get website settings for SMTP
$settings = getWebsiteSettings();

// Initialize variables
$email = "";
$errors = [];
$success_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    
    if (empty($email)) {
        $errors["email"] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format";
    } else {
        // Check if email exists
        $sql = "SELECT id, email, first_name FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $token_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Save token to database
            $update_sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $reset_token, $token_expiry, $user["id"]);
            
            if ($update_stmt->execute()) {
                // Create reset link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $reset_link = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/auth/reset_password.php?token=" . $reset_token;
                
                // Send Email using SMTP
                if (!empty($settings['smtp_host'])) {
                    $mail = new PHPMailer(true);

                    try {
                        //Server settings
                        $mail->isSMTP();
                        $mail->Host       = $settings['smtp_host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $settings['smtp_user'];
                        $mail->Password   = $settings['smtp_pass'];
                        $mail->SMTPSecure = ($settings['smtp_encryption'] == 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = $settings['smtp_port'];

                        //Recipients
                        $mail->setFrom($settings['smtp_from_email'], $settings['smtp_from_name']);
                        $mail->addAddress($email, $user['first_name']);

                        //Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request | ' . $settings['website_name'];
                        
                        // Email Body
                        $mail->Body    = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                <h2 style='color: #ef4444;'>Password Reset Request</h2>
                                <p>Hello " . htmlspecialchars($user['first_name']) . ",</p>
                                <p>We received a request to reset your password. If you didn't make this request, you can safely ignore this email.</p>
                                <p>To reset your password, click the button below:</p>
                                <div style='text-align: center; margin: 30px 0;'>
                                    <a href='$reset_link' style='background-color: #ef4444; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                                </div>
                                <p>Or copy and paste this link into your browser:</p>
                                <p style='word-break: break-all; color: #666;'>$reset_link</p>
                                <p>This link will expire in 1 hour.</p>
                                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                                <p style='font-size: 12px; color: #999;'>This is an automated email, please do not reply.</p>
                            </div>
                        ";

                        $mail->send();
                        $success_message = "Password reset instructions have been sent to your email address.";
                    } catch (Exception $e) {
                        $errors["email"] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    // Fallback or message if SMTP not configured
                    $success_message = "Password reset link generated (SMTP not configured): <a href='$reset_link'>$reset_link</a>";
                }
            } else {
                $errors["email"] = "An error occurred. Please try again later.";
            }
        } else {
            // Do not reveal that the email doesn't exist for security reasons
            $success_message = "If your email exists in our system, you will receive password reset instructions.";
        }
    }
}

renderHeader('Forgot Password | ADDAAX', 'forgot-password');
?>

    <style>
        .auth-page-hero { padding: 140px 0 160px; min-height: 100vh; background: #000; }
        .auth-split-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .auth-info-side h1 { font-size: 56px; font-weight: 900; line-height: 1.1; margin-bottom: 25px; }
        .auth-info-side p { font-size: 18px; color: var(--text-muted); line-height: 1.6; }
        .auth-card { background: rgba(255,255,255,0.02); backdrop-filter: blur(40px); border: 1px solid var(--glass-border); padding: 50px; border-radius: 24px; }
        
        @media (max-width: 992px) {
            .auth-split-grid { grid-template-columns: 1fr; gap: 40px; text-align: center; }
            .auth-info-side { display: flex; flex-direction: column; align-items: center; }
            .auth-info-side h1 { font-size: 42px; }
            .auth-info-side p { font-size: 16px; max-width: 600px; }
            .auth-card { padding: 35px 25px; }
        }

        @media (max-width: 576px) {
            .auth-page-hero { padding: 100px 0 100px; }
            .auth-info-side h1 { font-size: 32px; }
        }
    </style>

    <section class="auth-page-hero">
        <div class="container-wide">
            <div class="auth-split-grid">
                <div class="auth-info-side">
                    <h1>Forgot <span>Password?</span></h1>
                    <p>No worries, it happens to the best of us. Enter your registered email and we'll send you a secure link to reset it.</p>
                </div>

                <div class="auth-card-wrap">
                    <div class="auth-card">
                        <h2 class="auth-title" style="text-align: left; margin-bottom: 25px;">Reset Password</h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error" style="color: #ef4444; margin-bottom: 20px; background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 10px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo reset($errors); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success" style="color: #10b981; margin-bottom: 20px; background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 10px;">
                                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>

                        <form class="auth-form" method="post">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="example@mail.com" required>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">A reset link will be sent to this email.</p>
                            </div>

                            <button type="submit" class="post-ad-btn">Send Reset Link</button>

                            <div class="auth-footer-links" style="text-align: left; border-top: 1px solid var(--glass-border); padding-top: 25px; margin-top: 30px;">
                                <p>Remember your password? <a href="login.php" style="color: var(--accent-gold);">Back to Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
renderFooter();
?>