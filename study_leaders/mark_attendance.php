<?php
session_start();


// Database configuration
$servername = "localhost";
$username = "u550776502_temple";
$password = "Templeofgrace1";
$dbname = "u550776502_templedb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Check if the leader is logged insds
if (!isset($_SESSION['leader_id'])) {
    header("Location: leader_login.php"); // Redirect to login if not logged in
    exit();
}

// Get leader's ID from session
$leader_id = $_SESSION['leader_id'];

// Fetch leader's group from study_leader table
$stmt = $conn->prepare("
    SELECT sg.id AS group_id, sg.name AS group_name 
    FROM study_groups sg
    JOIN study_leaders sl ON sg.leader_id = sl.id 
    WHERE sl.id = ?
");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();

if (!$group) {
    echo "Group not found.";
    exit();
}

// Fetch members of the group
$group_id = $group['group_id'];
$stmt = $conn->prepare("
    SELECT m.id, m.first_name, m.last_name 
    FROM members m
    JOIN group_members gm ON m.id = gm.member_id 
    WHERE gm.group_id = ? AND m.active_status = 'yes'
");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$members_result = $stmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $attendance_data = $_POST['attendance'];
    $date = date('Y-m-d'); // Current date

    foreach ($attendance_data as $member_id => $status) {
        // Check if attendance for this member and date already exists
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM attendance_records 
            WHERE group_id = ? AND member_id = ? AND date = ?
        ");
        $check_stmt->bind_param("iis", $group_id, $member_id, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();

        if ($check_row['count'] > 0) {
            // If attendance already exists, update it
            $update_stmt = $conn->prepare("
                UPDATE attendance_records 
                SET status = ? 
                WHERE group_id = ? AND member_id = ? AND date = ?
            ");
            $update_stmt->bind_param("siis", $status, $group_id, $member_id, $date);
            $update_stmt->execute();
        } else {
            // If attendance does not exist, insert it
            $stmt = $conn->prepare("
                INSERT INTO attendance_records (group_id, member_id, date, status) 
                VALUES (?, ?, CURDATE(), ?)
            ");
            $stmt->bind_param("iis", $group_id, $member_id, $status);
            $stmt->execute();
        }
    }

    // Set the alert message in a JavaScript format
    echo "<script>
            alert('Attendance marked successfully.');
            window.location.href = 'dashboard.php';
          </script>";
    exit(); // Make sure to exit after the script to prevent further output
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles can be added here */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            min-width: 400px; /* Set a minimum width for the table */
            text-align: center;
        }
        th, td {
            min-width: 50px; /* Adjust the width of table cells */
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar with hamburger dropdown -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Mark Attendance</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_members.php">Add Members</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mark_attendance.php">Mark Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_attendance.php">View Attendance Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leader_login.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
    <h1 class="mb-4 fs-4"><?php echo date('F j, Y'); ?></h1>
        <form method="POST">
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <!-- <th>Member ID</th> -->
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($member = $members_result->fetch_assoc()): ?>
                            <tr>
                                <!-- <td><?php echo htmlspecialchars($member['id']); ?></td> -->
                                <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                                <td>
                                    <select name="attendance[<?php echo $member['id']; ?>]" class="form-select">
                                        <option value="absent">Absent</option>
                                        <option value="present">Present</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Submit Attendance</button>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
