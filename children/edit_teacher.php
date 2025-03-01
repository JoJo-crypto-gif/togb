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

// Fetch teacher's current class assignments
$sql_assigned = "SELECT class_id FROM teacher_classes WHERE teacher_id = ?";
$stmt_assigned = $conn->prepare($sql_assigned);
$stmt_assigned->bind_param("i", $teacher_id);
$stmt_assigned->execute();
$result_assigned = $stmt_assigned->get_result();
$assigned_classes = [];
while ($row = $result_assigned->fetch_assoc()){
    $assigned_classes[] = $row['class_id'];
}
$stmt_assigned->close();

// Fetch all available classes from church_classes
$sql_classes = "SELECT * FROM church_classes ORDER BY class_name ASC";
$result_classes = $conn->query($sql_classes);
$all_classes = [];
if ($result_classes) {
    while ($row = $result_classes->fetch_assoc()){
        $all_classes[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Teacher</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4">Edit Teacher Details</h2>
    <form id="editTeacherForm" action="update_teacher.php" method="POST" enctype="multipart/form-data">
      <!-- Hidden teacher id -->
      <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
      
      <div class="mb-4">
         <label class="block text-gray-700">First Name</label>
         <input type="text" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Last Name</label>
         <input type="text" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Phone</label>
         <input type="text" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>" required class="w-full border p-2 rounded">
      </div>
      <!-- Optionally, you can add a field to update the password -->
      <div class="mb-4">
         <label class="block text-gray-700">Password (Leave blank to keep unchanged)</label>
         <input type="password" name="password" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Profile Picture</label>
         <?php if (!empty($teacher['profile_picture'])): ?>
            <img src="../uploads/teachers/<?php echo htmlspecialchars($teacher['profile_picture']); ?>" alt="Profile Picture" class="w-24 h-24 rounded mb-2">
         <?php else: ?>
            <p>No image available.</p>
         <?php endif; ?>
         <input type="file" name="profile_picture" accept="image/*" class="w-full border p-2 rounded">
      </div>
      
      <!-- Class Assignment -->
      <div class="mb-4">
         <label class="block text-gray-700">Assign Classes</label>
         <?php if (count($all_classes) > 0): ?>
            <?php foreach ($all_classes as $class): ?>
               <div class="flex items-center mb-1">
                  <input type="checkbox" name="classes[]" value="<?php echo $class['id']; ?>" class="mr-2"
                  <?php echo in_array($class['id'], $assigned_classes) ? 'checked' : ''; ?>>
                  <span><?php echo htmlspecialchars($class['class_name']); ?></span>
               </div>
            <?php endforeach; ?>
         <?php else: ?>
            <p>No church classes available.</p>
         <?php endif; ?>
      </div>
      
      <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Update Teacher</button>
      <a href="view_teachers.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</a>
    </form>
  </div>
  
  <script>
    // Optional: AJAX form submission could be added here for a smoother user experience.
    // For now, this form submits normally to update_teacher.php.
  </script>
</body>
</html>
