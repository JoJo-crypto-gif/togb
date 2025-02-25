<?php
// Include the database connection
include 'config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone']; // Add phone field
    $created_at = date('Y-m-d H:i:s'); // Capture the creation time

    // Insert the new leader into the study_leader table
    $sql = "INSERT INTO study_leaders (first_name, last_name, username, password, email, phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("sssssss", $first_name, $last_name, $username, $hashed_password, $email, $phone, $created_at);

    if ($stmt->execute()) {
        // Leader creation was successful, send the email
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'jojoqazi44@gmail.com';               // SMTP username
            $mail->Password   = 'wzwfeuqpbcfvdtya';                        // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable SSL encryption
            $mail->Port       = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('josiahiscoding@gmail.com', 'TOGB Admin Team');
            $mail->addAddress($email, $first_name . ' ' . $last_name);  // Add recipient

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = 'Your Study Leader Account Details';
            $mail->Body    = "
                <h1>Welcome, {$first_name}!</h1>
                <p>Your study leader account has been created successfully. Here are your login details:</p>
                <p><strong>Username:</strong> {$username}</p>
                <p><strong>Password:</strong> {$password}</p>
                <p><strong>Please login here</strong> https://togbchurch.com/panel/study_leaders/leader_login.php</p>
                <p>Please keep this information safe and do not share it with anyone.</p>
                <p>Best regards,<br>Admin Team</p>
            ";

            // Send the email
            $mail->send();
            echo "Group leader created successfully! An email has been sent to their address.";
        } catch (Exception $e) {
            echo "Group leader created but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error creating leader: " . $stmt->error;
    }
}

// Fetch existing leaders
$sql_leaders = "SELECT * FROM study_leaders";
$result_leaders = $conn->query($sql_leaders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
    <title>Bible Study Leader</title>

    <style>
        .hover-3d:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .hover-3d {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .sidebar {
            transition: all 0.3s ease;
            height: 100vh; /* Stretch sidebar to fit the full screen height */
        }
        .sidebar-icon-only {
            width: 50px;
        }
        .dropdown {
            display: none;
            transition: all 0.4s ease;
        }
        .dropdown-expand {
            display: block;
            transition: all 1s ease-in-out;
        }
        .sidebar-icon-only .sidebar-text {
            display: none;
        }
        .sidebar-icon-only .dropdown-toggle::after {
            display: none;
        }
        th {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-bottom-width: 2px;
            border-color: #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
        td {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-bottom-width: 1px;
            border-color: #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <h1 class="text-2xl font-semibold">Add Bible Study Leader</h1>
        <div class="notifications">
            <a href="notifications.php" class="text-blue-500 hover:underline"><i class="fas fa-bell"></i> Notifications</a>
        </div>
    </div>
</header>

<div class="flex flex-1">
    <!-- Sidebar -->
    <nav class="sidebar w-64 bg-white h-auto shadow-md overflow-hidden relative">
        <div class="p-4">
            <ul class="mt-4">
            <li>
                    <a href="index.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-home"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="create_group.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-list-ul"></i>
                        <span class="sidebar-text">Manage Study Groups</span>
                    </a>
                </li>
                <li>
                    <a href="manage_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-tie"></i>
                        <span class="sidebar-text">Manage Study Leader</span>
                    </a>
                </li>
                <li>
                            <a href="view_study_groups.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                                <i class="fas fa-tasks"></i>
                                <span class="sidebar-text">Attendance</span>
                            </a>
                        </li>
                <li>
                    <a href="logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="flex-1 p-6 bg-white">
    <form method="POST" action="" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
    <div class="col-span-1">
        <label for="first_name" class="block text-gray-700">First Name:</label>
        <input type="text" id="first_name" name="first_name" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-1">
        <label for="last_name" class="block text-gray-700">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-1">
        <label for="username" class="block text-gray-700">Username:</label>
        <input type="text" id="username" name="username" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-1">
        <label for="email" class="block text-gray-700">Email:</label>
        <input type="email" id="email" name="email" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-1">
        <label for="password" class="block text-gray-700">Password:</label>
        <input type="password" id="password" name="password" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-1">
        <label for="phone" class="block text-gray-700">Phone:</label>
        <input type="text" id="phone" name="phone" required class="w-full p-2 border border-gray-300 rounded mt-1">
    </div>

    <div class="col-span-2 flex justify-center">
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">Create Leader</button>
    </div>
    </div>
    </form>

    <h2 class="text-xl font-bold mb-4">Existing Group Leaders</h2>
    <div class="bg-white shadow-md rounded p-4">
    <table class='bg-white shadow-md w-full'>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>
        <?php
        if ($result_leaders->num_rows > 0) {
            while ($row = $result_leaders->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['first_name'] . "</td>";
                echo "<td>" . $row['last_name'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['phone'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No leaders found</td></tr>";
        }
        ?>
    </table>
    </div>
    </main>
</body>
</html>
