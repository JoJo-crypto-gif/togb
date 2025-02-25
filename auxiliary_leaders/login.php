<?php
session_start();
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, auxiliary FROM auxiliary_leaders WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $leader = $result->fetch_assoc();
        $hashed_password = $leader['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['auxiliary_leader_logged_in'] = true;
            $_SESSION['leader_id'] = $leader['id'];
            $_SESSION['auxiliary'] = $leader['auxiliary'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Auxiliary Leader Login</title>
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
            transition: 1s ease-in-out;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }
        @media (min-width: 768px) {
            .login-image {
                display: block;
                width: 50%;
                background-image: url('../bg-images/curved-10.jpg');
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
            <h4 class="mb-3">Welcome back, Auxiliary Leader</h4>
            <p class="mb-4">Enter your username and password to sign in</p>
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
        <div class="login-image"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
