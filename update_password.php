<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    $sql = "SELECT * FROM admins WHERE reset_token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE admins SET password='$hashed_password', reset_token=NULL, reset_requested_at=NULL WHERE reset_token='$token'";
        if ($conn->query($sql) === TRUE) {
            // Password changed successfully, redirect to login page with success message
            header("Location: login.php?message=password_changed");
            exit();
        } else {
            echo "Error updating password: " . $conn->error;
        }
    } else {
        echo "Invalid or expired reset token.";
    }
}

$conn->close();
?>
