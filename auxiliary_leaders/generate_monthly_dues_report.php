<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "SELECT * FROM monthly_dues WHERE payment_date BETWEEN '$start_date' AND '$end_date'";
    $result = $conn->query($sql);

    $monthly_dues = [];
    $total_amount = 0.00;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthly_dues[] = $row;
            $total_amount += $row['amount'];
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Dues Report</title>
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
        <h1>Monthly Dues Report</h1>
        <h2>Period: <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h2>
        <h4>Total Amount: <?php echo number_format($total_amount, 2); ?></h4>
    </header>
    <main>
        <?php if (!empty($monthly_dues)) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_dues as $due) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($due['member_id']); ?></td>
                            <td><?php echo htmlspecialchars($due['amount']); ?></td>
                            <td><?php echo htmlspecialchars($due['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($due['description']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No monthly dues found for the selected period.</p>
        <?php } ?>
        <button class="print-button" onclick="printReport()">Print Report</button>
    </main>
</body>
</html>
