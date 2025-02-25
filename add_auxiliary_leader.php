<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $auxiliary = $_POST['auxiliary'];

    $sql = "INSERT INTO auxiliary_leaders (username, phone, password, auxiliary) VALUES ('$username', '$phone', '$password', '$auxiliary')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Auxiliary leader added successfully.'); window.location.href='view_auxiliary_leaders.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <title>Add New Auxiliary Leader</title>

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
        <h1 class="text-2xl font-semibold">Add New Auxiliary Leader</h1>
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
                    <a href="logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d mt-3">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="flex-1 p-6 bg-white">
        <form method="post" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="username" class="block text-gray-700">Username:</label>
            <input type="text" id="username" name="username" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="phone" class="block text-gray-700">Phone:</label>
            <input type="text" id="phone" name="phone" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="password" class="block text-gray-700">Password:</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="auxiliary" class="block text-gray-700">Auxiliary:</label>
            <select id="auxiliary" name="auxiliary" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="youth">Youth</option>
            </select>
        </div>
        <div class="col-span-2 flex justify-center mt-3">
            <input type="submit" value="Add Leader" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
        </div>
        </div>
        </form>
    </main>
</body>
</html>
