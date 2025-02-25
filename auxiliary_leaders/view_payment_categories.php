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

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM payment_categories WHERE id=?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    header("Location: view_payment_categories.php");
    exit();
}

$sql_categories = "SELECT * FROM payment_categories WHERE `for` = '$for'";
$result_categories = $conn->query($sql_categories);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Payment Categories</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <h1>View Payment Categories</h1>
    </header>
    <main>
        <h2>Existing Categories</h2>
        <ul>
            <?php while ($category = $result_categories->fetch_assoc()) { ?>
                <li>
                    <?php echo htmlspecialchars($category['name']) . " - " . htmlspecialchars($category['description']); ?>
                    <a href="view_payment_categories.php?delete_id=<?php echo $category['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                </li>
            <?php } ?>
        </ul>
    </main>
</body>
</html>
