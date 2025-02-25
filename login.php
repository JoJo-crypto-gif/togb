<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE phone='$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: index.php");
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "No admin found with that phone number.";
    }
}

$message = "";
if (isset($_GET['message'])) {
    if ($_GET['message'] == 'password_changed') {
        $message = "Password changed successfully. Please log in with your new password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css"> <!-- Custom CSS for additional styling -->
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">

    <title>Admin Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .login-container {
            display: flex;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-form {
            padding: 30px;
            width: 100%;
        }
        .login-form h4 {
            margin-top: 0;
            color: #007bff;
        }
        .login-image {
            display: none;
        }
        button:hover{
            transform: scale(1.1);
            transition: .5s ease-in-out;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }
        @media (min-width: 768px) {
            .login-image {
                display: block;
                width: 50%;
                background-image: url('./bg-images/curved-10.jpg');
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;
            }
            .login-form {
                width: 50%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h4 class="mb-3">Welcome back, Admin</h4>
            <p class="mb-4">Enter your phone and password to sign in</p>
            <?php if (!empty($message)) : ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form id="login-form" action="login.php" method="post">
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember-me">
                    <label class="form-check-label" for="remember-me">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                <p class="mt-3"><a href="forgot_password.php">Forgot password?</a></p>
                <p class="mt-2">Are you an Auxiliary Leader? <a href="./auxiliary_leaders/login.php">Login in here</a></p>
                <p class="mt-2">Are you a study group leader? <a href="./study_leaders/leader_login.php">Login in here</a></p>
            </form>
        </div>
        <div class="login-image" style="background-image: url('./bg-images/curved-10.jpg');"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
