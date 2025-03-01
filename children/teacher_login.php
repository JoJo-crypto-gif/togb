<?php
session_start();
include '../config.php'; // Adjust path as needed

// If teacher is already logged in, redirect to dashboard.
if (isset($_SESSION['teacher_logged_in']) && $_SESSION['teacher_logged_in'] === true) {
    header("Location: teacher_dashboard.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Query the teachers table by phone number
    $sql = "SELECT id, first_name, last_name, password, profile_picture FROM teachers WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error = "Database error: " . $conn->error;
    } else {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $teacher['password'])) {
                // Set teacher session variables
                $_SESSION['teacher_logged_in'] = true;
                $_SESSION['teacher_id'] = $teacher['id'];
                $_SESSION['teacher_first_name'] = $teacher['first_name'];
                $_SESSION['teacher_last_name'] = $teacher['last_name'];
                $_SESSION['teacher_profile_picture'] = $teacher['profile_picture'];
                header("Location: teacher_dashboard.php");
                exit();
            } else {
                $error = "Invalid phone number or password.";
            }
        } else {
            $error = "Invalid phone number or password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Teacher Login</title>
   <!-- Tailwind CSS -->
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
   <div class="max-w-md w-full bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6 text-center">Teacher Login</h2>
      <?php if (!empty($error)): ?>
         <div class="mb-4 text-red-600 text-center"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form action="teacher_login.php" method="POST">
         <div class="mb-4">
            <label for="phone" class="block text-gray-700">Phone Number</label>
            <input type="text" name="phone" id="phone" class="mt-1 w-full p-2 border rounded" required>
         </div>
         <div class="mb-4">
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password" name="password" id="password" class="mt-1 w-full p-2 border rounded" required>
         </div>
         <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white p-2 rounded">Login</button>
      </form>
   </div>
</body>
</html>
