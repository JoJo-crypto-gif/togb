<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Fetch auxiliary leader's details
$leader_id = $_SESSION['leader_id'];
$sql_leader = "SELECT * FROM auxiliary_leaders WHERE id='$leader_id'";
$result_leader = $conn->query($sql_leader);
$leader = $result_leader->fetch_assoc();

// Fetch members of the auxiliary
$auxiliary = $leader['auxiliary'];
$sql_members = "SELECT * FROM members WHERE auxiliary='$auxiliary'";
$result_members = $conn->query($sql_members);

// Fetch overview statistics
$sql_total_members = "SELECT COUNT(*) as total_members FROM members WHERE auxiliary='$auxiliary'";
$result_total_members = $conn->query($sql_total_members);
$total_members = $result_total_members->fetch_assoc()['total_members'];

$sql_total_payments = "SELECT SUM(amount) as total_payments FROM payments WHERE payer_name IN (SELECT CONCAT(first_name, ' ', last_name) FROM members WHERE auxiliary='$auxiliary')";
$result_total_payments = $conn->query($sql_total_payments);
$total_payments = $result_total_payments->fetch_assoc()['total_payments'];

$sql_total_dues = "SELECT SUM(amount) as total_dues FROM monthly_dues WHERE member_id IN (SELECT id FROM members WHERE auxiliary='$auxiliary')";
$result_total_dues = $conn->query($sql_total_dues);
$total_dues = $result_total_dues->fetch_assoc()['total_dues'];

$total_income = $total_payments + $total_dues;

$sql_total_expenses = "SELECT SUM(amount) as total_expenses FROM expenses WHERE auxiliary='$auxiliary'";
$result_total_expenses = $conn->query($sql_total_expenses);
$total_expenses = $result_total_expenses->fetch_assoc()['total_expenses'];

$net_total_income = $total_income - $total_expenses;

// Fetch recent activities
$sql_recent_payments = "SELECT payments.*, payment_categories.name AS category_name 
                        FROM payments 
                        JOIN payment_categories ON payments.category_id = payment_categories.id 
                        WHERE payer_name IN (SELECT CONCAT(first_name, ' ', last_name) FROM members WHERE auxiliary='$auxiliary') 
                        ORDER BY payment_date DESC LIMIT 5";
$result_recent_payments = $conn->query($sql_recent_payments);

$sql_recent_dues_payments = "SELECT monthly_dues.*, members.first_name, members.last_name 
                             FROM monthly_dues 
                             JOIN members ON monthly_dues.member_id = members.id 
                             WHERE members.auxiliary='$auxiliary' 
                             ORDER BY payment_date DESC LIMIT 5";
$result_recent_dues_payments = $conn->query($sql_recent_dues_payments);

// $sql_recent_attendance = "SELECT attendance.*, members.first_name, members.last_name 
//                           FROM attendance 
//                           JOIN members ON attendance.member_id = members.id 
//                           WHERE members.auxiliary='$auxiliary' 
//                           ORDER BY attendance_date DESC LIMIT 5";
// $result_recent_attendance = $conn->query($sql_recent_attendance);

$sql_recent_expenses = "SELECT * FROM expenses WHERE auxiliary='$auxiliary' ORDER BY expense_date DESC LIMIT 5";
$result_recent_expenses = $conn->query($sql_recent_expenses);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auxiliary Leader Dashboard</title>
</head>
<body>
    <header>
        <h1>Auxiliary Leader Dashboard</h1>
        <h2>Welcome, <?php echo htmlspecialchars($leader['username']); ?></h2>
    </header>
    <nav>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="record_attendance.php">Attendance</a></li>
        <li><a href="generate_attendance_report.php">Attendance Report</a></li>
        <li><a href="monthly_dues_form.php">Monthly Dues Payment</a></li>
        <li><a href="monthly_dues_report_form.php">Dues Report</a></li>
        <li><a href="manage_categories.php">Payment Categories</a></li>
        <li><a href="view_payment_categories.php">View Payement Categories</a></li>
        <li><a href="record_payment.php">Record Payment</a></li>
        <li><a href="report_form.php">Generate Report</a></li>
        <li><a href="record_expense.php">Record Expense</a></li>
        <li><a href="expense_report_form.php">Expense Report</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

    <main>
        <section>
            <h2>Overview</h2>
            <div class="stats">
                <div class="stat-item">
                    <h3>Total Members</h3>
                    <p><?php echo $total_members; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Income</h3>
                    <p><?php echo $total_income; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Total Expenses</h3>
                    <p><?php echo $total_expenses; ?></p>
                </div>
                <div class="stat-item">
                    <h3>Net Total Income</h3>
                    <p><?php echo $net_total_income; ?></p>
                </div>
            </div>
        </section>
        <section>
            <h2>Members in Your Auxiliary</h2>
            <?php
            if ($result_members->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>First Name</th><th>Last Name</th><th>Phone</th><th>Profile Picture</th><th>Email</th><th>Auxiliary</th><th>Actions</th></tr>";
                while ($row = $result_members->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td><img src='../uploads/" . htmlspecialchars($row['profile_picture']) . "' width='50' height='50'></td>"; // Display profile picture
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['auxiliary']) . "</td>";
                    echo "<td><a href='view_member.php?id=" . $row['id'] . "'>View</a> <a href='edit_member.php?id=" . $row['id'] . "'>Edit</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No members found in your auxiliary.</p>";
            }
            ?>
        </section>
        <section>
    <h2>Recent Payments</h2>
    <?php
    if ($result_recent_payments->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Payer Name</th><th>Phone</th><th>Category</th><th>Amount</th><th>Date</th></tr>";
        while ($row = $result_recent_payments->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['payer_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['payer_phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
            echo "<td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>"; // Date formatting
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recent payments found.</p>";
    }
    ?>
</section>

        <section>
            <h2>Recent Dues Payments</h2>
            <?php
            if ($result_recent_dues_payments->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Member Name</th><th>Amount</th><th>Date</th><th>Description</th></tr>";
                while ($row = $result_recent_dues_payments->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['payment_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No recent dues payments found.</p>";
            }
            ?>
        </section>
        <!-- <section>
            <h2>Recent Attendance</h2>
            <?php
            if ($result_recent_attendance->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Member Name</th><th>Status</th><th>Date</th></tr>";
                while ($row = $result_recent_attendance->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['attendance_date']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No recent attendance records found.</p>";
            }
            ?>
        </section> -->
        <section>
    <h2>Recent Expenses</h2>
    <?php
    if ($result_recent_expenses->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Amount</th><th>Date</th><th>Description</th></tr>";
        while ($row = $result_recent_expenses->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
            echo "<td>" . htmlspecialchars($row['expense_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recent expenses found.</p>";
    }
    ?>
</section>

    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
