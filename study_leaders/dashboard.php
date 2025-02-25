<?php
session_start();
include '../config.php'; // Include your existing config.php file

// Check if the leader is logged in
if (!isset($_SESSION['leader_id'])) {
    header("Location: leader_login.php"); // Redirect to login if not logged in
    exit();
}

// Pagination setup
$limit = 10; // Number of members per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get leader's ID
$leader_id = $_SESSION['leader_id'];

// Fetch the leader's details and group name from the `study_leader` and `study_groups` tables
$stmt = $conn->prepare("
    SELECT sl.first_name, sl.last_name, sg.name AS group_name, sg.id AS group_id
    FROM study_leaders sl
    JOIN study_groups sg ON sl.id = sg.leader_id 
    WHERE sl.id = ?
");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
$leader = $result->fetch_assoc();

if (!$leader) {
    echo "Leader not found.";
    exit();
}

$group_id = $leader['group_id'];

// Fetch members of the leader's group and their attendance percentage
$stmt = $conn->prepare("
    SELECT m.id, m.first_name, m.last_name, m.phone, 
           (SELECT COUNT(*) FROM attendance_records ar WHERE ar.member_id = m.id AND ar.status = 'present') AS present_days,
           (SELECT COUNT(*) FROM attendance_records ar WHERE ar.member_id = m.id) AS total_days
    FROM members m
    WHERE m.active_status = 'yes' AND m.id IN (
        SELECT gm.member_id FROM group_members gm 
        WHERE gm.group_id = ?
    ) 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $group_id, $limit, $offset);
$stmt->execute();
$members_result = $stmt->get_result();

// Total count for pagination
$total_stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM members m
    WHERE m.active_status = 'yes' AND m.id IN (
        SELECT gm.member_id FROM group_members gm 
        WHERE gm.group_id = ?
    )
");
$total_stmt->bind_param("i", $group_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_members = $total_row['total'];

// Calculate total pages for pagination
$total_pages = ceil($total_members / $limit);

// Display the dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leader Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
            
        /* Custom styles to widen the table columns */
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
            <a class="navbar-brand" href="#">Study Group Leader</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="add_members.php">Add Members to Group</a>
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
                        <a class="nav-link" href="update_info.php">Update Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leader_login.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h1 class="card-title">Welcome, <?php echo $leader['first_name']; ?>!</h1>
                <p class="card-text">Group: <strong><?php echo $leader['group_name']; ?></strong></p>
            </div>
        </div>

        <h2>Group Members</h2>
        <div class="table-responsive mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>First Name</th>
                        <th>last Name</th>
                        <th>Phone</th>
                        <th>Attendance %</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $members_result->fetch_assoc()): 
                        $present_days = $member['present_days'];
                        $total_days = $member['total_days'];
                        $attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100) : 0;
                    ?>
                        <tr>
                            <!-- <td><?php echo $member['id']; ?></td> -->
                            <td><?php echo $member['first_name']; ?></td>
                            <td><?php echo $member['last_name']; ?></td>
                            <td><?php echo $member['phone']; ?></td>
                            <td><?php echo $attendance_percentage; ?>%</td>
                            <td>
                                <form action="remove_member.php" method="POST">
                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this member?');">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="dashboard.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Bootstrap JS (optional for components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
