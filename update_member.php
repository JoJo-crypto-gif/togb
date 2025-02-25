<!-- update_member.php -->
<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $auxiliary = $_POST['auxiliary'];
    $residence = $_POST['residence'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $marital_status = $_POST['marital_status'];
    $active_status = $_POST['active_status'];
    $baptism_status = $_POST['baptism_status'];
    $profile_picture = $_FILES['profile_picture']['name'];

    $occupation = $_POST['occupation'];
    $contact1_name = $_POST['contact1_name'];
    $contact1_relationship = $_POST['contact1_relationship'];
    $contact1_phone = $_POST['contact1_phone'];
    $contact2_name = $_POST['contact2_name'];
    $contact2_relationship = $_POST['contact2_relationship'];
    $contact2_phone = $_POST['contact2_phone'];

    if ($profile_picture) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $profile_picture_sql = ", profile_picture='$profile_picture'";
    } else {
        $profile_picture_sql = "";
    }

    $sql = "UPDATE members SET 
            first_name='$first_name', 
            last_name='$last_name', 
            phone='$phone', 
            email='$email', 
            auxiliary='$auxiliary', 
            residence='$residence', 
            dob='$dob', 
            gender='$gender', 
            marital_status='$marital_status', 
            active_status='$active_status', 
            baptism_status='$baptism_status',
            occupation='$occupation',
            contact1_name='$contact1_name', 
            contact1_relationship='$contact1_relationship', 
            contact1_phone='$contact1_phone', 
            contact2_name='$contact2_name', 
            contact2_relationship='$contact2_relationship', 
            contact2_phone='$contact2_phone' 
            $profile_picture_sql 
            WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        // Insert notification
        $notification_message = "Member $first_name $last_name has been updated.";
        $notification_sql = "INSERT INTO notifications (message, created_at) VALUES ('$notification_message', NOW())";
        $conn->query($notification_sql);

        echo json_encode(['status' => 'success', 'message' => 'Member updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating record: ' . $conn->error]);
    }
}

$conn->close();
?>
