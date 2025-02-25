<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Fetch payment categories
$sql_categories = "SELECT * FROM categories_admin";
$result_categories = $conn->query($sql_categories);

// Fetch members
$sql_members = "SELECT id, first_name, last_name FROM members";
$result_members = $conn->query($sql_members);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <title>Payment Report</title>

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
        <h1 class="text-2xl font-semibold">Payments Report</h1>
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
                    <a href="payments_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="sidebar-text">Record Payment</span>
                    </a>
                </li>
                <li>
                    <a href="categories_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-chart-line"></i>
                        <span class="sidebar-text">Manage Categories</span>
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
        <form method="get" action="generate_report_admin.php" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="member_id" class="block text-gray-700">Member:</label>
            <select id="member_id" name="member_id" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                <option value="">All Members</option>
                <?php
                if ($result_members->num_rows > 0) {
                    while ($row = $result_members->fetch_assoc()) {
                        echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-span-2">
            <label for="category_id" class="block text-gray-700">Payment Category:</label>
            <select id="category_id" name="category_id" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                <option value="">All Categories</option>
                <?php
                if ($result_categories->num_rows > 0) {
                    while ($row = $result_categories->fetch_assoc()) {
                        echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-span-2">
            <label for="start_date" class="block text-gray-700">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="end_date" class="block text-gray-700">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2 flex justify-center">
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Generate Report</button>
        </div>
        </div>
        </form>
    </main>
</body>
</html>
