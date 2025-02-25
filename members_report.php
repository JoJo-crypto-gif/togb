<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Initialize variables for filters
$filter = isset($_POST['filter']) ? $_POST['filter'] : '';
$include_phone = isset($_POST['include_phone']);
$include_email = isset($_POST['include_email']);
$include_residence = isset($_POST['include_residence']);
$include_auxiliary = isset($_POST['include_auxiliary']);
$include_occupation = isset($_POST['include_occupation']);
$include_active_status = isset($_POST['include_active_status']);

// Construct the SQL query based on filters
$sql_base = "SELECT * FROM members WHERE 1=1";

if ($filter == 'youth') {
    $sql_base .= " AND auxiliary ='youth'";
} elseif ($filter == 'men') {
    $sql_base .= " AND auxiliary ='men'";
} elseif ($filter == 'women') {
    $sql_base .= " AND auxiliary ='women'";
} elseif ($filter == 'male') {
    $sql_base .= " AND gender ='male'";
} elseif ($filter == 'female') {
    $sql_base .= " AND gender ='female'";
}

// Execute the query
$result = $conn->query($sql_base);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Member Report</title>
    <style>
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
    <script>
        function printTable() {
            var printContents = document.getElementById('reportTable').outerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = "<html><head><title>Print Report</title></head><body>" + printContents + "</body>";

            window.print();

            document.body.innerHTML = originalContents;
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <div class="flex items-center">
            <a href="view_members.php" class="text-blue-500 hover:underline">
                <i class="fas fa-arrow-left"></i> 
            </a>
            <h1 class="text-2xl font-semibold ml-4">Members Report</h1>
        </div>
    </div>
</header>

<main class="flex-1 p-6">
    <form method="post" action="members_report.php" class="mb-8">
        <div class="flex flex-wrap gap-4 mb-4">
            <select name="filter" class="p-2 border border-gray-300 rounded mt-1 hover-3d w-full md:w-1/3">
                <option value="">All Members</option>
                <option value="youth" <?php if ($filter == 'youth') echo 'selected'; ?>>Youth</option>
                <option value="men" <?php if ($filter == 'men') echo 'selected'; ?>>Men</option>
                <option value="women" <?php if ($filter == 'women') echo 'selected'; ?>>Women</option>
                <option value="male" <?php if ($filter == 'male') echo 'selected'; ?>>Male</option>
                <option value="female" <?php if ($filter == 'female') echo 'selected'; ?>>Female</option>
            </select>

            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_phone" <?php if ($include_phone) echo 'checked'; ?>>
                <span class="ml-2">Add Phone</span>
            </label>

            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_email" <?php if ($include_email) echo 'checked'; ?>>
                <span class="ml-2">Add Email</span>
            </label>

            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_residence" <?php if ($include_residence) echo 'checked'; ?>>
                <span class="ml-2">Add Residence</span>
            </label>

            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_auxiliary" <?php if ($include_auxiliary) echo 'checked'; ?>>
                <span class="ml-2">Add Auxiliary</span>
            </label>

            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_occupation" <?php if ($include_occupation) echo 'checked'; ?>>
                <span class="ml-2">Add Ocupation</span>
            </label>
            <label class="flex items-center mt-1">
                <input type="checkbox" name="include_active_status" <?php if ($include_active_status) echo 'checked'; ?>>
                <span class="ml-2">Add Active Status</span>
            </label>
        </div>

        <input type="submit" value="Generate Report" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
    </form>

    <div id="reportTable">
        <?php
        if ($result && $result->num_rows > 0) {
            echo "<table class='bg-white shadow-md w-full'>";
            echo "<tr><th>First Name</th><th>Last Name</th>";
            if ($include_phone) {
                echo "<th>Phone</th>";
            }
            if ($include_email) {
                echo "<th>Email</th>";
            }
            if ($include_residence) {
                echo "<th>Residence</th>";
            }
            if ($include_auxiliary) {
                echo "<th>Auxiliary</th>";
            }
            if ($include_occupation) {
                echo "<th>Occupation</th>";
            }
            if ($include_active_status) {
                echo "<th>Active Status</th>";
            }
            echo "</tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['first_name'] . "</td>";
                echo "<td>" . $row['last_name'] . "</td>";
                if ($include_phone) {
                    echo "<td>" . $row['phone'] . "</td>";
                }
                if ($include_email) {
                    echo "<td>" . $row['email'] . "</td>";
                }
                if ($include_residence) {
                    echo "<td>" . $row['residence'] . "</td>";
                }
                if ($include_auxiliary) {
                    echo "<td>" . $row['auxiliary'] . "</td>";
                }
                if ($include_occupation) {
                    echo "<td>" . $row['occupation'] . "</td>";
                }
                if ($include_active_status) {
                    echo "<td>" . $row['active_status'] . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No members found.";
        }
        ?>
    </div>

    <button onclick="printTable()" class="mt-4 bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Print Report</button>
</main>

</body>
</html>
