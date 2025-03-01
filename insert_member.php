<?php
// insert_member.php
// For debugging purposes—remove these lines in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');
include 'config.php';

// Function to sanitize input
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate required fields
if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit();
}

// Sanitize member inputs
$first_name      = clean_input($_POST['first_name']);
$last_name       = clean_input($_POST['last_name']);
$phone           = clean_input($_POST['phone']);
$email           = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$auxiliary       = clean_input($_POST['auxiliary']);
$residence       = clean_input($_POST['residence']);
$dob             = clean_input($_POST['dob']);
$gender          = clean_input($_POST['gender']);
$marital_status  = clean_input($_POST['marital_status']);
$active_status   = clean_input($_POST['active_status']);
$baptism_status  = clean_input($_POST['baptism_status']);
$occupation      = isset($_POST['occupation']) ? clean_input($_POST['occupation']) : '';

// Emergency contacts
$contact1_name         = isset($_POST['contact1_name']) ? clean_input($_POST['contact1_name']) : '';
$contact1_relationship = isset($_POST['contact1_relationship']) ? clean_input($_POST['contact1_relationship']) : '';
$contact1_phone        = isset($_POST['contact1_phone']) ? clean_input($_POST['contact1_phone']) : '';
$contact2_name         = isset($_POST['contact2_name']) ? clean_input($_POST['contact2_name']) : '';
$contact2_relationship = isset($_POST['contact2_relationship']) ? clean_input($_POST['contact2_relationship']) : '';
$contact2_phone        = isset($_POST['contact2_phone']) ? clean_input($_POST['contact2_phone']) : '';

// Handle profile picture upload
$profile_picture = null;
$upload_dir = 'uploads/';
if (!empty($_FILES['profile_picture']['name'])) {
    $file_name = basename($_FILES['profile_picture']['name']);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_type, $allowed_types)) {
        $new_file_name = uniqid() . '.' . $file_type;
        $target_file = $upload_dir . $new_file_name;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $new_file_name;
        }
    }
}

// Insert member into database
$sql = "INSERT INTO members 
    (first_name, last_name, phone, email, auxiliary, residence, dob, gender, marital_status, active_status, baptism_status, profile_picture, occupation, contact1_name, contact1_relationship, contact1_phone, contact2_name, contact2_relationship, contact2_phone) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssssssss", 
    $first_name, $last_name, $phone, $email, $auxiliary, $residence, $dob, $gender, 
    $marital_status, $active_status, $baptism_status, $profile_picture, $occupation, 
    $contact1_name, $contact1_relationship, $contact1_phone, $contact2_name, $contact2_relationship, $contact2_phone
);


if ($stmt->execute()) {
    $member_id = $stmt->insert_id;

    // Handle children input if provided
    if (!empty($_POST['children'])) {
        $children = json_decode($_POST['children'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format for children: ' . json_last_error_msg()]);
            exit();
        }
        foreach ($children as $child) {
            // Required child fields: name, dob, school, gender, residence
            if (empty($child['name']) || empty($child['dob']) || empty($child['school']) || empty($child['gender']) || empty($child['residence'])) {
                continue;
            }
            $child_name      = clean_input($child['name']);
            $child_dob       = clean_input($child['dob']);
            $child_school    = clean_input($child['school']);
            $child_gender    = clean_input($child['gender']);
            $child_residence = clean_input($child['residence']);
            $child_phone     = isset($child['phone']) ? clean_input($child['phone']) : '';

            // Check if the child already exists (by name and dob)
            $sql_check = "SELECT id FROM children WHERE name = ? AND dob = ? LIMIT 1";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ss", $child_name, $child_dob);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $stmt_check->bind_result($existing_child_id);
                $stmt_check->fetch();
                $child_id = $existing_child_id;
            } else {
                // Insert new child
                $sql_child = "INSERT INTO children (name, dob, school, gender, residence, phone) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_child = $conn->prepare($sql_child);
                $stmt_child->bind_param("ssssss", $child_name, $child_dob, $child_school, $child_gender, $child_residence, $child_phone);
                $stmt_child->execute();
                $child_id = $stmt_child->insert_id;
                $stmt_child->close();
            }
            $stmt_check->close();

            // Determine parent_type based on existing links for this child
            $sql_count = "SELECT COUNT(*) FROM member_children WHERE child_id = ?";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->bind_param("i", $child_id);
            $stmt_count->execute();
            $stmt_count->bind_result($linkCount);
            $stmt_count->fetch();
            $stmt_count->close();
            $parent_type = ($linkCount == 0) ? 'Parent1' : 'Parent2';

            // Link child to member in member_children table
            $sql_link = "INSERT INTO member_children (member_id, child_id, parent_type) VALUES (?, ?, ?)";
            $stmt_link = $conn->prepare($sql_link);
            $stmt_link->bind_param("iis", $member_id, $child_id, $parent_type);
            $stmt_link->execute();
            $stmt_link->close();
        }
    }

    // Insert notification
    $notification_message = "New member added: $first_name $last_name";
    $sql_notification = "INSERT INTO notifications (message) VALUES (?)";
    $stmt_notification = $conn->prepare($sql_notification);
    $stmt_notification->bind_param("s", $notification_message);
    $stmt_notification->execute();
    $stmt_notification->close();

    echo json_encode(['status' => 'success', 'message' => 'Member added successfully.']);
} else {
    error_log("Database Insert Error: " . $stmt->error);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while adding the member.']);
}

$stmt->close();
$conn->close();
?>
