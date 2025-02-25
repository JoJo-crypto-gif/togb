<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the member exists
    $sql = "SELECT * FROM members WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch member details for notification
        $member = $result->fetch_assoc();
        $member_name = $member['first_name'] . ' ' . $member['last_name'];

        // Display confirmation dialog before deleting
        echo "<script>
                if (confirm('Are you sure you want to delete member: $member_name?')) {
                    // User confirmed deletion
                    deleteMember($id);
                } else {
                    // User cancelled deletion
                    window.location.href = 'view_members.php';
                }

                function deleteMember(id) {
                    // Make AJAX request to delete member
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_member.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status == 200) {
                            // Successful deletion
                            alert('Member deleted successfully.');
                            window.location.href = 'view_members.php';
                        } else {
                            // Error deleting member
                            alert('Error deleting member: ' + xhr.responseText);
                            window.location.href = 'view_members.php';
                        }
                    };
                    xhr.send('id=' + id);
                }
              </script>";
    } else {
        echo "<script>alert('Member not found.'); window.location.href='view_members.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='view_members.php';</script>";
}

$conn->close();
?>
