<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$member_id = isset($_GET['member_id']) ? $_GET['member_id'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Modified SQL to join payment_categories_admin to get category names
$sql = "SELECT p.id, p.category_id, p.payment_date, m.first_name, m.last_name, m.phone, c.name AS category_name 
        FROM payment_admin p 
        JOIN members m ON p.member_id = m.id 
        JOIN categories_admin c ON p.category_id = c.id
        WHERE p.payment_date BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = 'ss';

if ($member_id != '') {
    $sql .= " AND p.member_id = ?";
    $params[] = $member_id;
    $types .= 'i';
}

if ($category_id != '') {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <title>payment report</title>

    <style>
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

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
<div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4 no-print">
            <button onclick="window.location.href='generate_report_admin_form.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">&larr; Back</button>
            <button onclick="window.print()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Print Page</button>
        </div>
<div class="bg-white shadow-md rounded-lg p-6">
    <header>
        <h1 class="text-2xl font-bold text-center mb-6">Payment Report</h1>
        <h2 class="text-2xl font-bold text-center mb-6">Category: <?php echo $category_id ? htmlspecialchars($category_id) : 'All'; ?></h2>
        <h3 class="text-2xl font-bold text-center mb-6">Period: <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h3>
    </header>
    <main>
        <table class='bg-white shadow-md w-full'>
            <thead>
                <tr>
                    
                    <th>Category</th>
                    <th>Payment Date</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="6">No records found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
