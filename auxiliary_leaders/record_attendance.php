<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

$sql_members = "SELECT * FROM members";
$result_members = $conn->query($sql_members);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Attendance</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        function markAttendance(memberId, status) {
            document.getElementById('status_' + memberId).value = status;
            document.getElementById('button_present_' + memberId).style.backgroundColor = status === 'Present' ? 'green' : '';
            document.getElementById('button_absent_' + memberId).style.backgroundColor = status === 'Absent' ? 'red' : '';
        }
    </script>
</head>
<body>
    <header>
        <h1>Record Attendance</h1>
    </header>
    <main>
        <form method="POST" action="submit_attendance.php">
            <label for="attendance_date">Attendance Date:</label>
            <input type="date" id="attendance_date" name="attendance_date" required>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $result_members->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['first_name'] . " " . $member['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone']); ?></td>
                            <td>
                                <button type="button" id="button_present_<?php echo $member['id']; ?>" onclick="markAttendance(<?php echo $member['id']; ?>, 'Present')">Present</button>
                                <button type="button" id="button_absent_<?php echo $member['id']; ?>" onclick="markAttendance(<?php echo $member['id']; ?>, 'Absent')">Absent</button>
                                <input type="hidden" id="status_<?php echo $member['id']; ?>" name="attendance[<?php echo $member['id']; ?>]" value="">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <button type="submit">Submit Attendance</button>
        </form>
    </main>
</body>
</html>
