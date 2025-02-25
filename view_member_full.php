<?php
session_start();
include 'config.php';

// Get the member ID from the URL
if (!isset($_GET['member_id'])) {
    echo "Member ID not provided.";
    exit();
}
$member_id = intval($_GET['member_id']);

// Fetch member details
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    echo "Member not found.";
    exit();
}

// Fetch attendance data for the member
$stmt = $conn->prepare("
    SELECT date, status 
    FROM attendance_records 
    WHERE member_id = ? 
    ORDER BY date DESC
");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

// Initialize variables for attendance summary
$total_days = 0;
$present_days = 0;
$absent_days = 0;
$attendance_data = [];

while ($row = $attendance_result->fetch_assoc()) {
    $total_days++;
    if ($row['status'] == 'present') {
        $present_days++;
    } else {
        $absent_days++;
    }
    $attendance_data[] = $row;
}

// Calculate percentage of present and absent days
$present_percentage = $total_days > 0 ? ($present_days / $total_days) * 100 : 0;
$absent_percentage = $total_days > 0 ? ($absent_days / $total_days) * 100 : 0;

// Close statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Attendance and Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Attendance and Profile for <?php echo htmlspecialchars($member['first_name'] . " " . $member['last_name']); ?></h1>

        <!-- Pie Chart for Attendance -->
        <div class="row my-4">
            <div class="col-md-6">
                <h3>Attendance Summary</h3>
                <canvas id="attendanceChart"></canvas>
                <script>
                    var ctx = document.getElementById('attendanceChart').getContext('2d');
                    var attendanceChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Present', 'Absent'],
                            datasets: [{
                                data: [<?php echo $present_percentage; ?>, <?php echo $absent_percentage; ?>],
                                backgroundColor: ['#28a745', '#dc3545']
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                </script>
            </div>

            <!-- Attendance Records Table -->
            <div class="col-md-6">
                <h3>Attendance Records</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_data as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                <td><?php echo ucfirst($record['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Member Profile Section -->
        <div class="row my-4">
            <div class="col-md-12">
                <h3>Member Profile</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>First Name</th>
                        <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Auxiliary</th>
                        <td><?php echo htmlspecialchars($member['auxiliary']); ?></td>
                    </tr>
                    <!-- <tr>
                        <th>Profile Picture</th>
                        <td><img src="../uploads/<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" width="100"></td>
                    </tr> -->
                </table>
            </div>
        </div>

        <!-- Back to Attendance List -->
        <div class="my-4">
            <a href="view_study_groups.php" class="btn btn-primary">Back to Attendance List</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
