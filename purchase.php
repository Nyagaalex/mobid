<?php
session_start();
$host = 'localhost';
$db = 'modbind';
$user = 'root';
$pass = 'adminroot001?';

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateSerialNumber() {
    return str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
}

function generateIDNumber() {
    return str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
}

$message = '';
$message_class = '';
$idData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    // Validate required fields
    if (empty($_POST['surname'])) $errors[] = "Surname is required.";
    if (empty($_POST['fname'])) $errors[] = "First name is required.";
    if (empty($_POST['gender'])) $errors[] = "Gender is required.";
    if (empty($_POST['dob'])) $errors[] = "Date of birth is required.";
    if (empty($_POST['placeOfBirth'])) $errors[] = "Place of birth is required.";
    if (empty($_POST['issuePlace2'])) $errors[] = "Place of issue is required.";

    if (empty($errors)) {
        $serial = generateSerialNumber();
        $idno = generateIDNumber();
        $surname = $_POST['surname'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'] ?? '';
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $place_of_birth = $_POST['placeOfBirth'];
        $place_of_issue = $_POST['issuePlace2'];
        $date_of_issue = date('Y-m-d');
        $date_of_expiry = date('Y-m-d', strtotime('+10 years'));

        //use user_id if available, else set to NULL
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $stmt = $conn->prepare("INSERT INTO mod_ids (user_id, serial, idno, surname, fname, lname, gender, dob, place_of_birth, place_of_issue, date_of_issue, date_of_expiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssssss",
            $user_id,
            $serial,
            $idno,
            $surname,
            $fname,
            $lname,
            $gender,
            $dob,
            $place_of_birth,
            $place_of_issue,
            $date_of_issue,
            $date_of_expiry
        );

        if ($stmt->execute()) {
            $message = "Purchase Successful! ID generated.";
            $message_class = "success";
            $idData = [
                'serial' => $serial,
                'idno' => $idno,
                'surname' => $surname,
                'fname' => $fname,
                'lname' => $lname,
                'gender' => $gender,
                'dob' => $dob,
                'placeOfBirth' => $place_of_birth,
                'issuePlace2' => $place_of_issue,
                'dateOfIssue' => $date_of_issue,
                'dateOfExpiry' => $date_of_expiry
            ];
        } else {
            $message = "Failed to save ID. " . $stmt->error;
            $message_class = "error";
        }
        $stmt->close();
    } else {
        $message = implode("<br>", $errors);
        $message_class = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase ID | modbid binds</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(120deg, #e2e2e2 0%, #667ead 100%);
            font-family: 'Poppins', sans-serif;
        }
        .purchase-container {
            max-width: 500px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(102,126,173,0.13);
            padding: 2.5rem 2rem 2rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .message.success {
            color: #2e7d32;
            background: #e8f5e9;
            border-radius: 6px;
            padding: 10px 18px;
            margin-bottom: 1.2rem;
            font-weight: 500;
        }
        .message.error {
            color: #d32f2f;
            background: #ffebee;
            border-radius: 6px;
            padding: 10px 18px;
            margin-bottom: 1.2rem;
            font-weight: 500;
        }
        .id-preview {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f5f6fa;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(102,126,173,0.07);
            text-align: center;
        }
        .id-label {
            font-weight: bold;
            color: #4e54c8;
            margin-bottom: 0.5rem;
        }
        .id-fields {
            text-align: left;
            margin: 0 auto;
            max-width: 320px;
        }
        .id-fields span {
            display: block;
            margin-bottom: 0.3rem;
        }
        .id-image {
            margin: 1rem 0;
            border-radius: 8px;
            border: 1.5px solid #667ead;
            background: #fff;
            width: 320px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .id-image img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 6px;
        }
        .btn-home {
            margin-top: 1.5rem;
            background: #4e54c8;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 28px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-home:hover {
            background: #3b3f9e;
        }
    </style>
</head>
<body>
    <div class="purchase-container">
        <h2>Purchase ID</h2>
        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_class === "success" && !empty($idData)): ?>
            <div class="id-preview">
                <div class="id-label">Your New Kenyan ID</div>
                <div class="id-image">
                    <img src="https://dummyimage.com/320x200/4e54c8/ffffff&text=Kenyan+ID+Preview" alt="ID Preview">
                </div>
                <div class="id-fields">
                    <span><strong>Serial:</strong> <?php echo htmlspecialchars($idData['serial']); ?></span>
                    <span><strong>ID Number:</strong> <?php echo htmlspecialchars($idData['idno']); ?></span>
                    <span><strong>Name:</strong> <?php echo htmlspecialchars($idData['surname'] . ' ' . $idData['fname'] . ' ' . $idData['lname']); ?></span>
                    <span><strong>Gender:</strong> <?php echo htmlspecialchars($idData['gender']); ?></span>
                    <span><strong>Date of Birth:</strong> <?php echo htmlspecialchars($idData['dob']); ?></span>
                    <span><strong>Place of Birth:</strong> <?php echo htmlspecialchars($idData['placeOfBirth']); ?></span>
                    <span><strong>Place of Issue:</strong> <?php echo htmlspecialchars($idData['issuePlace2']); ?></span>
                    <span><strong>Date of Issue:</strong> <?php echo htmlspecialchars($idData['dateOfIssue']); ?></span>
                    <span><strong>Date of Expiry:</strong> <?php echo htmlspecialchars($idData['dateOfExpiry']); ?></span>
                </div>
            </div>
            <a href="home.php" class="btn-home">Back to Home</a>
        <?php else: ?>
            <a href="home.php" class="btn-home">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>