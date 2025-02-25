<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Get the category name for the report header
    $sql_category = "SELECT name FROM payment_categories WHERE id = $category_id";
    $result_category = $conn->query($sql_category);
    $category_name = $result_category->fetch_assoc()['name'];

    $sql_payments = "SELECT * FROM payments WHERE category_id = $category_id AND payment_date BETWEEN '$start_date' AND '$end_date'";
    $result_payments = $conn->query($sql_payments);

    $payments = [];
    $total_amount = 0.00;
    if ($result_payments->num_rows > 0) {
        while ($row = $result_payments->fetch_assoc()) {
            $payments[] = $row;
            $total_amount += $row['amount'];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Report</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .print-button {
            margin-top: 20px;
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
    <header>
        <h1>Payment Report</h1>
        <h2>Category: <?php echo htmlspecialchars($category_name); ?></h2>
        <h3>Period: <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h3>
        
    </header>
    <main>
        <?php if (!empty($payments)) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Payer Name</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['payer_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payer_phone']); ?></td>
                            <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['description']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <h4>Total Amount: <?php echo number_format($total_amount, 2); ?></h4>
        <?php } else { ?>
            <p>No payments found for the selected period and category.</p>
        <?php } ?>
        <button class="print-button" onclick="printReport()">Print Report</button>
    </main>
</body>
</html>
