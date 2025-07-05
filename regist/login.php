<?php
// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../home.html");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'modbind';
$user = 'root';
$pass = 'adminroot001?';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Initialize message
$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username or email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // If no validation errors, check credentials
    if (empty($errors)) {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['message'] = "Login successful! Welcome, {$user['username']}.";
                $_SESSION['message_class'] = "success";
                // Redirect to dashboard or home
                header("Location: ../home.html");
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "Username or email not found.";
        }
        $stmt->close();
    }

    // Set error message
    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $message_class = "error";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | modbid binds</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="regist-container">
        <form class="regist-form" id="loginForm" method="POST" autocomplete="on">
            <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
            <?php if ($message): ?>
                <div id="login-message" class="message <?php echo htmlspecialchars($message_class); ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <div class="input-group">
                <label for="login-username">Username or Email</label>
                <input type="text" id="login-username" name="username" required placeholder="Enter username or email" value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn btn-primary">Login </i></button>
            <p class="switch-link">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
   
</body>
</html>