<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['auxiliary'])) {
        $auxiliary = $_SESSION['auxiliary'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $expense_date = $_POST['expense_date'];

        $sql = "INSERT INTO expenses (auxiliary, amount, description, expense_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdss', $auxiliary, $amount, $description, $expense_date);

        if ($stmt->execute()) {
            $message = "Expense recorded successfully.";
        } else {
            $message = "Error recording expense: " . $conn->error;
        }
    } else {
        $message = "Auxiliary not set in session.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Record Expense</title>
</head>
<body>
    <header>
        <h1>Record Expense</h1>
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
            <?php if (isset($message)) echo "<p>$message</p>"; ?>
            <form action="record_expense.php" method="post">
                <label for="amount">Amount:</label>
                <input type="number" step="0.01" name="amount" id="amount" required>
                
                <label for="description">Description:</label>
                <textarea name="description" id="description" required></textarea>
                
                <label for="expense_date">Date:</label>
                <input type="date" name="expense_date" id="expense_date" required>
                
                <button type="submit">Record Expense</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
