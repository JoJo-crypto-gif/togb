<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];

    $sql = "SELECT * FROM admins WHERE phone='$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $reset_token = bin2hex(random_bytes(16));
        $reset_requested_at = date('Y-m-d H:i:s');

        $sql = "UPDATE admins SET reset_token='$reset_token', reset_requested_at='$reset_requested_at' WHERE phone='$phone'";
        if ($conn->query($sql) === TRUE) {
            // Send reset link via email
            $reset_link = "http://localhost/TOBGv0/reset_password.php?token=$reset_token";
            $to = $admin['email'];
            $subject = "Password Reset";
            $message = "Click the link to reset your password: $reset_link";

            // Gmail SMTP configuration
            $smtpHost = 'smtp.gmail.com';
            $smtpUsername = 'jojoqazi44@gmail.com'; // Your Gmail email address
            $smtpPassword = 'wzwfeuqpbcfvdtya'; // Your generated app password
            $smtpPort = 587; // SMTP port (TLS)

            // PHPMailer configuration (you'll need to download and include PHPMailer library)
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
            require 'PHPMailer/src/Exception.php';

            // Create PHPMailer object
            $mail = new PHPMailer\PHPMailer\PHPMailer();

            // Enable verbose debug output
            $mail->SMTPDebug = 2; // Set to 0 for production
            $mail->Debugoutput = 'html';

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = $smtpPort;

            // Email content
            $mail->setFrom($smtpUsername); // Sender email address
            $mail->addAddress($to); // Recipient email address
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $message;

            // Send email
            if ($mail->send()) {
                // Alert notification
                echo '<script>alert("Reset link sent successfully.");</script>';
            } else {
                // Alert notification with error message
                echo '<script>alert("Failed to send reset link. Please try again later. Error: ' . $mail->ErrorInfo . '");</script>';
            }
        } else {
            // Alert notification with error message
            echo '<script>alert("Error updating reset token. Please try again later.");</script>';
        }
    } else {
        // Alert notification for no admin found
        echo '<script>alert("No admin found with that phone number.");</script>';
    }
}

$conn->close();
?>
