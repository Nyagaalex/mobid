<?php
//start session
session_start();

//db connect
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

//initialize message
$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    //sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    //validation
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    //check if username or email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }

    //if no errors, insert user
    if (empty($errors)) 
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
            $message_class = "success";
        } else {
            $message = "Registration failed. Please try again.";
            $message_class = "error";
        }
        $stmt->close();
    } 
    else 
    {
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
    <title>Register | modbid binds</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="regist-container">
        <form class="regist-form" id="registerForm" method="POST" autocomplete="off">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <?php if ($message): ?>
                <div id="reg-message" class="message <?php echo htmlspecialchars($message_class); ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <div class="input-group">
                <label for="reg-username">Username</label>
                <input type="text" id="reg-username" name="username" required placeholder="Enter username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="reg-email">Email</label>
                <input type="email" id="reg-email" name="email" required placeholder="Enter email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="reg-password">Password</label>
                <input type="password" id="reg-password" name="password" required placeholder="Enter password" minlength="6">
            </div>
            <div class="input-group">
                <label for="reg-confirm">Confirm Password</label>
                <input type="password" id="reg-confirm" name="confirm" required placeholder="Confirm password" minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <p class="switch-link">Already have an account? <a href="login.php">Sign in</a></p>
        </form>
    </div>
    
</body>
</html>