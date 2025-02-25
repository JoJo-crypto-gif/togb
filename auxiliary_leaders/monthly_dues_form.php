<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Monthly Dues</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        function fetchMemberDetails() {
            const firstName = document.getElementById('first_name').value;
            const phone = document.getElementById('phone').value;

            if (firstName && phone) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_member.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('last_name').value = response.member.last_name;
                            document.getElementById('member_id').value = response.member.id;
                        } else {
                            alert('Member not found');
                        }
                    }
                };
                xhr.send('first_name=' + encodeURIComponent(firstName) + '&phone=' + encodeURIComponent(phone));
            } else {
                alert('Please enter both first name and phone number');
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Record Monthly Dues</h1>
    </header>
    <main>
        <form method="POST" action="submit_monthly_dues.php">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" required>

            <button type="button" onclick="fetchMemberDetails()">Fetch Member</button>
            
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required readonly>
            
            <input type="hidden" id="member_id" name="member_id" required>
            
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" required>
            
            <label for="payment_date">Payment Date:</label>
            <input type="date" id="payment_date" name="payment_date" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
            
            <button type="submit">Record Payment</button>
        </form>
    </main>
</body>
</html>
