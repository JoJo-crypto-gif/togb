<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$member_results = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_member'])) {
    $first_name = $_POST['first_name'];
    
    $sql_member = "SELECT * FROM members WHERE first_name = ?";
    $stmt_member = $conn->prepare($sql_member);
    $stmt_member->bind_param("s", $first_name);
    $stmt_member->execute();
    $member_results = $stmt_member->get_result();
    $stmt_member->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment'])) {
    $member_id = $_POST['member_id'];
    $category_id = $_POST['category_id'];
    $payment_date = $_POST['payment_date'];
    
    $sql_insert = "INSERT INTO payment_admin (member_id, category_id, payment_date) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iis", $member_id, $category_id, $payment_date);
    $stmt_insert->execute();
    $stmt_insert->close();
    
    $success_message = "Payment added successfully!";
}

$sql_categories = "SELECT * FROM categories_admin";
$result_categories = $conn->query($sql_categories);

$conn->close();
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
    <title>Record Payment</title>

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
        <h1 class="text-2xl font-semibold">Record Payments</h1>
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
                    <a href="categories_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-list-ul"></i>
                                <span class="sidebar-text">Payment Categories</span>
                    </a>
                </li>
                <li>
                    <a href="payments_admin.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="sidebar-text">Record Payment</span>
                    </a>
                </li>
                <li>
                    <a href="generate_report_admin_form.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-chart-line"></i>
                        <span class="sidebar-text">Payment Report</span>
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
        <?php if (isset($success_message)) { echo "<p>$success_message</p>"; } ?>
        <form method="post" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="first_name" class="block text-gray-700">Member First Name:</label>
            <input type="text" id="first_name" name="first_name" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
            <div class="col-span-2 flex justify-center">
            <button type="submit" name="fetch_member" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d mt-4">Fetch Member</button>
            </div>
        </div>
        </div>
        </form>
        
        <?php if ($member_results && $member_results->num_rows > 0) { ?>
            <form method="post" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="col-span-2">
                <label for="member_id" class="block text-gray-700">Select Member:</label>
                <select id="member_id" name="member_id" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                    <?php while ($member = $member_results->fetch_assoc()) {
                        echo "<option value='" . $member['id'] . "'>" . htmlspecialchars($member['first_name'] . " " . $member['last_name'] . " - " . $member['phone']) . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-span-2">
                <label for="category_id" class="block text-gray-700">Payment Category:</label>
                <select id="category_id" name="category_id" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                    <?php while ($category = $result_categories->fetch_assoc()) {
                        echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-span-2">
                <label for="payment_date" class="block text-gray-700">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
            </div>
                <div class="col-span-2 flex justify-center">
                <button type="submit" name="add_payment" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Add Payment</button>
                </div>
            </div>
            </form>
        <?php } elseif (isset($member_results)) { ?>
            <p>No members found with that first name.</p>
        <?php } ?>
    </main>
</body>
</html>
