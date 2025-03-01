<?php
session_start();
if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: teacher_login.php");
    exit();
}

include '../config.php';

// Validate class ID passed via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}
$class_id = intval($_GET['id']);

// Fetch church class details from church_classes table
$sql_class = "SELECT * FROM church_classes WHERE id = ?";
$stmt_class = $conn->prepare($sql_class);
$stmt_class->bind_param("i", $class_id);
$stmt_class->execute();
$result_class = $stmt_class->get_result();
$church_class = $result_class->fetch_assoc();
$stmt_class->close();

if (!$church_class) {
    die("Class not found.");
}

// We'll assume children table stores church_class as a string that matches church_classes.class_name
$class_name = $church_class['class_name'];

// Fetch children enrolled in this class
$sql_children = "SELECT * FROM children WHERE church_class = ?";
$stmt_children = $conn->prepare($sql_children);
$stmt_children->bind_param("s", $class_name);
$stmt_children->execute();
$result_children = $stmt_children->get_result();
$children = [];
while ($row = $result_children->fetch_assoc()) {
    $children[] = $row;
}
$stmt_children->close();
$total_children = count($children);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Class Details - <?php echo htmlspecialchars($class_name); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="container mx-auto">
    <!-- Header and Navigation -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Class: <?php echo htmlspecialchars($class_name); ?></h1>
      <a href="teacher_dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back to Dashboard</a>
    </div>
    
    <!-- Optional Class Description -->
    <?php if (!empty($church_class['description'])): ?>
      <div class="mb-6 p-4 bg-white shadow rounded">
         <p><?php echo htmlspecialchars($church_class['description']); ?></p>
      </div>
    <?php endif; ?>
    
    <!-- Navigation Tiles -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <!-- Tile: Manage Attendance -->
      <a href="attendance.php?class_id=<?php echo $class_id; ?>" class="block bg-green-500 hover:bg-green-600 text-white p-4 rounded shadow text-center">
         <div class="text-4xl font-bold">Attendance</div>
         <div class="mt-2 text-sm">Take & Manage Attendance</div>
      </a>
      
      <!-- Tile: Manage Children -->
      <a href="manage_children.php?class_id=<?php echo $class_id; ?>" class="block bg-purple-500 hover:bg-purple-600 text-white p-4 rounded shadow text-center">
         <div class="text-4xl font-bold">Children</div>
         <div class="mt-2 text-sm">Manage Children</div>
      </a>
      
      <!-- Tile: Total Children -->
      <div class="block bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded shadow text-center">
         <div class="text-4xl font-bold"><?php echo $total_children; ?></div>
         <div class="mt-2 text-sm">Total Children</div>
      </div>
    </div>
    
    <!-- Table of Children -->
    <div class="bg-white shadow rounded overflow-x-auto">
      <table class="min-w-full">
         <thead>
            <tr class="bg-gray-200">
               <th class="py-2 px-4 border">ID</th>
               <th class="py-2 px-4 border">Name</th>
               <th class="py-2 px-4 border">DOB</th>
               <th class="py-2 px-4 border">Gender</th>
               <th class="py-2 px-4 border">Phone</th>
               <th class="py-2 px-4 border">Actions</th>
            </tr>
         </thead>
         <tbody>
            <?php if(count($children) > 0): ?>
               <?php foreach($children as $child): ?>
                  <tr class="text-center">
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['id']); ?></td>
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['name']); ?></td>
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['dob']); ?></td>
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['gender']); ?></td>
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['phone']); ?></td>
                     <td class="py-2 px-4 border">
                        <a href="view_child.php?id=<?php echo $child['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">View</a>
                        <a href="edit_child.php?id=<?php echo $child['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded ml-2">Edit</a>
                     </td>
                  </tr>
               <?php endforeach; ?>
            <?php else: ?>
               <tr>
                  <td colspan="6" class="py-4 text-center">No children found for this class.</td>
               </tr>
            <?php endif; ?>
         </tbody>
      </table>
    </div>
    
  </div>
</body>
</html>
