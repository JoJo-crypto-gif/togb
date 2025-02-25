<?php
session_start();
include '../config.php'; // Include your existing config.php file

// Check if the leader is logged in
if (!isset($_SESSION['leader_id'])) {
    header("Location: leader_login.php"); // Redirect to login if not logged in
    exit();
}

// Get leader's ID
$leader_id = $_SESSION['leader_id'];

// Fetch the group name
$stmt = $conn->prepare("
    SELECT sg.id as group_id FROM study_leaders sl 
    JOIN study_groups sg ON sl.id = sg.leader_id 
    WHERE sl.id = ?
");

$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();
$group_id = $group['group_id'];

// Add member to group
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'])) {
    $member_id = $_POST['member_id'];

    // Check if the member is already in another group
    $check_stmt = $conn->prepare("SELECT * FROM group_members WHERE member_id = ?");
    $check_stmt->bind_param("i", $member_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Error: This member is already in another group.');</script>";
    } else {
        // Insert member into group_members table
        $insert_stmt = $conn->prepare("INSERT INTO group_members (group_id, member_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $group_id, $member_id);
        if ($insert_stmt->execute()) {
            echo "<script>alert('Member added to the group successfully!');</script>";
        } else {
            echo "<script>alert('Error adding member.');</script>";
        }
    }
}

// Search for members
$members = [];
if (isset($_POST['search'])) {
    $first_name = $_POST['first_name'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE first_name LIKE ? AND active_status = 'yes'");
    $search_term = "%{$first_name}%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Members to Group</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles can be added here */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            min-width: 800px; /* Set a minimum width for the table */
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
            <a class="navbar-brand" href="#">Add member</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mark_attendance.php">Mark Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_attendance.php">View Attendance Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance_report.php">View Attendance Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leader_login.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- <h1 class="mb-4 fs-4">Add Members to Your Group</h1> -->

        <form method="POST" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Search Member by First Name" required>
                <button class="btn btn-primary" type="submit" name="search">Search</button>
            </div>
        </form>

        <?php if (!empty($members)): ?>
            <h2>Search Results</h2>
            <form method="POST" action="">
                <div class="table-responsive mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <!-- <th>ID</th> -->
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><input type="radio" name="member_id" value="<?php echo $member['id']; ?>" required></td>
                                    <!-- <td><?php echo $member['id']; ?></td> -->
                                    <td><?php echo $member['first_name']; ?></td>
                                    <td><?php echo $member['last_name']; ?></td>
                                    <td><?php echo $member['phone']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success">Add Member to Group</button>
            </form>
        <?php endif; ?>
        
        <!-- <h2>Options</h2>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></li>
            <li><a href="leader_login.php" class="btn btn-danger">Logout</a></li>
        </ul> -->
    </div>

    <!-- Bootstrap JS (optional for components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
