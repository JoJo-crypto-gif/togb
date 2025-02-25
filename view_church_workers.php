<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Define number of results per page
$results_per_page = 10;

// Get the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Handle search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Modify the SQL query to include search and pagination logic
$sql = "SELECT * FROM church_workers WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR role LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%' LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// Get the total number of records for pagination
$sql_total = "SELECT COUNT(*) AS total FROM church_workers WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR role LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
$result_total = $conn->query($sql_total);
$total_records = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_records / $results_per_page);

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
    <title>Manage church workers</title>
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
        <h1 class="text-2xl font-semibold">Manage church worker</h1>
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
                        <span class="sidebar-text">Manage Auxiliary Leaders</span>
                    </a>
                </li>
                <li>
                    <a href="add_church_worker.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-church"></i>
                        <span class="sidebar-text">Add New Church Workers</span>
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

    <main class="flex-1 p-6">
        <!-- Search Form -->
        <form method="GET" action="view_church_workers.php" class="mb-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." class="p-2 border border-gray-300 rounded w-6/12 mr-5 hover-3d">
            <button type="submit" class="p-2 bg-blue-500 text-white rounded hover-3d">Search</button>
        </form>

        <table class='bg-white shadow-md w-full'>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Start Date</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                    <td>
                        <a href="edit_church_worker.php?id=<?php echo $row['id']; ?>" class='text-green-500 hover:underline hover-3d'>Edit</a>
                        <a href="delete_church_worker.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" class='text-red-500 hover:underline hover-3d'>Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Pagination Links -->
        <div class="pagination mt-3 flex justify-center text-blue-300 flex-row gap-4">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="px-3 py-1 border border-blue-500 rounded"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="view_church_workers.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover-3d"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </main>
</div>
</body>
</html>
