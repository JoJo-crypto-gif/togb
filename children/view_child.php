<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Ensure child ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request");
}

$child_id = intval($_GET['id']);

// Fetch child details
$sql = "SELECT * FROM children WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$result = $stmt->get_result();
$child = $result->fetch_assoc();
$stmt->close();

if (!$child) {
    die("Child not found.");
}

// Fetch linked parents from member_children table
$sql_parents = "
    SELECT m.id, m.first_name, m.last_name, m.phone, mc.parent_type
    FROM members m
    JOIN member_children mc ON m.id = mc.member_id
    WHERE mc.child_id = ?";
$stmt_parents = $conn->prepare($sql_parents);
$stmt_parents->bind_param("i", $child_id);
$stmt_parents->execute();
$result_parents = $stmt_parents->get_result();
$parents = [];
while ($row = $result_parents->fetch_assoc()){
    $parents[$row['parent_type']] = $row;
}
$stmt_parents->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Child</title>
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow-md">
        <div class="flex flex-col items-center mb-6">
            <?php if (!empty($child['profile_picture'])): ?>
                <img src="../uploads/children/<?php echo htmlspecialchars($child['profile_picture']); ?>" 
                     alt="Profile Picture" 
                     class="w-32 h-32 rounded-full object-cover mb-4">
            <?php else: ?>
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <span class="text-gray-500">No Image</span>
                </div>
            <?php endif; ?>
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($child['name']); ?></h2>
        </div>

        <div class="mb-4">
            <strong>Date of Birth:</strong> <?php echo htmlspecialchars($child['dob']); ?>
        </div>
        <div class="mb-4">
            <strong>School/Class/Stage:</strong> <?php echo htmlspecialchars($child['school']); ?>
        </div>
        <div class="mb-4">
            <strong>Gender:</strong> <?php echo htmlspecialchars($child['gender']); ?>
        </div>
        <div class="mb-4">
            <strong>Residence:</strong> <?php echo htmlspecialchars($child['residence']); ?>
        </div>
        <div class="mb-4">
            <strong>Phone:</strong> <?php echo htmlspecialchars($child['phone'] ?: "N/A"); ?>
        </div>
        <div class="mb-4">
            <strong>Church Class:</strong> <?php echo htmlspecialchars($child['church_class']); ?>
        </div>

        <h3 class="text-xl font-bold mt-6">Parents</h3>
        <div class="mb-4">
            <strong>Parent 1:</strong> 
            <?php
            if (isset($parents['Parent1'])) {
                echo htmlspecialchars($parents['Parent1']['first_name'] . " " . $parents['Parent1']['last_name'])
                     . " - " . htmlspecialchars($parents['Parent1']['phone']);
            } else {
                echo "Name: " . htmlspecialchars($child['parent1_name'] ?? "N/A") . 
                     " | Phone: " . htmlspecialchars($child['parent1_phone'] ?? "N/A");
            }
            ?>
        </div>
        <div class="mb-4">
            <strong>Parent 2:</strong> 
            <?php
            if (isset($parents['Parent2'])) {
                echo htmlspecialchars($parents['Parent2']['first_name'] . " " . $parents['Parent2']['last_name'])
                     . " - " . htmlspecialchars($parents['Parent2']['phone']);
            } else {
                echo "Name: " . htmlspecialchars($child['parent2_name'] ?? "N/A") . 
                     " | Phone: " . htmlspecialchars($child['parent2_phone'] ?? "N/A");
            }
            ?>
        </div>
        <a href="view_children.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back to Children List</a>
    </div>
</body>
</html>
