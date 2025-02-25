<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $profile_picture = $newFileName;
        }
    }

    // Update the admin's data
    if (isset($profile_picture)) {
        $sql_update = "UPDATE admins SET name=?, email=?, phone=?, profile_picture=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ssssi', $name, $email, $phone, $profile_picture, $admin_id);
    } else {
        $sql_update = "UPDATE admins SET name=?, email=?, phone=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('sssi', $name, $email, $phone, $admin_id);
    }
    
    $stmt_update->execute();
    $stmt_update->close();
    $conn->close();

    header("Location: profile_settings.php");
    exit();
}

$sql_admin = "SELECT * FROM admins WHERE id=?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param('i', $admin_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin = $result_admin->fetch_assoc();
$stmt_admin->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
        <h1 class="text-2xl font-semibold">Profile Settings</h1>
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
    <main class="flex-1 p-6 bg-white">
        <form method="post" action="profile_settings.php" enctype="multipart/form-data" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg block">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="name" class="block text-gray-700">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="email" class="block text-gray-700">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="phone" class="block text-gray-700">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="profile_picture" class="block text-gray-700">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
            <?php if (!empty($admin['profile_picture'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Profile Picture" width="100" class="mt-4 justify-center">
            <?php endif; ?>
        </div>
        <div class="col-span-2 flex justify-center">
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Save Changes</button>
        </div>
        </div>
        </form>
    </main>

        <main class="flex-1 p-6 bg-white">
        <form id="change-password-form" action="update_password_logged_in.php" method="post" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg block">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="current_password" class="block text-gray-700">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="new_password" class="block text-gray-700">New Password:</label>
            <input type="password" id="new_password" name="new_password" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">
            <label for="confirm_password" class="block text-gray-700">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2 flex justify-center">
            <input type="submit" value="Change Password" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
        </div>
        </div>
        </form>
    </main>

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
</script>
</body>
</html>
