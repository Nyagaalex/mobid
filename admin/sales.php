<?php
session_start();

//only allow admin users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../modlogin.php");
    exit();
}

$host = 'localhost';
$db = 'modbind';
$user = 'root';
$pass = 'adminroot001?';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

//fetch sales/purchases
$sql = "SELECT p.id, p.user_id, u.username, p.serial, p.idno, p.amount, p.status, p.created_at
        FROM purchases p
        LEFT JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Tracking | Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        .sales-table th, .sales-table td {
            border: 1px solid #ccc;
            padding: 0.7rem 1rem;
            text-align: left;
        }
        .sales-table th {
            background: #4e54c8;
            color: #fff;
        }
        .status-success { color: #2e7d32; font-weight: bold; }
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-canceled { color: #d32f2f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sales & Purchases Tracking</h2>
        <table class="sales-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Serial</th>
                    <th>ID Number</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): $i = 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($row['serial']); ?></td>
                            <td><?php echo htmlspecialchars($row['idno']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td class="status-<?php echo strtolower($row['status']); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No sales or purchases found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>