<?php
session_start();
include 'config.php'; // Include your existing config.php file

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get group ID from URL parameter
if (!isset($_GET['group_id'])) {
    echo "Group not found.";
    exit();
}

$group_id = $_GET['group_id'];

// Fetch the group's details
$stmt = $conn->prepare("
    SELECT sg.name AS group_name, sl.first_name, sl.last_name 
    FROM study_groups sg
    JOIN study_leaders sl ON sg.leader_id = sl.id
    WHERE sg.id = ?
");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group_result = $stmt->get_result();
$group = $group_result->fetch_assoc();

if (!$group) {
    echo "Group not found.";
    exit();
}

// Fetch members and their attendance
$date_filter = '';
$date_selected = false;
$group_attendance_percentage = 0; // Default to 0 in case no attendance records

if (isset($_POST['attendance_date']) && !empty($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];
    $date_selected = true;

    // Query to fetch overall group attendance percentage for the selected date
    $overall_query = "
        SELECT 
            COUNT(CASE WHEN ar.status = 'present' THEN 1 END) AS present_days, 
            COUNT(ar.id) AS total_days 
        FROM attendance_records ar 
        WHERE ar.group_id = ? AND ar.date = ?
    ";
    $stmt = $conn->prepare($overall_query);
    $stmt->bind_param("is", $group_id, $attendance_date);
} else {
    // Query to fetch overall group attendance percentage across all days
    $overall_query = "
        SELECT 
            COUNT(CASE WHEN ar.status = 'present' THEN 1 END) AS present_days, 
            COUNT(ar.id) AS total_days 
        FROM attendance_records ar 
        WHERE ar.group_id = ?
    ";
    $stmt = $conn->prepare($overall_query);
    $stmt->bind_param("i", $group_id);
}

$stmt->execute();
$overall_result = $stmt->get_result();
$overall_attendance = $overall_result->fetch_assoc();

// Calculate the overall group attendance percentage (either for a specific date or overall)
$total_days_group = $overall_attendance['total_days'];
$present_days_group = $overall_attendance['present_days'];
$group_attendance_percentage = $total_days_group > 0 ? round(($present_days_group / $total_days_group) * 100, 2) : 0;

// Query to fetch members
if ($date_selected) {
    // If date is selected, fetch status (present/absent) for the specific date
    $query = "
        SELECT m.id AS member_id, m.first_name, m.last_name, 
               ar.status AS attendance_status
        FROM members m
        JOIN group_members gm ON m.id = gm.member_id
        LEFT JOIN attendance_records ar ON m.id = ar.member_id AND ar.group_id = gm.group_id
        WHERE gm.group_id = ? AND m.active_status = 'yes' AND ar.date = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $group_id, $attendance_date);
} else {
    // If no date is selected, show overall attendance percentage for each member
    $query = "
        SELECT m.id AS member_id, m.first_name, m.last_name, 
               COUNT(CASE WHEN ar.status = 'present' THEN 1 END) AS present_days, 
               COUNT(ar.id) AS total_days 
        FROM members m
        JOIN group_members gm ON m.id = gm.member_id
        LEFT JOIN attendance_records ar ON m.id = ar.member_id AND ar.group_id = gm.group_id
        WHERE gm.group_id = ? AND m.active_status = 'yes'
        GROUP BY m.id
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $group_id);
}

$stmt->execute();
$members_result = $stmt->get_result();

// CSV Export logic
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Fetch the attendance data based on whether a date is selected
    if (isset($_GET['attendance_date']) && !empty($_GET['attendance_date'])) {
        $attendance_date = $_GET['attendance_date'];

        $query = "
            SELECT m.first_name, m.last_name, ar.status AS attendance_status
            FROM members m
            JOIN group_members gm ON m.id = gm.member_id
            LEFT JOIN attendance_records ar ON m.id = ar.member_id AND ar.group_id = gm.group_id
            WHERE gm.group_id = ? AND m.active_status = 'yes' AND ar.date = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $group_id, $attendance_date);
    } else {
        // If no date is selected, show overall attendance percentage
        $query = "
            SELECT m.first_name, m.last_name,
                COUNT(CASE WHEN ar.status = 'present' THEN 1 END) AS present_days,
                COUNT(ar.id) AS total_days 
            FROM members m
            JOIN group_members gm ON m.id = gm.member_id
            LEFT JOIN attendance_records ar ON m.id = ar.member_id AND ar.group_id = gm.group_id
            WHERE gm.group_id = ? AND m.active_status = 'yes'
            GROUP BY m.id
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $group_id);
    }

    $stmt->execute();
    $members_result = $stmt->get_result();

    // Prepare CSV output
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');

    $output = fopen('php://output', 'w');
    
    // Add date row if a date is selected
    if (isset($attendance_date) && !empty($attendance_date)) {
        fputcsv($output, ['Attendance Report for Date: ' . $attendance_date]);
        fputcsv($output, ['First Name', 'Last Name', 'Attendance Status']);
    } else {
        fputcsv($output, ['Overall Attendance Report']);
        fputcsv($output, ['First Name', 'Last Name', 'Present Days', 'Total Days']);
    }

    // Fetch the data and output to CSV
    while ($member = $members_result->fetch_assoc()) {
        if (isset($attendance_date) && !empty($attendance_date)) {
            fputcsv($output, [
                $member['first_name'],
                $member['last_name'],
                ucfirst($member['attendance_status']) ?: 'Absent'
            ]);
        } else {
            $present_days = $member['present_days'];
            $total_days = $member['total_days'];
            fputcsv($output, [
                $member['first_name'],
                $member['last_name'],
                $present_days,
                $total_days
            ]);
        }
    }

    fclose($output);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <title>Study Groups</title>

    <style>
        .hover-3d:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .hover-3d {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .sidebar {
            transition: all 0.3s ease;
            height: 100vh; /* Stretch sidebar to fit the full screen height */
        }
        .sidebar-icon-only {
            width: 50px;
        }
        .dropdown {
            display: none;
            transition: all 0.4s ease;
        }
        .dropdown-expand {
            display: block;
            transition: all 1s ease-in-out;
        }
        .sidebar-icon-only .sidebar-text {
            display: none;
        }
        .sidebar-icon-only .dropdown-toggle::after {
            display: none;
        }
        th {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-bottom-width: 2px;
            border-color: #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
        td {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-bottom-width: 1px;
            border-color: #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <h1 class="text-2xl font-semibold"><?php echo $group['group_name']; ?> Attendance</h1>
        <div class="notifications">
            <a href="notifications.php" class="text-blue-500 hover:underline"><i class="fas fa-bell"></i> Notifications</a>
        </div>
    </div>
</header>

<div class="flex flex-1">
    <!-- Sidebar -->
    <nav class="sidebar w-64 bg-white h-auto shadow-md overflow-hidden relative">
        <div class="p-4">
            <ul class="mt-4">
            <li>
                    <a href="index.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-home"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="create_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-plus"></i>
                        <span class="sidebar-text">Add Study Leader</span>
                    </a>
                </li>
                <li>
                    <a href="manage_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-tie"></i>
                        <span class="sidebar-text">Manage Study Leader</span>
                    </a>
                </li>
                <li>
                            <a href="view_study_groups.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-tasks"></i>
                                <span class="sidebar-text">Attendance</span>
                            </a>
                        </li>
                <li>
                    <a href="logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="flex-1 p-6 bg-white">
    <h2 class="text-xl font-bold mb-4">Group Leader: <?php echo $group['first_name'] . ' ' . $group['last_name']; ?></h2>
    
    <form method="POST" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="col-span-2">
    <label for="attendance_date" class="text-gray-700">View Attendance for a Specific Date:</label>
    <input type="date" name="attendance_date" id="attendance_date" class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>
    <div class="col-span-2">
    <div class="col-span-2 flex justify-center gap-8">
    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">View</button>
    
    
    
    <a href="?group_id=<?php echo $group_id; ?>&export=csv&attendance_date=<?php echo isset($attendance_date) ? $attendance_date : ''; ?>" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600 hover-3d">Download Spreadsheet</a>
    </div>
    </div>
    </div>
</form>



    <h3 class="text-xl font-bold mb-4" style="color: grey;">Attendance Percentage: <?php echo $group_attendance_percentage; ?>%</h3>
    <div class="bg-white shadow-md rounded p-4">
    <table class='bg-white shadow-md w-full'>
        <thead>
            <tr>
                <!-- <th>Member ID</th> -->
                <th>First Name</th>
                <th>Last Name</th>
                <th><?php echo $date_selected ? 'Attendance Status' : 'Attendance Percentage'; ?></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($member = $members_result->fetch_assoc()): ?>
                <tr>
                  <!-- <td><?php echo $member['member_id']; ?></td> -->
                    <td><?php echo $member['first_name']; ?></td>
                    <td><?php echo $member['last_name']; ?></td>
                    <td>
                        <?php 
                        if ($date_selected) {
                            // Show attendance status
                            echo $member['attendance_status'] ? ucfirst($member['attendance_status']) : 'Absent';
                        } else {
                            // Show overall attendance percentage
                            $total_days = $member['total_days'];
                            $present_days = $member['present_days'];
                            $attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
                            echo $attendance_percentage . '%';
                        }
                        ?>
                    </td>
                    <td>
                    <a href="view_member_full.php?member_id=<?php echo $member['member_id']; ?>" class='text-blue-500 hover:underline hover-3d'>View Member</a>


                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
    </main>
</body>
</html>
