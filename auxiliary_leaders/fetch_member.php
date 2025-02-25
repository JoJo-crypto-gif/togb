<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $phone = $_POST['phone'];
    
    $sql = "SELECT id, last_name FROM members WHERE first_name = '$first_name' AND phone = '$phone'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
        echo json_encode(['success' => true, 'member' => $member]);
    } else {
        echo json_encode(['success' => false]);
    }
}

$conn->close();
?>
