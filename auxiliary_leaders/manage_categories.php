<?php
session_start();
if (!isset($_SESSION['auxiliary_leader_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

$auxiliary_leader_id = $_SESSION['leader_id'];
$sql_leader = "SELECT * FROM auxiliary_leaders WHERE id='$auxiliary_leader_id'";
$result_leader = $conn->query($sql_leader);
$leader = $result_leader->fetch_assoc();
$for = $leader['auxiliary']; // Use the auxiliary leader's group as the `for` value

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $sql = "INSERT INTO payment_categories (name, description, `for`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("sss", $name, $description, $for);
    if ($stmt->execute() === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();
}

$sql_categories = "SELECT * FROM payment_categories WHERE `for` = '$for'";
$result_categories = $conn->query($sql_categories);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Payment Categories</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>Manage Payment Categories</h1>
    </header>
    <main>
        <form method="POST" action="manage_categories.php">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
            <button type="submit">Add Category</button>
        </form>
        <h2>Existing Categories</h2>
        <ul>
            <?php while ($category = $result_categories->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($category['name']) . " - " . htmlspecialchars($category['description']) . "</li>";
            } ?>
        </ul>
    </main>
</body>
</html>
