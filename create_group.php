<?php
// Include the database connection
include 'config.php';

// Fetch existing group leaders from the study_leader table
$sql_leaders = "
    SELECT id, CONCAT(first_name, ' ', last_name) AS name 
    FROM study_leaders
";
$result_leaders = $conn->query($sql_leaders);

// Fetch existing study groups and their leaders' names
$sql_groups = "
    SELECT sg.id, sg.name AS group_name, CONCAT(sl.first_name, ' ', sl.last_name) AS leader_name, sg.created_at
    FROM study_groups AS sg
    JOIN study_leaders AS sl ON sg.leader_id = sl.id
";
$result_groups = $conn->query($sql_groups);

// Check if the form is submitted to create a new group
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_group'])) {
    $group_name = $_POST['group_name'];
    $leader_id = $_POST['leader_id'];

    // Insert the new study group into the study_groups table
    $sql = "INSERT INTO study_groups (name, leader_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $group_name, $leader_id);

    if ($stmt->execute()) {
        echo "Study group created successfully!";
        // Refresh the list of groups after adding a new one
        $result_groups = $conn->query($sql_groups);
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Check if the form is submitted to delete a group
if (isset($_GET['delete_id'])) {
    $group_id = $_GET['delete_id'];

    // Delete the study group
    $sql = "DELETE FROM study_groups WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);

    if ($stmt->execute()) {
        echo "Study group deleted successfully!";
        // Refresh the list of groups after deletion
        $result_groups = $conn->query($sql_groups);
    } else {
        echo "Error deleting group: " . $stmt->error;
    }
}

// Fetch the group for editing if edit_id is set
$edit_group = null;
if (isset($_GET['edit_id'])) {
    $group_id = $_GET['edit_id'];
    $sql_edit = "SELECT * FROM study_groups WHERE id = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_group = $result->fetch_assoc();
}

// Handle the form submission for editing a group
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_group'])) {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];
    $leader_id = $_POST['leader_id'];

    // Update the study group in the database
    $sql = "UPDATE study_groups SET name = ?, leader_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $group_name, $leader_id, $group_id);

    if ($stmt->execute()) {
        echo "Study group updated successfully!";
        // Refresh the list of groups after updating
        $result_groups = $conn->query($sql_groups);
        $edit_group = null; // Clear the edit group after update
    } else {
        echo "Error updating group: " . $stmt->error;
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
    <title>Study Groups</title>

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
        <h1 class="text-2xl font-semibold"><?php echo isset($edit_group) ? "Edit Study Group" : "Create Study Group"; ?></h1>
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
                    <a href="create_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-plus"></i>
                        <span class="sidebar-text">Add Study Leader</span>
                    </a>
                </li>
                <li>
                    <a href="manage_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-tie"></i>
                        <span class="sidebar-text">Manage Study Leader</span>
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
    <form method="POST" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="group_id" value="<?php echo isset($edit_group) ? $edit_group['id'] : ''; ?>">
        
        <div class="col-span-2">
        <label for="group_name" class="block text-gray-700">Group Name:</label>
        <input type="text" id="group_name" name="group_name" value="<?php echo isset($edit_group) ? $edit_group['name'] : ''; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <div class="col-span-2">
        <label for="leader_id" class="block text-gray-700">Select Group Leader:</label>
        <select id="leader_id" name="leader_id" required class="w-full p-2 border border-gray-300 rounded mt-1">
            <option value="">Select Leader</option>
            <?php
            if ($result_leaders->num_rows > 0) {
                while ($row = $result_leaders->fetch_assoc()) {
                    $selected = isset($edit_group) && $edit_group['leader_id'] == $row['id'] ? 'selected' : '';
                    echo "<option value='" . $row['id'] . "' $selected>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select>
        </div>
        
        <div class="col-span-2 flex justify-center">
        <button type="submit" name="<?php echo isset($edit_group) ? 'update_group' : 'create_group'; ?>" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
            <?php echo isset($edit_group) ? 'Update Study Group' : 'Create Study Group'; ?>
        </button>
        </div>
    </div>
    </form>

    <h2 class="text-xl font-bold mb-4">Existing Study Groups</h2>
    <div class="bg-white shadow-md rounded p-4">
    <table class='bg-white shadow-md w-full'>
        <tr>
            <th>ID</th>
            <th>Group Name</th>
            <th>Leader Name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result_groups->num_rows > 0) {
            while ($row = $result_groups->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['group_name'] . "</td>";
                echo "<td>" . $row['leader_name'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>
                    <a href='?edit_id=" . $row['id'] . "' class='text-blue-500 hover:underline hover-3d'>Edit</a> | 
                    <a href='?delete_id=" . $row['id'] . "' onclick=\"return confirm('Are you sure you want to delete this group?');\" class='text-red-500 hover:underline hover-3d'>Delete</a>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No groups found</td></tr>";
        }
        ?>
    </table>
    </div>
    </main>
</body>
</html>
