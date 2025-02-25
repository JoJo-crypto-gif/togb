<?php
// insert_child.php – Inserts a child record (including profile picture) and links parents if found

// Enable error reporting (development only; disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}
header('Content-Type: application/json');
include '../config.php';  // Adjust path as needed

// Helper function to sanitize input
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate required child fields (excluding profile picture)
$required = ['name', 'dob', 'school', 'gender', 'residence', 'church_class'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Field '$field' is required."]);
        exit();
    }
}

// Sanitize child inputs
$child_name         = clean_input($_POST['name']);
$child_dob          = clean_input($_POST['dob']);
$child_school       = clean_input($_POST['school']);
$child_gender       = clean_input($_POST['gender']);
$child_residence    = clean_input($_POST['residence']);
$child_phone        = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
$child_church_class = clean_input($_POST['church_class']);

// Process profile picture upload (if provided)
$child_profile_picture = "";
$upload_dir = "../uploads/children/"; // Adjust directory as needed
if (!empty($_FILES['profile_picture']['name'])) {
    $file_name = basename($_FILES['profile_picture']['name']);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_type, $allowed_types)) {
        $new_file_name = uniqid() . '.' . $file_type;
        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $target_file = $upload_dir . $new_file_name;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $child_profile_picture = $new_file_name;
        } else {
            // If file upload fails, you might decide to handle it differently
            error_log("Failed to move uploaded file for profile picture.");
        }
    } else {
        error_log("File type not allowed for profile picture.");
    }
}

// Insert the child record into the children table (including profile_picture)
$sql = "INSERT INTO children (name, dob, school, gender, residence, phone, church_class, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param("ssssssss", $child_name, $child_dob, $child_school, $child_gender, $child_residence, $child_phone, $child_church_class, $child_profile_picture);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
    $stmt->close();
    exit();
}
$child_id = $stmt->insert_id;
$stmt->close();

/*
  Helper function: process a parent's field.
  It looks up the parent's ID using the provided full name.
  If found, it inserts a linking record into member_children.
  If no matching member is found, it updates the child record with the manual parent details.
*/
function processParent($parentFieldName, $parentPhoneFieldName, $child_id, $parentType, $conn) {
    if (isset($_POST[$parentFieldName]) && trim($_POST[$parentFieldName]) !== "") {
        $p_fullname = clean_input($_POST[$parentFieldName]);
        $p_phone    = isset($_POST[$parentPhoneFieldName]) ? clean_input($_POST[$parentPhoneFieldName]) : "";
        error_log("Processing $parentFieldName: Searching for '$p_fullname'");
        $sql_lookup = "SELECT id FROM members WHERE CONCAT(first_name, ' ', last_name) = ? LIMIT 1";
        $stmt_lookup = $conn->prepare($sql_lookup);
        if (!$stmt_lookup) {
            error_log("Lookup prepare failed for $parentFieldName: " . $conn->error);
            return;
        }
        $stmt_lookup->bind_param("s", $p_fullname);
        $stmt_lookup->execute();
        $stmt_lookup->store_result();
        $p_id = null;
        if ($stmt_lookup->num_rows > 0) {
            $stmt_lookup->bind_result($p_id);
            $stmt_lookup->fetch();
            error_log("Found parent for $parentFieldName: ID = $p_id");
            // Insert linking record into member_children table
            $sql_link = "INSERT INTO member_children (member_id, child_id, parent_type) VALUES (?, ?, ?)";
            $stmt_link = $conn->prepare($sql_link);
            if ($stmt_link) {
                $stmt_link->bind_param("iis", $p_id, $child_id, $parentType);
                if(!$stmt_link->execute()){
                    error_log("Link execute failed for $parentFieldName: " . $stmt_link->error);
                }
                $stmt_link->close();
            } else {
                error_log("Link prepare failed for $parentFieldName: " . $conn->error);
            }
        } else {
            // No matching member found; update child record with manual parent details.
            error_log("No matching member found for $parentFieldName: '$p_fullname'. Updating child record.");
            $sql_update = "UPDATE children SET {$parentFieldName} = ?, {$parentPhoneFieldName} = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("ssi", $p_fullname, $p_phone, $child_id);
                if(!$stmt_update->execute()){
                    error_log("Update execute failed for $parentFieldName: " . $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                error_log("Update prepare failed for $parentFieldName: " . $conn->error);
            }
        }
        $stmt_lookup->close();
    }
}

// Process Parent 1 and Parent 2
processParent('parent1_name', 'parent1_phone', $child_id, 'Parent1', $conn);
processParent('parent2_name', 'parent2_phone', $child_id, 'Parent2', $conn);

ob_clean();
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Child added successfully.', 'child_id' => $child_id]);
$conn->close();
exit();
?>
