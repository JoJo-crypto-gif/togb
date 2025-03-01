<?php
// update_teacher.php – Processes teacher detail updates

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Helper function to sanitize input
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate required fields
if (!isset($_POST['teacher_id']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['phone'])) {
    die("Required fields missing.");
}

$teacher_id = intval($_POST['teacher_id']);
$first_name = clean_input($_POST['first_name']);
$last_name  = clean_input($_POST['last_name']);
$phone      = clean_input($_POST['phone']);
$password   = $_POST['password'];  // If provided, we'll update it
// Process profile picture upload if provided
$profile_picture = "";
if (!empty($_FILES['profile_picture']['name'])) {
    $upload_dir = "../uploads/teachers/";
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

// Update teacher record. If password is provided, hash and update it.
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($profile_picture != "") {
        $sql = "UPDATE teachers SET first_name = ?, last_name = ?, phone = ?, profile_picture = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $first_name, $last_name, $phone, $profile_picture, $hashed_password, $teacher_id);
    } else {
        $sql = "UPDATE teachers SET first_name = ?, last_name = ?, phone = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $hashed_password, $teacher_id);
    }
} else {
    if ($profile_picture != "") {
        $sql = "UPDATE teachers SET first_name = ?, last_name = ?, phone = ?, profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $first_name, $last_name, $phone, $profile_picture, $teacher_id);
    } else {
        $sql = "UPDATE teachers SET first_name = ?, last_name = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $first_name, $last_name, $phone, $teacher_id);
    }
}

if (!$stmt->execute()) {
    die("Update failed: " . $stmt->error);
}
$stmt->close();

// Update teacher's class assignments: remove old links and add new ones.
$sql_del = "DELETE FROM teacher_classes WHERE teacher_id = ?";
$stmt_del = $conn->prepare($sql_del);
$stmt_del->bind_param("i", $teacher_id);
$stmt_del->execute();
$stmt_del->close();

// Process new class assignments if provided
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

$conn->close();

// After updating, redirect back to view_teacher.php with a success message.
header("Location: view_teacher.php?id=" . $teacher_id . "&success=Teacher updated successfully");
exit();
?>
