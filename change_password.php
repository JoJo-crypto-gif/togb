<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <title>Change Password</title>
</head>
<body>
    <header>
        <h1>Change Password</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="add_member.php">Add Member</a></li>
            <li><a href="view_members.php">View Members</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <form id="change-password-form" action="update_password_logged_in.php" method="post">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required><br>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required><br>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>
            <input type="submit" value="Change Password">
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
    <script src="js/scripts.js"></script>
</body>
</html>
