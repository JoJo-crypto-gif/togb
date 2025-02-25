<?php
session_start();
include '../config.php'; // Include your existing config.php file

// Check if the leader is logged in
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
    echo "<div class='alert alert-danger'>Group not found.</div>";
    exit();
}

// Initialize date filter variables
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Fetch attendance records for the group based on date filter
$group_id = $group['group_id'];
$query = "
    SELECT ar.member_id, ar.date, ar.status, m.first_name, m.last_name 
    FROM attendance_records ar
    JOIN members m ON ar.member_id = m.id 
    WHERE ar.group_id = ?
";

if ($start_date && $end_date) {
    $query .= " AND ar.date BETWEEN ? AND ?";
}

$query .= " ORDER BY ar.date DESC";

$stmt = $conn->prepare($query);
if ($start_date && $end_date) {
    $stmt->bind_param("iss", $group_id, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $group_id);
}
$stmt->execute();
$attendance_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <title>View Attendance</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Attendance Records</a>
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
    <div class="container mt-5">
        <!-- <h1 class="text-center mb-4">Attendance Records for <?php echo htmlspecialchars($group['group_name']); ?></h1> -->
        
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-5">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control">Filter</button>
                </div>
            </div>
        </form>

        <?php if ($attendance_result->num_rows > 0): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <!-- <th>Member ID</th> -->
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($record = $attendance_result->fetch_assoc()): ?>
                        <tr>
                            <!-- <td><?php echo htmlspecialchars($record['member_id']); ?></td> -->
                            <td><?php echo htmlspecialchars($record['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($record['status'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No attendance records found for this group within the selected date range.</div>
        <?php endif; ?>

        <div class="text-center mt-4 no-print">
            <!-- <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a> -->
            <button class="btn btn-danger" onclick="window.print()">Download PDF</button>
            <a href="export_attendance.php?group_id=<?php echo $group_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success">Download Spreadsheet</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
