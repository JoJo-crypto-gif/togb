<?php
session_start();
include '../config.php'; // Include your existing config.php file

// Check if the leader is logged in
if (!isset($_SESSION['leader_id'])) {
    header("Location: leader_login.php"); // Redirect to login if not logged in
    exit();
}

// Check if the form has been submitted with the necessary data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'], $_POST['group_id'])) {
    $member_id = $_POST['member_id'];
    $group_id = $_POST['group_id'];

    // Delete the member from the group
    $stmt = $conn->prepare("DELETE FROM group_members WHERE member_id = ? AND group_id = (SELECT id FROM study_groups WHERE name = ?)");
    $stmt->bind_param("is", $member_id, $group_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?success=MemberRemoved"); // Redirect back to the dashboard with a success message
        exit();
    } else {
        echo "Error removing member.";
    }
} else {
    header("Location: dashboard.php?error=InvalidRequest"); // Redirect if accessed improperly
    exit();
}
?>
