<?php
require_once 'session.php';
require_once 'db_connect.php';

// Initialize variables
$email = "";
$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"])) {
        $message = "Email is required";
        $message_type = "error";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format";
            $message_type = "error";
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
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/auth/reset_password.php?token=" . $reset_token;
                    
                    // In a real application, send email with reset link
                    // For this example, we'll set a success message with the link
                    $message = "Password reset instructions have been sent to your email address. (For demo purposes, use this link: <a href='" . $reset_link . "'>Reset Password</a>)";
                    $message_type = "success";
                } else {
                    $message = "An error occurred. Please try again later.";
                    $message_type = "error";
                }
            } else {
                // Do not reveal that the email doesn't exist for security reasons
                $message = "If your email exists in our system, you will receive password reset instructions.";
                $message_type = "success";
            }
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
    <title>Forgot Password | Wholesale E-commerce</title>
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

    <!-- Forgot Password Form -->
    <main>
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Forgot Password</h1>
                    <p>Enter your email to receive password reset instructions</p>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-submit">Reset Password</button>
                    
                    <div class="auth-links">
                        <a href="login.php">Back to Login</a>
                    </div>
                </form>
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
</body>
</html> 