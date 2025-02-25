<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $description = $_POST['description'];

    $sql = "INSERT INTO monthly_dues (member_id, amount, payment_date, description)
            VALUES ('$member_id', '$amount', '$payment_date', '$description')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Monthly dues recorded successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error recording payment: " . $conn->error . "'); window.location.href='dashboard.php';</script>";
    }

    $conn->close();
}
?>
