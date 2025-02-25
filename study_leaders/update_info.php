<?php
session_start();
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['leader_id'])) {
    header('Location: login.php');
    exit();
}

$leader_id = $_SESSION['leader_id'];

// Fetch the current leader's information
$sql = "SELECT * FROM study_leaders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
$leader = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Password update only if provided
    $updatePassword = "";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $updatePassword = ", password = '$hashed_password'";
    }

    // Update leader details
    $sql = "UPDATE study_leaders SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ? $updatePassword WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $username, $email, $phone, $leader_id);

    if ($stmt->execute()) {
        echo '<script>alert("Profile updated successfully!");</script>';
        header('Location: dashboard.php'); // Redirect to dashboard or any preferred page
        exit();
    } else {
        echo '<script>alert("Error updating profile.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-center">Update Profile</h2>
        <form method="POST" action="">
            <label class="block mb-2">First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($leader['first_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mb-4">

            <label class="block mb-2">Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($leader['last_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mb-4">

            <label class="block mb-2">Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($leader['username']); ?>" required class="w-full p-2 border border-gray-300 rounded mb-4">

            <label class="block mb-2">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($leader['email']); ?>" required class="w-full p-2 border border-gray-300 rounded mb-4">

            <label class="block mb-2">Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($leader['phone']); ?>" required class="w-full p-2 border border-gray-300 rounded mb-4">

            <label class="block mb-2">New Password (leave blank to keep current password)</label>
            <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded mb-4">

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Update Profile</button>
        </form>
    </div>
</body>
</html>
