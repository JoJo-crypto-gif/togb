<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Validate teacher ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}
$teacher_id = intval($_GET['id']);

// Fetch teacher details
$sql = "SELECT * FROM teachers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

if (!$teacher) {
    die("Teacher not found.");
}

// Fetch assigned classes via teacher_classes linking table
$sql_classes = "
    SELECT cc.class_name, cc.description
    FROM teacher_classes tc
    JOIN church_classes cc ON tc.class_id = cc.id
    WHERE tc.teacher_id = ?";
$stmt_classes = $conn->prepare($sql_classes);
$stmt_classes->bind_param("i", $teacher_id);
$stmt_classes->execute();
$result_classes = $stmt_classes->get_result();
$classes = [];
while ($row = $result_classes->fetch_assoc()) {
    $classes[] = $row;
}
$stmt_classes->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Teacher</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow-md">
    <div class="flex items-center mb-6">
      <?php if (!empty($teacher['profile_picture'])): ?>
         <img src="../uploads/teachers/<?php echo htmlspecialchars($teacher['profile_picture']); ?>" alt="Profile Picture" class="w-24 h-24 rounded-full mr-4">
      <?php else: ?>
         <div class="w-24 h-24 rounded-full bg-gray-200 mr-4 flex items-center justify-center">
            <span class="text-gray-500">No Image</span>
         </div>
      <?php endif; ?>
      <div>
         <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']); ?></h2>
         <p><strong>Phone:</strong> <?php echo htmlspecialchars($teacher['phone']); ?></p>
      </div>
    </div>
    <h3 class="text-xl font-bold mb-2">Assigned Classes</h3>
    <?php if (count($classes) > 0): ?>
       <ul class="list-disc pl-5">
          <?php foreach ($classes as $class): ?>
             <li>
                <strong><?php echo htmlspecialchars($class['class_name']); ?></strong>
                <?php if (!empty($class['description'])): ?>
                   - <?php echo htmlspecialchars($class['description']); ?>
                <?php endif; ?>
             </li>
          <?php endforeach; ?>
       </ul>
    <?php else: ?>
       <p>No classes assigned.</p>
    <?php endif; ?>
    <div class="mt-6">
       <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Edit Teacher</a>
       <a href="view_teachers.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Back to Teachers List</a>
    </div>
  </div>
</body>
</html>
