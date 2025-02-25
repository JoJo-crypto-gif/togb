<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$auxiliary = $_SESSION['auxiliary'];

$sql = "SELECT * FROM expenses WHERE auxiliary=? AND expense_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $auxiliary, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Expense Report</title>
</head>
<body>
    <header>
        <h1>Expense Report</h1>
    </header>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="record_expense.php">Record Expense</a></li>
            <li><a href="expense_report_form.php">Expense Report</a></li>
            <li><a href="edit_profile.php">Edit Profile</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <section>
            <?php
            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Amount</th><th>Description</th><th>Date</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['expense_date']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No expenses found for the selected period.</p>";
            }
            ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
