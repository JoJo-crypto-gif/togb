<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Fetch all notifications
$sql_notifications = "SELECT * FROM notifications ORDER BY created_at DESC";
$result_notifications = $conn->query($sql_notifications);

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
    <title>Notifications</title>

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
            <ul class="mt-4">
            <li>
                    <a href="index.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-chart-pie"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
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
                        <span class="sidebar-text">Manage Church Workers</span>
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
                            <a href="add_payment_category.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-plus-circle"></i>
                                <span class="sidebar-text">Manage Payment Category</span>
                            </a>
                        </li>
                        <li>
                            <a href="record_payment.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span class="sidebar-text">Record Payment</span>
                            </a>
                        </li>
                        <li>
                            <a href="generate_payment_report.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-chart-line"></i>
                                <span class="sidebar-text">Payment Report</span>
                            </a>
                        </li>
                        <li>
                            <a href="add_expense.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-minus-circle"></i>
                                <span class="sidebar-text">Add Expense</span>
                            </a>
                        </li>
                        <li>
                            <a href="view_expenses.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-receipt"></i>
                                <span class="sidebar-text">Expenses</span>
                            </a>
                        </li>
                        <li>
                            <a href="generate_expense_report.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-chart-pie"></i>
                                <span class="sidebar-text">Expenses Report</span>
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
        <section class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold">Notifications</h2>
                <form action="mark_notification_read.php" method="post">
                    <button type="submit" name="clear_all" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors duration-200">Clear All</button>
                </form>
            </div>
            <ul>
                <?php while($notification = $result_notifications->fetch_assoc()) { ?>
                    <li class="mb-2"><?php echo htmlspecialchars($notification['message']); ?> - <small><?php echo htmlspecialchars($notification['created_at']); ?></small></li>
                <?php } ?>
            </ul>
        </section>
    </main>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('sidebar-icon-only');
    }

    function toggleDropdown(element) {
        element.nextElementSibling.classList.toggle('dropdown-expand');
    }
</script>

</body>
</html>
