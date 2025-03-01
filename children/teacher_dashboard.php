<?php
session_start();
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit();
}

include '../config.php';

$teacher_id = $_SESSION['teacher_id'];

// Retrieve teacher profile information from the teachers table
$sql_teacher = "SELECT * FROM teachers WHERE id = ?";
$stmt_teacher = $conn->prepare($sql_teacher);
$stmt_teacher->bind_param("i", $teacher_id);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();
$teacher = $result_teacher->fetch_assoc();
$stmt_teacher->close();

// Retrieve teacher's assigned classes from the linking table
$sql_classes = "
    SELECT cc.id AS class_id, cc.class_name, cc.description
    FROM teacher_classes tc
    JOIN church_classes cc ON tc.class_id = cc.id
    WHERE tc.teacher_id = ?
";
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
   <title>Teacher Dashboard</title>
   <!-- Tailwind CSS -->
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="container mx-auto p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($teacher['first_name']); ?>!</h1>
      <a href="teacher_logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Logout</a>
    </div>
    
    <!-- Profile Section -->
    <div class="bg-white p-6 rounded shadow mb-6">
      <h2 class="text-2xl font-bold mb-4">My Profile</h2>
      <div class="flex items-center">
        <?php if (!empty($teacher['profile_picture'])): ?>
          <img src="../uploads/teachers/<?php echo htmlspecialchars($teacher['profile_picture']); ?>" alt="Profile Picture" class="w-24 h-24 rounded-full mr-4">
        <?php else: ?>
          <div class="w-24 h-24 rounded-full bg-gray-200 mr-4 flex items-center justify-center">
            <span class="text-gray-500">No Image</span>
          </div>
        <?php endif; ?>
        <div>
          <p class="text-xl font-bold"><?php echo htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']); ?></p>
          <p><strong>Phone:</strong> <?php echo htmlspecialchars($teacher['phone']); ?></p>
        </div>
      </div>
    </div>
    
    <!-- Classes Section -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-4">My Classes</h2>
      <?php if (count($classes) > 0): ?>
        <ul class="space-y-4">
          <?php foreach ($classes as $class): ?>
            <li class="p-4 border rounded">
              <h3 class="text-xl font-bold"><?php echo htmlspecialchars($class['class_name']); ?></h3>
              <?php if (!empty($class['description'])): ?>
                <p><?php echo htmlspecialchars($class['description']); ?></p>
              <?php endif; ?>
              <a href="class_details.php?id=<?php echo $class['class_id']; ?>" class="mt-2 inline-block bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">View Class Details</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You have not been assigned any classes yet.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
