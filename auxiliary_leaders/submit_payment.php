<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payer_name = $_POST['payer_name'];
    $payer_phone = $_POST['payer_phone'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $payment_date = $_POST['payment_date'];
    $leader_id = $_SESSION['leader_id'];

    $sql = "INSERT INTO payments (payer_name, payer_phone, category_id, amount, description, payment_date)
            VALUES ('$payer_name', '$payer_phone', '$category_id', '$amount', '$description', '$payment_date')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Payment recorded successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error recording payment: " . $conn->error . "'); window.location.href='dashboard.php';</script>";
    }

    $conn->close();
}
?>
