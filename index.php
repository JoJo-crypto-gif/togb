<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'functions.php';

// Count total members
$sql_total = "SELECT COUNT(*) as total FROM members";
$result_total = $conn->query($sql_total);
$total_members = $result_total->fetch_assoc()['total'];

// Count inactive members
$sql_inactive = "SELECT COUNT(*) as total FROM members WHERE active_status='No'";
$result_inactive = $conn->query($sql_inactive);
$total_inactive = $result_inactive->fetch_assoc()['total'];

// Count non-baptized members
$sql_non_baptized = "SELECT COUNT(*) as total FROM members WHERE baptism_status='No'";
$result_non_baptized = $conn->query($sql_non_baptized);
$total_non_baptized = $result_non_baptized->fetch_assoc()['total'];

// Get latest notifications
$sql_notifications = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 8";
$result_notifications = $conn->query($sql_notifications);

// Calculate total income
$sql_income = "SELECT SUM(amount) as total_income FROM payments";
$result_income = $conn->query($sql_income);
$total_income = $result_income->fetch_assoc()['total_income'];

// Fetch recent payments
$sql_recent_payments = "
    SELECT p.payment_date, m.first_name, m.last_name, c.name AS category_name
    FROM payment_admin p
    JOIN members m ON p.member_id = m.id
    JOIN categories_admin c ON p.category_id = c.id
    ORDER BY p.payment_date DESC
    LIMIT 5";
$result_recent_payments = $conn->query($sql_recent_payments);

//Fetch church workers
$sql_workers = "SELECT first_name, last_name, role, phone, email FROM church_workers";
$result_workers = $conn->query($sql_workers);

// Prepare data for net income line graph
$sql_net_income = "
    SELECT DATE(payment_date) as date, SUM(amount) as income 
    FROM payments 
    GROUP BY DATE(payment_date)
    UNION
    SELECT DATE(expense_date) as date, -SUM(amount) as expense 
    FROM admin_expenses 
    GROUP BY DATE(expense_date)
    ORDER BY date
";
$result_net_income = $conn->query($sql_net_income);
$net_income_data = [];
while ($row = $result_net_income->fetch_assoc()) {
    $net_income_data[] = $row;
}

$sql = "
    SELECT sg.name AS group_name, 
           CONCAT(sl.first_name, ' ', sl.last_name) AS leader_name, 
           (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.status)) * 100 AS attendance_percentage
    FROM study_groups sg
    JOIN study_leaders sl ON sg.leader_id = sl.id
    LEFT JOIN attendance_records a ON sg.id = a.group_id
    GROUP BY sg.id
    ORDER BY attendance_percentage ASC
    LIMIT 5
";

$query = $conn->prepare("
    SELECT sg.name AS group_name, 
           CONCAT(sl.first_name, ' ', sl.last_name) AS leader_name, 
           (SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.status)) * 100 AS attendance_percentage
    FROM study_groups sg
    JOIN study_leaders sl ON sg.leader_id = sl.id
    LEFT JOIN attendance_records a ON sg.id = a.group_id
    GROUP BY sg.id
    ORDER BY attendance_percentage ASC
    LIMIT 5
");

$query->execute();
$result = $query->get_result();


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>

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
        .welfare-income {
            grid-column: span 2;
            /* grid-row: span 2; */
        }
        .welfare-income-overlay {
            background: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Welcome!, Admin</h1>
        <div class="notifications">
            <a href="notifications.php" class="text-blue-500 hover:underline"><i class="fas fa-bell"></i> Notifications</a>
        </div>
    </div>
</header>

<div class="flex">
    <!-- Sidebar -->
    <nav class="sidebar w-64 bg-white h-screen shadow-md overflow-hidden relative">
        <div class="p-4">
            <button class="text-gray-600 focus:outline-none mb-6" onclick="toggleSidebar()">
                <i id="toggle-icon" class="fas fa-arrow-left"></i>
            </button> 
            <!-- <h2 class="text-xl font-semibold sidebar-text">Menu</h2> -->
            <ul class="mt-4">
                <li>
                    <a href="view_members.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-users"></i>
                        <span class="sidebar-text">View Members</span>
                    </a>
                </li>
                <li>
                    <a href="add_member.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-plus"></i>
                        <span class="sidebar-text">Add Member</span>
                    </a>
                </li>
                <li>
                    <a href="add_auxiliary_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-tie"></i>
                        <span class="sidebar-text">Add Auxiliary Leader</span>
                    </a>
                </li>
                <li>
                    <a href="view_auxiliary_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-users-cog"></i>
                        <span class="sidebar-text">View Auxiliary Leaders</span>
                    </a>
                </li>
                <li>
                    <a href="view_church_workers.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-church"></i>
                        <span class="sidebar-text">Church Workers</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="block py-2.5 px-4 rounded hover:bg-gray-200 dropdown-toggle hover-3d" onclick="toggleDropdown(this)">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="sidebar-text">Finance</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown">
                        <li>
                            <a href="categories_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-list-ul"></i>
                                <span class="sidebar-text">Payment Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="payments_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-money-bill-wave"></i>
                                <span class="sidebar-text">Record Payment</span>
                            </a>
                        </li>

                        <li>
                            <a href="generate_report_admin_form.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-chart-line"></i>
                                <span class="sidebar-text">Payment Report</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0)" class="block py-2.5 px-4 rounded hover:bg-gray-200 dropdown-toggle hover-3d" onclick="toggleDropdown(this)">
                        <i class="fas fa-book"></i>
                        <span class="sidebar-text">Bible Study Groups</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown">
                        <li>
                            <a href="./create_group.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-list-ul"></i>
                                <span class="sidebar-text">Manage Study Groups</span>
                            </a>
                        </li>
                        <li>
                            <a href="./create_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-user-plus"></i>
                                <span class="sidebar-text">Add Study Leaders</span>
                            </a>
                        </li>

                        <li>
                            <a href="./manage_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-user-tie"></i>
                                <span class="sidebar-text">Manage Study Leaders</span>
                            </a>
                        </li>
                        <li>
                            <a href="view_study_groups.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-tasks"></i>
                                <span class="sidebar-text">Attendance</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0)" class="block py-2.5 px-4 rounded hover:bg-gray-200 dropdown-toggle hover-3d" onclick="toggleDropdown(this)">
                        <i class="fas fa-cogs"></i>
                        <span class="sidebar-text">Settings</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown">
                        <li>
                            <a href="profile_settings.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-user-cog"></i>
                                <span class="sidebar-text">Profile Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="site_settings.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-sliders-h"></i>
                                <span class="sidebar-text">Site Settings</span>
                            </a>
                        </li>
                    </ul>
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

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Members -->
            <a href="view_members.php">
            <div class="bg-white p-6 rounded shadow-md hover-3d">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-users">&nbsp;</i>Total Members</h2>
                <p class="text-3xl font-bold"><?php echo $total_members; ?></p>
            </div>
             </a>

            <!-- Inactive Members -->
            <a href="view_members.php?filter=inactive">
            <div class="bg-white p-6 rounded shadow-md hover-3d">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-user-slash">&nbsp;</i>Inactive Members</h2>
                <p class="text-3xl font-bold"><?php echo $total_inactive; ?></p>
            </div>
            </a>

            <!-- Non-baptized Members -->
            <a href="view_members.php?filter=non_baptized">
            <div class="bg-white p-6 rounded shadow-md hover-3d">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-user-slash">&nbsp;</i>Non-baptized Members</h2>
                <p class="text-3xl font-bold"><?php echo $total_non_baptized; ?></p>
            </div>
            </a>

            <!-- Bible study group -->
            <div class="bg-white p-6 rounded shadow-md welfare-income hover-3d">
    <h2 class="text-2xl font-semibold mb-5"><i class="fas fa-book-open"></i> Bible Study</h2>
    <table class="w-full border-collapse">
        <thead>
            <tr>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Group Name</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Leader Name</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Attendance Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo $row['group_name']; ?></td>
                <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo $row['leader_name']; ?></td>
                <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo round($row['attendance_percentage'], 2); ?>%</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


            <!-- Latest Notifications -->
            <div class="bg-white p-6 rounded shadow-md col-span-2 lg:col-span-1 hover-3d">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-bell">&nbsp;</i>Latest Notifications</h2>
                <ul>
                    <?php while ($row = $result_notifications->fetch_assoc()) { ?>
                        <li class="mb-2">
                            <div class="flex flex-col">
                                <p><?php echo $row['message']; ?></p>
                                <span class="text-gray-500 text-sm"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></span>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white p-6 rounded shadow-md col-span-2 lg:col-span-1 hover-3d">
                <section>
                    <h2 class="text-2xl font-semibold mb-4"><i class="fas fa-receipt"></i> Recent Payments</h2>
                    <div class="bg-white shadow-md p-4 rounded">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b border-gray-200">Name</th>
                                    <th class="py-2 px-4 border-b border-gray-200">Category</th>
                                    <th class="py-2 px-4 border-b border-gray-200">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($result_recent_payments->num_rows > 0) {
                             while ($row = $result_recent_payments->fetch_assoc()) { ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['first_name']); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200 text-gray-500 text-sm"><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                </tr>
                                <?php }
                                 } else { ?>
                               <tr>
                               <td colspan="3">No recent payments found.</td>
                             </tr>
                             <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="bg-white p-6 rounded shadow-md welfare-income hover-3d">
    <h2 class="text-2xl font-semibold mb-5"><i class="fas fa-user"></i> Church Workers</h2>
    <table class="w-full border-collapse">
        <thead>
            <tr>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">First Name</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Last Name</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Role</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Email</th>
                <th class="py-3 px-6 border-b border-gray-200 text-center align-middle">Phone</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_workers->num_rows > 0) {
                while ($worker = $result_workers->fetch_assoc()) { ?>
                    <tr>
                        <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo htmlspecialchars($worker['first_name']); ?></td>
                        <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo htmlspecialchars($worker['last_name']); ?></td>
                        <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo htmlspecialchars($worker['role']); ?></td>
                        <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo htmlspecialchars($worker['email']); ?></td>
                        <td class="py-3 px-6 border-b border-gray-200 text-center align-middle"><?php echo htmlspecialchars($worker['phone']); ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5" class="py-3 px-6 border-b border-gray-200 text-center align-middle">No workers found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.querySelector('.sidebar');
        var toggleIcon = document.getElementById('toggle-icon');
        sidebar.classList.toggle('sidebar-icon-only');
        if (sidebar.classList.contains('sidebar-icon-only')) {
            toggleIcon.classList.remove('fa-arrow-left');
            toggleIcon.classList.add('fa-arrow-right');
        } else {
            toggleIcon.classList.remove('fa-arrow-right');
            toggleIcon.classList.add('fa-arrow-left');
        }
    }

    function toggleDropdown(element) {
        var dropdown = element.nextElementSibling;
        dropdown.classList.toggle('dropdown-expand');
        var icon = element.querySelector('.dropdown-icon');
        if (dropdown.classList.contains('dropdown-expand')) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
    


    const netIncomeData = <?php echo json_encode($net_income_data); ?>;
    const labels = netIncomeData.map(data => data.date);
    const data = {
        labels: labels,
        datasets: [{
            label: 'Income',
            tension: .3,
            borderWidth: 0,
            data: netIncomeData.map(data => data.income || -data.expense),
            borderColor: 'rgba(51, 0, 100, .5)',
            borderWidth: 3,
            pointRadius: 2,
            backgroundColor: 'rgba(51, 0, 100, .2)',
            maxBarThickness: 6,
            fill: true
        }]
    };
    const config = {
        type: 'line',
        data: data,
        options: {            
            interaction: {
              intersect: false,
              mode: 'index',
            },
            plugins: {
                legend:{
                    display: false,
                }
            }
        }
    };
    const netIncomeChart = new Chart(
        document.getElementById('netIncomeChart'),
        config
    );
</script>

</body>
</html>