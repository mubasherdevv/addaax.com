<?php
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $query = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for your message. We'll get back to you soon!";
        } else {
            $error_message = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
}

renderHeader('Contact Us | ADDAAX', 'contact');
?>

<main>
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3rem; font-weight: 900;">Contact Us</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); margin-top: 10px;">
                <a href="index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Contact Us</span>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container-wide">
            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 60px;">
                <!-- Contact Info -->
                <div>
                    <h2 style="font-size: 2rem; margin-bottom: 30px; color: var(--white);">Get in Touch</h2>
                    <p style="color: var(--text-muted); margin-bottom: 40px; line-height: 1.8;">
                        Have questions or need assistance? Our team is here to help you. Reach out to us through any of these channels or fill out the form.
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: 25px;">
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <div style="width: 50px; height: 50px; background: rgba(201, 168, 76, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-gold); font-size: 1.2rem;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 style="color: white; margin-bottom: 4px;">Location</h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">DHA Phase 5, Lahore, Pakistan</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <div style="width: 50px; height: 50px; background: rgba(201, 168, 76, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-gold); font-size: 1.2rem;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 style="color: white; margin-bottom: 4px;">Email</h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">support@ADDAAX.pk</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div style="background: var(--glass-bg); padding: 40px; border-radius: 24px; border: 1px solid var(--glass-border); backdrop-filter: blur(20px);">
                    <?php if ($success_message): ?>
                        <div style="padding: 15px; background: rgba(34, 197, 94, 0.1); color: #22c55e; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(34, 197, 94, 0.2);">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div style="padding: 15px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label style="color: white; display: block; margin-bottom: 8px; font-size: 0.9rem;">Full Name</label>
                                <input type="text" name="name" class="form-control" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white;" required>
                            </div>
                            <div class="form-group">
                                <label style="color: white; display: block; margin-bottom: 8px; font-size: 0.9rem;">Email Address</label>
                                <input type="email" name="email" class="form-control" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white;" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="color: white; display: block; margin-bottom: 8px; font-size: 0.9rem;">Subject</label>
                            <input type="text" name="subject" class="form-control" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white;" required>
                        </div>
                        <div class="form-group">
                            <label style="color: white; display: block; margin-bottom: 8px; font-size: 0.9rem;">Message</label>
                            <textarea name="message" class="form-control" rows="5" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white;" required></textarea>
                        </div>
                        <button type="submit" class="post-ad-btn" style="width: 100%; height: 50px; font-weight: 700;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>