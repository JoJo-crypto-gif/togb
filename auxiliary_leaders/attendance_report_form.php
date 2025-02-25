<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "SELECT m.first_name, m.last_name, m.phone, a.attendance_date, a.status
            FROM attendance a
            JOIN members m ON a.member_id = m.id
            WHERE a.attendance_date BETWEEN '$start_date' AND '$end_date'
            ORDER BY a.attendance_date, m.first_name, m.last_name";
    $result = $conn->query($sql);

    $attendance_data = [];
    while ($row = $result->fetch_assoc()) {
        $attendance_data[] = $row;
    }

    $total_present = 0;
    $total_absent = 0;
    foreach ($attendance_data as $attendance) {
        if ($attendance['status'] == 'Present') {
            $total_present++;
        } else {
            $total_absent++;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>Attendance Report</h1>
    </header>
    <main>
        <h2>Report from <?php echo $start_date; ?> to <?php echo $end_date; ?></h2>
        <p>Total Present: <?php echo $total_present; ?></p>
        <p>Total Absent: <?php echo $total_absent; ?></p>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_data as $attendance) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attendance['first_name'] . " " . $attendance['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['phone']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['attendance_date']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['status']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button onclick="window.print()">Print Report</button>
    </main>
</body>
</html>
