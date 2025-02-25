<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payer_name = $_POST['payer_name'];
    $payer_phone = $_POST['payer_phone'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];

    $sql = "INSERT INTO payments (payer_name, payer_phone, category_id, amount, payment_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssids", $payer_name, $payer_phone, $category_id, $amount, $payment_date);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: record_payment.php");
    exit();
}

// Get the user type from the session
$user_type = 'admin';  // Since this is for the admin

$sql_categories = "SELECT * FROM payment_categories WHERE `for` = ?";
$stmt_categories = $conn->prepare($sql_categories);
$stmt_categories->bind_param("s", $user_type);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
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
    <title>Record payment</title>

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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <h1 class="text-2xl font-semibold">Record Payment</h1>
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
                        <span class="sidebar-text">Expense</span>
                    </a>
                </li>
                <li>
                    <a href="generate_expense_report.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-chart-pie"></i>
                        <span class="sidebar-text">Expense Report</span>
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
        <form method="post" action="record_payment.php" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-1">
            <label for="payer_name" class="block text-gray-700">Payer's Name:</label>
            <input type="text" id="payer_name" name="payer_name" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">
            <label for="payer_phone" class="block text-gray-700">Payer's Phone No:</label>
            <input type="text" id="payer_phone" name="payer_phone" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="category_id" class="block text-gray-700">Payment Category:</label>
            <select id="category_id" name="category_id" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
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
            <label for="amount" class="block text-gray-700">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.1" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="payment_date" class="block text-gray-700">Payment Date:</label>
            <input type="date" id="payment_date" name="payment_date" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2 flex justify-center">
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Record Payment</button>
        </div>
        </div>
        </form>
    </main>
</body>
</html>
