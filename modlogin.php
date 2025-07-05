<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/sales.php");
    exit();
}

//login via POST
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    //DB connection
    $host = 'localhost';
    $db = 'modbind';
    $user = 'root';
    $pass = 'adminroot001?';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        $message = "Database connection failed.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE (username = ? OR email = ?) AND role = 'admin' LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role'] = $admin['role'];
                header("Location: admin/sales.php");
                exit();
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "Admin user not found or not authorized.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>modbid || Admin Login</title>
    <link rel="stylesheet" href="mod.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <a class="nav-link active" href="#"><i class="fas fa-user-shield"></i> Admin Login</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="container" style="max-width:400px;">
            <h2>Admin Login</h2>
            <?php if ($message): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="input-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required placeholder="Enter admin username or email">
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary btn-small">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>