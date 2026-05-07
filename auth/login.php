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

$email = "";
$errors = [];
$return_to = isset($_GET['return_to']) ? $_GET['return_to'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors["login"] = "Email and password are required";
    } else {
        $sql = "SELECT id, email, password, first_name, last_name, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_name"] = $user["first_name"] . " " . $user["last_name"];
                $_SESSION["user_role"] = $user["role"];
                
                if (!empty($_POST['return_to'])) {
                    header("Location: ../" . ltrim($_POST['return_to'], '/'));
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        }
        $errors["login"] = "Invalid email or password";
    }
}

renderHeader('Login | ADDAAX', 'login');
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
            .auth-card-wrap { width: 100%; }
        }
    </style>

    <section class="auth-page-hero">
        <div class="container-wide">
            <div class="auth-split-grid">
                <div class="auth-info-side">
                    <h1>Access Your <span>Premium</span> Account</h1>
                    <p>Log in to manage your listings, respond to messages, and reach more clients on Pakistan's #1 premium directory.</p>
                </div>

                <div class="auth-card-wrap">
                    <div class="auth-card">
                        <h2 class="auth-title" style="text-align: left; margin-bottom: 25px;">Sign In</h2>

                        <?php if (isset($errors["login"])): ?>
                            <div class="alert alert-error" style="color: #ef4444; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $errors["login"]; ?>
                            </div>
                        <?php endif; ?>

                        <form class="auth-form" method="post">
                            <?php if (!empty($return_to)): ?>
                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to); ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="example@mail.com" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="••••••••" required>
                            </div>

                            <button type="submit" class="post-ad-btn">Log In Now</button>

                            <div class="auth-footer-links" style="text-align: left; border-top: 1px solid var(--glass-border); padding-top: 25px; margin-top: 30px;">
                                <p>New here? <a href="register.php<?php echo !empty($return_to) ? '?return_to=' . urlencode($return_to) : ''; ?>" style="color: var(--accent-gold);">Create an Account</a></p>
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