<?php
// update_child.php – Processes updates for a child record and re‑links parent details

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}
header('Content-Type: application/json');
include '../config.php';

// Helper function to sanitize input
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate required fields
$required = ['child_id', 'name', 'dob', 'school', 'gender', 'residence', 'church_class'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Field '$field' is required."]);
        exit();
    }
}

$child_id         = intval($_POST['child_id']);
$child_name       = clean_input($_POST['name']);
$child_dob        = clean_input($_POST['dob']);
$child_school     = clean_input($_POST['school']);
$child_gender     = clean_input($_POST['gender']);
$child_residence  = clean_input($_POST['residence']);
$child_phone      = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
$child_church_class = clean_input($_POST['church_class']);

// Process profile picture upload if a new file is provided
$child_profile_picture = "";
if (!empty($_FILES['profile_picture']['name'])) {
    $upload_dir = "../uploads/children/";
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
            $child_profile_picture = $new_file_name;
        } else {
            error_log("Failed to move uploaded file.");
        }
    } else {
        error_log("Invalid file type for profile picture.");
    }
}

// Update child record
if ($child_profile_picture != "") {
    $sql = "UPDATE children SET name = ?, dob = ?, school = ?, gender = ?, residence = ?, phone = ?, church_class = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $child_name, $child_dob, $child_school, $child_gender, $child_residence, $child_phone, $child_church_class, $child_profile_picture, $child_id);
} else {
    $sql = "UPDATE children SET name = ?, dob = ?, school = ?, gender = ?, residence = ?, phone = ?, church_class = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $child_name, $child_dob, $child_school, $child_gender, $child_residence, $child_phone, $child_church_class, $child_id);
}
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Child update failed: ' . $stmt->error]);
    $stmt->close();
    exit();
}
$stmt->close();

/*
  For parent's details, we re‑link the child:
  Here we remove any existing linking records for Parent1 and Parent2 for this child,
  then process the updated parent fields.
*/
function deleteParentLink($parentType, $child_id, $conn) {
    $sql = "DELETE FROM member_children WHERE child_id = ? AND parent_type = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("is", $child_id, $parentType);
        $stmt->execute();
        $stmt->close();
    }
}
deleteParentLink('Parent1', $child_id, $conn);
deleteParentLink('Parent2', $child_id, $conn);

// Process parent's fields (lookup and link if found)
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
processParent('parent1_name', 'parent1_phone', $child_id, 'Parent1', $conn);
processParent('parent2_name', 'parent2_phone', $child_id, 'Parent2', $conn);

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Child updated successfully.']);
$conn->close();
exit();
?>
