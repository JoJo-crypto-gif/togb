<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if admin with the provided phone number or email already exists
    $check_sql = "SELECT * FROM admins WHERE phone='$phone' OR email='$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $_SESSION['signup_error'] = "An admin with this phone number or email already exists.";
        header("Location: signup.php");
        exit();
    }

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle profile picture upload
    $profile_picture = null; // Default to null if no file is uploaded

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $profile_picture = $_FILES['profile_picture']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['signup_error'] = "File is not an image.";
            header("Location: signup.php");
            exit();
        }

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $_SESSION['signup_error'] = "Sorry, your file is too large.";
            header("Location: signup.php");
            exit();
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['signup_error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: signup.php");
            exit();
        }

        // Upload file
        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $_SESSION['signup_error'] = "Sorry, there was an error uploading your file.";
            header("Location: signup.php");
            exit();
        }
    }

    // Insert new admin record into the database
    $sql = "INSERT INTO admins (name, phone, email, password, profile_picture) VALUES ('$name', '$phone', '$email', '$hashed_password', '$profile_picture')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['signup_success'] = "Admin registered successfully. You can now log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['signup_error'] = "Error: " . $sql . "<br>" . $conn->error;
        header("Location: signup.php");
        exit();
    }
}

$conn->close();
?>
