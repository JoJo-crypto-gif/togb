<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance_date = $_POST['attendance_date'];
    $attendance = $_POST['attendance'];

    foreach ($attendance as $member_id => $status) {
        if ($status) { // Only insert if status is set
            $sql = "INSERT INTO attendance (member_id, attendance_date, status)
                    VALUES ('$member_id', '$attendance_date', '$status')";
            $conn->query($sql);
        }
    }

    echo "<script>alert('Attendance recorded successfully.'); window.location.href='dashboard.php';</script>";
    $conn->close();
}
?>
