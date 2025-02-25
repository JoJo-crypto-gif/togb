<?php
// Database connection
$servername = "localhost";
$username = "username"; // replace with your database username
$password = "password"; // replace with your database password
$dbname = "your_database_name"; // replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to count members not in any group
$sql_count_members_no_group = "
    SELECT COUNT(*) AS total_not_in_group
    FROM members
    WHERE id NOT IN (SELECT member_id FROM group_members)";
$result_count = $conn->query($sql_count_members_no_group);

$count = 0;
if ($result_count->num_rows > 0) {
    $row = $result_count->fetch_assoc();
    $count = $row['total_not_in_group'];
}

// Fetch the list of members not in any group (optional: in case you want to display them)
$sql_members_no_group = "
    SELECT id, name FROM members
    WHERE id NOT IN (SELECT member_id FROM group_members)";
$result_members_no_group = $conn->query($sql_members_no_group);

$members_no_group = [];
if ($result_members_no_group->num_rows > 0) {
    while($row = $result_members_no_group->fetch_assoc()) {
        $members_no_group[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Not in Any Group</title>
    <script>
        // Function to toggle the visibility of the list
        function toggleMembersNoGroup() {
            var list = document.getElementById("members-list");
            if (list.style.display === "none") {
                list.style.display = "block";
            } else {
                list.style.display = "none";
            }
        }
    </script>
    <style>
        /* Some simple styling for the page */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .bg-white {
            background-color: white;
        }
        .p-6 {
            padding: 24px;
        }
        .rounded {
            border-radius: 8px;
        }
        .shadow-md {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .hover-3d:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease-in-out;
        }
        .bg-blue-500 {
            background-color: #4299e1;
        }
        .text-white {
            color: white;
        }
        .px-4 {
            padding-left: 16px;
            padding-right: 16px;
        }
        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .rounded {
            border-radius: 8px;
        }
        .cursor-not-allowed {
            cursor: not-allowed;
        }
        .bg-gray-400 {
            background-color: #cbd5e0;
        }
        .hover:bg-blue-600:hover {
            background-color: #3182ce;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Button to show/hide the list of members not in any group -->
    <?php if ($count > 0): ?>
        <div class="bg-white p-6 rounded shadow-md hover-3d">
            <button onclick="toggleMembersNoGroup()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                View Members Not in Any Group (<?php echo $count; ?>)
            </button>
        </div>
    <?php else: ?>
        <div class="bg-white p-6 rounded shadow-md">
            <button class="bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed" disabled>
                No Members Without Groups
            </button>
        </div>
    <?php endif; ?>

    <!-- List of members not in any group, initially hidden -->
    <div id="members-list" style="display: none; margin-top: 20px;">
        <h2>Members Not in Any Group</h2>
        <ul>
            <?php if (count($members_no_group) > 0): ?>
                <?php foreach ($members_no_group as $member): ?>
                    <li><?php echo htmlspecialchars($member['name']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No members found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>
