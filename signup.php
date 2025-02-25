<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Admin Signup</title>
</head>
<body>
    <header>
        <h1>Admin Signup</h1>
    </header>
    <main>
        <?php
        session_start();
        if (isset($_SESSION['signup_error'])) {
            echo "<p class='error'>" . $_SESSION['signup_error'] . "</p>";
            unset($_SESSION['signup_error']);
        }
        ?>
        <form id="signup-form" action="register_admin.php" method="post" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*"><br>
            <input type="submit" value="Sign Up">
        </form>
    </main>
    <footer>
        <p>&copy; 2024 Church Management System</p>
    </footer>
</body>
</html>
