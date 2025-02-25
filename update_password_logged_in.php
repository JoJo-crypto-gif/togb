<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    $admin_id = $_SESSION['admin_id'];

    $sql = "SELECT * FROM admins WHERE id='$admin_id'";
    $result = $conn->query($sql);
    $admin = $result->fetch_assoc();

    if (password_verify($current_password, $admin['password'])) {
        $sql = "UPDATE admins SET password='$new_password' WHERE id='$admin_id'";
        if ($conn->query($sql) === TRUE) {
            echo "Password updated successfully.";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Current password is incorrect.";
    }
}

$conn->close();
?>
