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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>View Member Details</title>
</head>
<body>
    <header>
        <h1>Member Details</h1>
    </header>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="edit_profile.php">Edit Profile</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <section>
            <h2><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h2>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
            <p><strong>Auxiliary:</strong> <?php echo htmlspecialchars($member['auxiliary']); ?></p>
            <p><strong>Residence:</strong> <?php echo htmlspecialchars($member['residence']); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($member['dob']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($member['gender']); ?></p>
            <p><strong>Marital Status:</strong> <?php echo htmlspecialchars($member['marital_status']); ?></p>
            <p><strong>Active Status:</strong> <?php echo htmlspecialchars($member['active_status']); ?></p>
            <p><strong>Baptism Status:</strong> <?php echo htmlspecialchars($member['baptism_status']); ?></p>
            <?php if ($member['profile_picture']) { ?>
                <p><img src="../uploads/<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" width="150" height="150"></p>
            <?php } ?>
            <button onclick="window.print()">Print</button>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
