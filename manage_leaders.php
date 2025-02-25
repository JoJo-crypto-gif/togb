<?php
// Include the database connection
include 'config.php';

// Fetch existing group leaders from the study_leader table
$sql_leaders = "
SELECT id, CONCAT(first_name, ' ', last_name) AS leader_name, phone, username, created_at 
FROM study_leaders
";
$result_leaders = $conn->query($sql_leaders);

// Check if the form is submitted to delete a leader
if (isset($_GET['delete_id'])) {
    $leader_id = $_GET['delete_id'];

    // Delete the leader from the study_leader table
    $sql = "DELETE FROM study_leaders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leader_id);

    if ($stmt->execute()) {
        echo "Leader deleted successfully!";
        // Refresh the list of leaders after deletion
        $result_leaders = $conn->query($sql_leaders);
    } else {
        echo "Error deleting leader: " . $stmt->error;
    }
}

// Fetch the leader for editing if edit_id is set
$edit_leader = null;
if (isset($_GET['edit_id'])) {
    $leader_id = $_GET['edit_id'];
    $sql_edit = "
        SELECT id, CONCAT(first_name, ' ', last_name) AS leader_name, username, phone 
        FROM study_leaders
        WHERE id = ?
    ";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $leader_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_leader = $result->fetch_assoc();
}

// Handle the form submission for editing a leader
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_leader'])) {
    $leader_id = $_POST['leader_id'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update the leader details in the study_leader table
    if ($password) {
        $sql = "UPDATE study_leaders SET username = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $phone, $password, $leader_id);
    } else {
        $sql = "UPDATE study_leaders SET username = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $phone, $leader_id);
    }

    if ($stmt->execute()) {
        echo "Leader updated successfully!";
        // Refresh the list of leaders after updating
        $result_leaders = $conn->query($sql_leaders);
        $edit_leader = null; // Clear the edit leader after update
    } else {
        echo "Error updating leader: " . $stmt->error;
    }
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
    <title>Bible Study Leader</title>

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
        <h1 class="text-2xl font-semibold"><?php echo isset($edit_leader) ? "Edit Study Group Leader" : "Manage Study Group Leaders"; ?></h1>
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
                    <a href="create_group.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-list-ul"></i>
                        <span class="sidebar-text">Manage Study Groups</span>
                    </a>
                </li>
                <li>
                    <a href="create_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-plus"></i>
                        <span class="sidebar-text">Add Study Leader</span>
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
    <!-- Edit Leader Form -->
    <?php if (isset($edit_leader)): ?>
        <form method="POST" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
        <input type="hidden" name="leader_id" value="<?php echo $edit_leader['id']; ?>" class="w-full p-2 border border-gray-300 rounded mt-1">

        <div class="col-span-1">
        <label for="leader_name" class="block text-gray-700">Leader Name:</label>
        <input type="text" id="leader_name" value="<?php echo $edit_leader['leader_name']; ?>" readonly class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <div class="col-span-1">
        <label for="phone" class="block text-gray-700">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo $edit_leader['phone']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <div class="col-span-1">
        <label for="username" class="block text-gray-700">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $edit_leader['username']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <div class="col-span-1">
        <label for="password" class="block text-gray-700">Password (leave blank to keep unchanged):</label>
        <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <div class="col-span-2 flex justify-center">
        <button type="submit" name="update_leader" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Update Leader</button>
        </div>
        </div>
    </form>
    <?php endif; ?>

    <h2 class="text-xl font-bold mb-4">Existing Study Group Leaders</h2>
    <div class="bg-white shadow-md rounded p-4">
    <table class='bg-white shadow-md w-full'>
        <tr>
            <th>ID</th>
            <th>Leader Name</th>
            <th>Phone</th>
            <th>Username</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result_leaders->num_rows > 0) {
            while ($row = $result_leaders->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['leader_name'] . "</td>";
                echo "<td>" . $row['phone'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>
                    <a href='?edit_id=" . $row['id'] . "'>Edit</a> | 
                    <a href='?delete_id=" . $row['id'] . "' onclick=\"return confirm('Are you sure you want to delete this leader?');\">Delete</a>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No leaders found</td></tr>";
        }
        ?>
    </table>
    </div>
    </main>
</body>
</html>
