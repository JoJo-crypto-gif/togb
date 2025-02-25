<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payer_name = $_POST['payer_name'];
    $payer_phone = $_POST['payer_phone'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];

    $sql = "INSERT INTO payments (payer_name, payer_phone, category_id, amount, payment_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssids", $payer_name, $payer_phone, $category_id, $amount, $payment_date);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: record_payment.php");
    exit();
}

$auxiliary_leader_type = $_SESSION['auxiliary'];
$sql_categories = "SELECT * FROM payment_categories WHERE `for` = ?";
$stmt_categories = $conn->prepare($sql_categories);
$stmt_categories->bind_param("s", $auxiliary_leader_type);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Payment</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>Record Payment</h1>
    </header>
    <main>
        <form method="post" action="record_payment.php">
            <label for="payer_name">Payer Name:</label>
            <input type="text" id="payer_name" name="payer_name" required>
            <label for="payer_phone">Payer Phone:</label>
            <input type="text" id="payer_phone" name="payer_phone" required>
            <label for="category_id">Payment Category:</label>
            <select id="category_id" name="category_id" required>
                <?php
                if ($result_categories->num_rows > 0) {
                    while ($row = $result_categories->fetch_assoc()) {
                        echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['name']) . "</option>";
                    }
                } else {
                    echo "<option value=\"\">No categories found</option>";
                }
                ?>
            </select>
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" required>
            <label for="payment_date">Payment Date:</label>
            <input type="date" id="payment_date" name="payment_date" required>
            <button type="submit">Record Payment</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
