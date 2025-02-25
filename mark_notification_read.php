<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (isset($_POST['clear_all'])) {
    // Delete all notifications
    $sql_delete_notifications = "DELETE FROM notifications";
    if ($conn->query($sql_delete_notifications) === TRUE) {
        $_SESSION['message'] = "All notifications cleared.";
    } else {
        $_SESSION['message'] = "Error clearing notifications: " . $conn->error;
    }
} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: notifications.php");
exit();
?>
