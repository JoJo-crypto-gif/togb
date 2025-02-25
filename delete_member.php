<?php
include 'config.php';

function getMemberName($conn, $id) {
    $sql = "SELECT first_name, last_name FROM members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['first_name'] . ' ' . $row['last_name'];
    } else {
        return null;
    }
}

function deleteRelatedRecords($conn, $id, $relatedTables) {
    foreach ($relatedTables as $table) {
        $sql_delete_related = "DELETE FROM $table WHERE member_id = ?";
        $stmt = $conn->prepare($sql_delete_related);
        $stmt->bind_param("i", $id);
        if ($stmt->execute() !== TRUE) {
            echo "<script>alert('Error deleting related records from $table: " . $conn->error . "'); window.location.href = 'view_members.php';</script>";
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Get member name
    $member_name = getMemberName($conn, $id);

    if ($member_name) {
        // Define related tables
        $relatedTables = ['group_members', 'attendance_records', 'payment_admin']; // Add more related tables here as needed, here's an example ($relatedTables = ['group_members', 'another_table', 'yet_another_table'];)

        // Delete related records in specified tables
        deleteRelatedRecords($conn, $id, $relatedTables);

        // Proceed to delete the member
        $sql_delete_member = "DELETE FROM members WHERE id = ?";
        $stmt = $conn->prepare($sql_delete_member);
        $stmt->bind_param("i", $id);
        if ($stmt->execute() === TRUE) {
            // Insert notification
            $notification_message = "Member deleted: $member_name";
            $sql_notification = "INSERT INTO notifications (message) VALUES (?)";
            $stmt = $conn->prepare($sql_notification);
            $stmt->bind_param("s", $notification_message);
            $stmt->execute();

            echo "<script>alert('Member deleted successfully.'); window.location.href = 'view_members.php';</script>";
        } else {
            echo "<script>alert('Error deleting member: " . $conn->error . "'); window.location.href = 'view_members.php';</script>";
        }
    } else {
        echo "<script>alert('Member not found.'); window.location.href = 'view_members.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'view_members.php';</script>";
}

$conn->close();
?>
