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

//handle contact form submission
$message = '';
$message_class = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message_form = trim($_POST['message']);

    $errors = [];
    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name required. At least have 100 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($message_form) || strlen($message_form) > 1000) {
        $errors[] = "Message required. At least have 1000 characters.";
    }

    if (empty($errors)) 
    {
        $stmt = $conn->prepare("INSERT INTO cont_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message_form);
        if ($stmt->execute()) {
            $message = "Message sent☑";
            $message_class = "success";
            $name = $email = $message_form = '';
        } else {
            $message = "❌ 
            Failed to send Please retry.";
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
    <title>Contact Us | modbid binds</title>
    <link rel="stylesheet" href="mod.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <a class="nav-link" href="home.php"><i class="fas fa-home"></i> Home</a>
            <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a class="nav-link active" href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <h2><i class="fas fa-envelope"></i> Contact Us</h2>
            <?php if ($message): ?>
                <div class="message <?php echo htmlspecialchars($message_class); ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form id="contact-form" method="POST" class="regist-form">
                <div class="input-grid">
                    <div class="input-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required placeholder="Enter your message" rows="5"><?php echo htmlspecialchars($message_form ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Send Message <i class="fas fa-spinner fa-spin hidden"></i></button>
                </div>
            </form>
            <div class="contact-info">
                <h3>Reach Us On:</h3>
                <p><i class="fab fa-whatsapp"></i> WhatsApp: <a href="https://wa.me/+1234567890" target="_blank">+1234567890</a></p>
                <p><i class="fab fa-telegram"></i> Telegram: <a href="https://t.me/modbid_support" target="_blank">@modbid_support</a></p>
                <p><i class="fas fa-envelope"></i> Email: <a href="mailto:support@modbid.com">support@modbid.com</a></p>
            </div>
        </div>
    </div>
    <script src="script.js"></script>//initially register.js
</body>
</html>