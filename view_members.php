<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Set the number of items per page
$items_per_page = 10;

// Get the current page number from the query string, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calculate the offset for the SQL query,
$offset = ($page - 1) * $items_per_page;

// Get the search term from the query string,
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get the filter parameter from the query string,
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Construct the base SQL query
$sql_base = "SELECT * FROM members WHERE (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR occupation LIKE '%$search%')";

// Apply the filter conditions,
if ($filter == 'inactive') {
    $sql_base .= " AND active_status='No'";
} elseif ($filter == 'non_baptized') {
    $sql_base .= " AND baptism_status='No'";
}
  elseif ($filter == 'youth'){
    $sql_base .=" AND auxiliary ='youth'";
}
  elseif ($filter == 'men'){
    $sql_base .=" AND auxiliary ='men'";
}
  elseif ($filter == 'women'){
    $sql_base .=" AND auxiliary ='women'";
}

// Get the total number of members matching the search term and filter for pagination,
$sql_count = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql_base);
$result_count = $conn->query($sql_count);
$total_items = $result_count->fetch_assoc()['total'];

// Calculate the total number of pages,
$total_pages = ceil($total_items / $items_per_page);

// Add pagination to the SQL query
$sql = $sql_base . " LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

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
    <title>View members</title>

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

        th{
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    border-bottom-width: 2px;
    border-color: #e5e7eb;
    text-align: center;
    vertical-align: middle;
}

td{
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
        <h1 class="text-2xl font-semibold">View members</h1>
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
                    <a href="logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <main class="flex-1 p-6">
        <form method="get" action="view_members.php" class="flex flex-row gap-4 mb-8">
            <input type="text" name="search" placeholder="Search by name or occupation..." value="<?php echo htmlspecialchars($search); ?>" class="w-6/12 p-2 border border-gray-300 rounded mt-1 hover-3d">
            
            <select name="filter" onchange="this.form.submit()" class="w-30 p-2 border border-gray-300 rounded mt-1 hover-3d w-3/12">
                <option value="">All Members</option>
                <option value="inactive" <?php if ($filter == 'inactive') echo 'selected'; ?>>Inactive Members</option>
                <option value="non_baptized" <?php if ($filter == 'non_baptized') echo 'selected'; ?>>Non-Baptized Members</option>
                <option value="youth" <?php if ($filter == 'youth') echo 'selected'; ?>>Youth</option>
                <option value="men" <?php if ($filter == 'men') echo 'selected'; ?>>Men</option>
                <option value="women" <?php if ($filter == 'women') echo 'selected'; ?>>Women</option>
            </select>

            <input type="submit" value="Search" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
            <a href="members_report.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d"><button type="button" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Export</button></a>
        </form>
       <div>
        <?php
        if ($result->num_rows > 0) {
            echo "<table class='bg-white shadow-md w-full'>";
            echo "<tr><th>First Name</th><th>Last Name</th><th>Phone</th><th>Profile Picture</th><th>Email</th><th>Auxiliary</th><th>Active</th><th>Occupation</th><th>Action</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['first_name'] . "</td>";
                echo "<td>" . $row['last_name'] . "</td>";
                echo "<td>" . $row['phone'] . "</td>";
                echo "<td><img src='uploads/" . $row['profile_picture'] . "' width='50' height='50' class='justify-center align-center'></td>"; // Display profile picture
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['auxiliary'] . "</td>";
                echo "<td>" . $row['active_status'] . "</td>";
                echo "<td>" . $row['occupation'] . "</td>";
                echo "<td>
                <a href='view_member.php?id=" . $row['id'] . "' class='text-blue-500 hover:underline hover-3d'>View</a>
                <a href='edit_member.php?id=" . $row['id'] . "' class='text-green-500 hover:underline hover-3d'>Edit</a> 
               <!-- <a href='delete_member.php?id=" . $row['id'] . "' class='text-red-500 hover:underline hover-3d' onclick= 'return confirm('Are you sure you want to delete this category?')> Delete </a> -->
                </td>";
                echo "</tr>";
            }
            echo "</table>";

// Display pagination
echo "<div class='pagination mt-3 flex justify-center text-blue-300'>";

// Previous Page Link
if ($page > 1) {
    echo "<a href='view_members.php?page=" . ($page - 1) . "&search=" . urlencode($search) . "&filter=" . urlencode($filter) . "' class='px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover:3d mr-3' aria-label='Previous page'> < </a>";
}

// Page Numbers
$max_visible_pages = 5; // Maximum number of visible page links
$start_page = max(1, $page - floor($max_visible_pages / 2)); // Start the pagination from the middle
$end_page = min($total_pages, $start_page + $max_visible_pages - 1); // Ensure the last page is within the limit

// Ensure that there are pages before and after the current page range
if ($start_page > 1) {
    echo "<a href='view_members.php?page=1&search=" . urlencode($search) . "&filter=" . urlencode($filter) . "' class='px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover:3d' aria-label='First page'>1</a>";
    if ($start_page > 2) {
        echo "<span class='mx-1'>...</span>";
    }
}

for ($i = $start_page; $i <= $end_page; $i++) {
    if ($i == $page) {
        echo "<span class='px-3 py-1 border border-blue-500 bg-blue-500 rounded text-white'>$i</span>";
    } else {
        echo "<a href='view_members.php?page=$i&search=" . urlencode($search) . "&filter=" . urlencode($filter) . "' class='px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover:3d' aria-label='Page $i'>$i</a>";
    }

    // Add margin to separate items, except the last one
    if ($i < $end_page) {
        echo "<span class='mx-1'></span>";
    }
}

// Add ellipses if necessary for the end
if ($end_page < $total_pages) {
    if ($end_page < $total_pages - 1) {
        echo "<span class='mx-1'>...</span>";
    }
    echo "<a href='view_members.php?page=$total_pages&search=" . urlencode($search) . "&filter=" . urlencode($filter) . "' class='px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover:3d' aria-label='Last page'>$total_pages</a>";
}

// Next Page Link
if ($page < $total_pages) {
    echo "<a href='view_members.php?page=" . ($page + 1) . "&search=" . urlencode($search) . "&filter=" . urlencode($filter) . "' class='px-3 py-1 border border-blue-500 rounded hover:bg-blue-500 hover:text-white hover:3d ml-3' aria-label='Next page'> > </a>";
}

echo "</div>";

} else {
    echo "No members found.";
}

        ?>
        </div>
    </main>
</body>
</html>
