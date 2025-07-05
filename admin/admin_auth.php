<?php
session_start();

//_dmin authentication logic
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // unauthorized access
    $_SESSION['message'] = "
    Error 404!
    File not found"; // must be an admin
    $_SESSION['message_class'] = "error";
    header("Location: ../modlogin.php");
    exit();
}
?>