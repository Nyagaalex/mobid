<?php
session_start();

//db connection
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

//fetch user data
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

//profile update handling
$message = '';
$message_class = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $errors = [];

    if (strlen($new_username) < 3 || strlen($new_username) > 50) {
        $errors[] = "Username must be 3-50 characters.";
    }
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    //check for duplicates
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) 
    {
        $errors[] = "Username or email already exists.";//error class
    }
    $stmt->close();

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            $message = "Profile updated successfully!";
            $message_class = "success";
            $user['username'] = $new_username;
            $user['email'] = $new_email;
        } else {
            $message = "Update failed! Retry.";
            $message_class = "error";
        }
        $stmt->close();
    } else {
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
    <title>Profile | modbid binds</title>
    <link rel="stylesheet" href="mod.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <a class="nav-link" href="mod.php"><i class="fas fa-home"></i> Home</a>
            <a class="nav-link active" href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <h2><i class="fas fa-user"></i> Your Profile</h2>
            <?php if ($message): ?>
                <div class="message <?php echo htmlspecialchars($message_class); ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form id="profile-form" method="POST" class="regist-form">
                <div class="input-group">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Enter username" value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Enter email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Update Profile <i class="fas fa-spinner fa-spin hidden"></i></button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>