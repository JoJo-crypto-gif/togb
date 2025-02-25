<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if (isset($_GET['id'])) {
    $member_id = $_GET['id'];
    $sql = "SELECT * FROM members WHERE id='$member_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
    } else {
        echo "<script>alert('Member not found.'); window.location.href='dashboard.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='dashboard.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $residence = $_POST['residence'];
    $active_status = $_POST['active_status'];

    $sql = "UPDATE members SET 
            phone='$phone', 
            residence='$residence', 
            active_status='$active_status'
            WHERE id='$member_id'";

    if ($conn->query($sql) === TRUE) {
        // Add a notification for the admin
        $leader_id = $_SESSION['leader_id'];
        $sql_leader = "SELECT username FROM auxiliary_leaders WHERE id='$leader_id'";
        $result_leader = $conn->query($sql_leader);
        $leader = $result_leader->fetch_assoc();
        $leader_username = $leader['username'];

        $notification_message = $leader_username . " updated member details for " . $member['first_name'] . " " . $member['last_name'];
        $sql_notification = "INSERT INTO notifications (message, is_read, created_at) VALUES ('$notification_message', 0, NOW())";
        $conn->query($sql_notification);

        echo "<script>alert('Member updated successfully.'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating record: " . $conn->error . "'); window.location.href='dashboard.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Edit Member</title>
</head>
<body>
    <header>
        <h1>Edit Member</h1>
    </header>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="edit_profile.php">Edit Profile</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <form method="POST" action="edit_member.php?id=<?php echo $member_id; ?>">
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" required>

            <label for="residence">Residence:</label>
            <input type="text" id="residence" name="residence" value="<?php echo htmlspecialchars($member['residence']); ?>" required>

            <label for="active_status">Active Status:</label>
            <select id="active_status" name="active_status" required>
                <option value="Yes" <?php if ($member['active_status'] == 'Yes') echo 'selected'; ?>>Yes</option>
                <option value="No" <?php if ($member['active_status'] == 'No') echo 'selected'; ?>>No</option>
            </select>

            <button type="submit">Update Member</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
