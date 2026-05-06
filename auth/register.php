<?php
require_once 'session.php';
require_once 'db_connect.php';
require_once '../includes/layout_functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isset($_GET['return_to'])) {
        header("Location: ../" . $_GET['return_to']);
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$email = $first_name = $last_name = $phone = "";
$errors = [];
$return_to = isset($_GET['return_to']) ? $_GET['return_to'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $first_name = trim($_POST["first_name"] ?? '');
    $last_name = trim($_POST["last_name"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $password = $_POST["password"] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors["register"] = "All fields are required";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, first_name, last_name, phone, is_verified) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $email, $hashed_password, $first_name, $last_name, $phone);
        
        if ($stmt->execute()) {
            header("Location: login.php?msg=Registration successful! Please login.");
            exit;
        } else {
            $errors["register"] = "Registration failed. Email might already be in use.";
        }
    }
}

renderHeader('Create Account | ADDAAX Premium', 'register');
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
            .auth-card { padding: 35px 25px; margin: 0 auto; }
        }

        @media (max-width: 576px) {
            .auth-page-hero { padding: 100px 0 100px; }
            .auth-info-side h1 { font-size: 32px; }
            .responsive-grid { grid-template-columns: 1fr !important; gap: 20px !important; }
        }
    </style>

    <section class="auth-page-hero">
        <div class="container-wide">
            <div class="auth-split-grid">
                <div class="auth-info-side">
                    <h1>Start Your <span>Premium</span> Journey</h1>
                    <p>Join thousands of advertisers reaching millions of potential clients across Pakistan. Create your account today.</p>
                </div>

                <div class="auth-card-wrap">
                    <div class="auth-card" style="max-width: 550px;">
                        <h2 class="auth-title" style="text-align: left; margin-bottom: 25px;">Create Account</h2>

                        <form class="auth-form" method="post">
                            <div class="responsive-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" placeholder="John" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" placeholder="Doe" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" placeholder="+92 3XX XXXXXXX" required>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" placeholder="example@mail.com" required>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" required>
                            </div>

                            <button type="submit" class="post-ad-btn">Create Account Now</button>

                            <div class="auth-footer-links" style="text-align: left; border-top: 1px solid var(--glass-border); padding-top: 25px; margin-top: 30px;">
                                <p>Already have an account? <a href="login.php" style="color: var(--accent-gold);">Sign In</a></p>
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