<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Query teachers and join with church_classes via teacher_classes, using GROUP_CONCAT for assigned classes.
$sql = "SELECT t.*, GROUP_CONCAT(cc.class_name SEPARATOR ', ') AS classes
        FROM teachers t
        LEFT JOIN teacher_classes tc ON t.id = tc.teacher_id
        LEFT JOIN church_classes cc ON tc.class_id = cc.id
        GROUP BY t.id
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
$teachers = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $teachers[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Teachers</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="container mx-auto">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-3xl font-bold">Teachers List</h1>
      <a href="dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back to Dashboard</a>
    </div>
    <div class="overflow-x-auto bg-white shadow rounded">
      <table class="min-w-full">
         <thead>
            <tr class="bg-gray-200">
               <th class="py-2 px-4 border">ID</th>
               <th class="py-2 px-4 border">Name</th>
               <th class="py-2 px-4 border">Phone</th>
               <th class="py-2 px-4 border">Classes</th>
               <th class="py-2 px-4 border">Actions</th>
            </tr>
         </thead>
         <tbody>
            <?php if(count($teachers) > 0): ?>
              <?php foreach($teachers as $teacher): ?>
                <tr class="text-center">
                  <td class="py-2 px-4 border"><?php echo htmlspecialchars($teacher['id']); ?></td>
                  <td class="py-2 px-4 border"><?php echo htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']); ?></td>
                  <td class="py-2 px-4 border"><?php echo htmlspecialchars($teacher['phone']); ?></td>
                  <td class="py-2 px-4 border"><?php echo htmlspecialchars($teacher['classes'] ?: "None"); ?></td>
                  <td class="py-2 px-4 border">
                     <a href="view_teacher.php?id=<?php echo $teacher['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">View</a>
                     <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded ml-2">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="py-4 text-center">No teachers found.</td>
              </tr>
            <?php endif; ?>
         </tbody>
      </table>
    </div>
  </div>
</body>
</html>
