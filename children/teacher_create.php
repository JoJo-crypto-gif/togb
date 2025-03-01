<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Fetch available church classes for assignment
$sql_classes = "SELECT * FROM church_classes ORDER BY class_name ASC";
$result_classes = $conn->query($sql_classes);
$classes = [];
if ($result_classes) {
    while ($row = $result_classes->fetch_assoc()){
        $classes[] = $row;
    }
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get teacher details from the form
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $phone      = trim($_POST['phone']);
    $password   = $_POST['password']; // raw password (to be hashed)
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($password)) {
        $error = "All required fields must be provided.";
    } else {
        // Process profile picture upload if provided
        $profile_picture = "";
        $upload_dir = "../uploads/teachers/"; // Adjust path as needed
        if (!empty($_FILES['profile_picture']['name'])) {
            $file_name = basename($_FILES['profile_picture']['name']);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_type, $allowed_types)) {
                $new_file_name = uniqid() . '.' . $file_type;
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $target_file = $upload_dir . $new_file_name;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    $profile_picture = $new_file_name;
                }
            }
        }
        
        // Hash the password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert the teacher record into the teachers table
        $sql = "INSERT INTO teachers (first_name, last_name, phone, profile_picture, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("sssss", $first_name, $last_name, $phone, $profile_picture, $hashed_password);
            if ($stmt->execute()) {
                $teacher_id = $stmt->insert_id;
                $stmt->close();
                
                // Process class assignments if any were selected
                if (isset($_POST['classes']) && is_array($_POST['classes'])) {
                    foreach ($_POST['classes'] as $class_id) {
                        $sql_link = "INSERT INTO teacher_classes (teacher_id, class_id) VALUES (?, ?)";
                        $stmt_link = $conn->prepare($sql_link);
                        if ($stmt_link) {
                            $stmt_link->bind_param("ii", $teacher_id, $class_id);
                            $stmt_link->execute();
                            $stmt_link->close();
                        }
                    }
                }
                header("Location: view_teachers.php?success=Teacher created successfully");
                exit();
            } else {
                $error = "Error executing query: " . $stmt->error;
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Teacher Account</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4">Create Teacher Account</h2>
    <?php if (!empty($error)): ?>
      <div class="mb-4 text-red-600"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="teacher_create.php" method="POST" enctype="multipart/form-data">
      <div class="mb-4">
         <label class="block text-gray-700">First Name</label>
         <input type="text" name="first_name" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Last Name</label>
         <input type="text" name="last_name" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Phone Number</label>
         <input type="text" name="phone" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Password</label>
         <input type="password" name="password" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Profile Picture (Optional)</label>
         <input type="file" name="profile_picture" accept="image/*" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Assign Classes</label>
         <?php if (count($classes) > 0): ?>
            <?php foreach ($classes as $class): ?>
               <div class="flex items-center mb-1">
                  <input type="checkbox" name="classes[]" value="<?php echo $class['id']; ?>" class="mr-2">
                  <span><?php echo htmlspecialchars($class['class_name']); ?></span>
               </div>
            <?php endforeach; ?>
         <?php else: ?>
            <p>No church classes available.</p>
         <?php endif; ?>
      </div>
      <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white p-2 rounded">Create Teacher</button>
    </form>
  </div>
</body>
</html>
