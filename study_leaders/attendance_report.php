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

// Fetch leader's group
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

// Initialize date range
$start_date = '';
$end_date = '';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// Fetch members of the leader's group with their attendance records
$group_id = $group['group_id'];
$stmt = $conn->prepare("
    SELECT m.id, m.first_name, m.last_name, 
    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count, 
    COUNT(ar.id) AS total_attendance,
    (SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(ar.id), 0)) * 100 AS attendance_percentage
    FROM members m
    LEFT JOIN attendance_records ar ON m.id = ar.member_id 
    WHERE m.active_status = 'yes' AND m.id IN (
        SELECT member_id FROM group_members WHERE group_id = ?
    )
    " . ($start_date && $end_date ? "AND ar.date BETWEEN ? AND ?" : "") . " 
    GROUP BY m.id
");

if ($start_date && $end_date) {
    $stmt->bind_param("iss", $group_id, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $group_id);
}

$stmt->execute();
$report_result = $stmt->get_result();

// Handle export requests
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $filename = "attendance_report_" . date('Ymd') . "." . ($export_type === 'csv' ? "csv" : "pdf");
    
    if ($export_type === 'csv') {
        // CSV Export Logic
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Member ID', 'First Name', 'Last Name', 'Present Count', 'Total Attendance', 'Attendance Percentage']);

        // Reset result pointer to fetch data again
        $report_result->data_seek(0);
        
        while ($report = $report_result->fetch_assoc()) {
            fputcsv($output, [
                $report['id'],
                $report['first_name'],
                $report['last_name'],
                $report['present_count'],
                $report['total_attendance'],
                number_format($report['attendance_percentage'], 2) . '%'
            ]);
        }
        fclose($output);
        exit();
    } elseif ($export_type === 'pdf') {
        // Include TCPDF
        require_once 'vendor/autoload.php'; // Adjust the path as needed
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        
        // Table header
        $html = '<h1>Attendance Report for ' . htmlspecialchars($group['group_name']) . '</h1>';
        $html .= '<table border="1"><tr><th>Member ID</th><th>First Name</th><th>Last Name</th><th>Present Count</th><th>Total Attendance</th><th>Attendance Percentage</th></tr>';
        
        // Reset result pointer to fetch data again
        $report_result->data_seek(0);
        
        while ($report = $report_result->fetch_assoc()) {
            $html .= '<tr>
                <td>' . htmlspecialchars($report['id']) . '</td>
                <td>' . htmlspecialchars($report['first_name']) . '</td>
                <td>' . htmlspecialchars($report['last_name']) . '</td>
                <td>' . htmlspecialchars($report['present_count']) . '</td>
                <td>' . htmlspecialchars($report['total_attendance']) . '</td>
                <td>' . number_format($report['attendance_percentage'], 2) . '%</td>
            </tr>';
        }
        $html .= '</table>';
        
        $pdf->writeHTML($html);
        $pdf->Output($filename, 'D'); // Download the PDF
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
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
            <a class="navbar-brand" href="#">Attendance Report</a>
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
        <form method="POST" action="" class="mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>" required>
                </div>
            </div>
            <div class="mb-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="?export=csv" class="btn btn-success">Export as CSV</a>            
        </div>
        </form>

        <div class="table-responsive mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <!-- <th>Member ID</th> -->
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Present Count</th>
                        <th>Total Attendance</th>
                        <th>Attendance Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $report_result->fetch_assoc()): ?>
                        <tr>
                            <!-- <td><?php echo htmlspecialchars($report['id']); ?></td> -->
                            <td><?php echo htmlspecialchars($report['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['present_count']); ?></td>
                            <td><?php echo htmlspecialchars($report['total_attendance']); ?></td>
                            <td><?php echo number_format($report['attendance_percentage'], 2) . '%'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
