<?php
session_start();
include '../config.php'; // Include your existing config.php file

if (!isset($_SESSION['leader_id'])) {
    header("Location: leader_login.php");
    exit();
}

$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Prepare the query for fetching attendance records
$query = "
    SELECT ar.member_id, ar.date, ar.status, m.first_name, m.last_name 
    FROM attendance_records ar
    JOIN members m ON ar.member_id = m.id 
    WHERE ar.group_id = ?
";

if ($start_date && $end_date) {
    $query .= " AND ar.date BETWEEN ? AND ?";
}

$stmt = $conn->prepare($query);
if ($start_date && $end_date) {
    $stmt->bind_param("iss", $group_id, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $group_id);
}
$stmt->execute();
$attendance_result = $stmt->get_result();

// Prepare CSV output
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_records.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['First Name', 'Last Name', 'Date', 'Status']); // CSV header

while ($record = $attendance_result->fetch_assoc()) {
    // Format the date and prepend a single quote for Excel compatibility
    $formatted_date = "'" . date('Y-m-d', strtotime($record['date'])); // Change format as needed
    fputcsv($output, [
        // $record['member_id'],
        $record['first_name'],
        $record['last_name'],
        $formatted_date,
        ucfirst($record['status'])
    ]);
}

fclose($output);
exit();
