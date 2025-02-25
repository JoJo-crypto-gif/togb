<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Retrieve auxiliary leader's information
$auxiliary_leader_id = $_SESSION['leader_id'];
$sql_leader = "SELECT * FROM auxiliary_leaders WHERE id='$auxiliary_leader_id'";
$result_leader = $conn->query($sql_leader);
$leader = $result_leader->fetch_assoc();
$for = $leader['auxiliary']; // Use the auxiliary leader's group as the `for` value

// Modify the SQL query to filter categories based on auxiliary leader's group
$sql_categories = "SELECT * FROM payment_categories WHERE `for` = ?";
$stmt = $conn->prepare($sql_categories);
$stmt->bind_param('s', $for);
$stmt->execute();
$result_categories = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Payment Report</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>Generate Payment Report</h1>
    </header>
    <main>
        <form method="POST" action="generate_report.php">
            <label for="category">Category:</label>
            <select id="category" name="category_id" required>
                <?php while ($category = $result_categories->fetch_assoc()) {
                    echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                } ?>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <button type="submit">Generate Report</button>
        </form>
    </main>
</body>
</html>
