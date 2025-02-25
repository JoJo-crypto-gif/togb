<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';
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
        <h1>Generate Expense Report</h1>
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
            <form action="generate_expense_report.php" method="post">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" required>
                
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" required>
                
                <button type="submit">Generate Report</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
