<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the member record
    $sql = "SELECT * FROM members WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
    } else {
        echo "<script>alert('Member not found.'); window.location.href='view_members.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='view_members.php';</script>";
    exit();
}

// Now, fetch the children associated with this member
$children = [];
$sqlChildren = "SELECT c.* 
                FROM children c 
                INNER JOIN member_children mc ON c.id = mc.child_id 
                WHERE mc.member_id = '$id'";
$resultChildren = $conn->query($sqlChildren);
if ($resultChildren && $resultChildren->num_rows > 0) {
    while($row = $resultChildren->fetch_assoc()){
        $children[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <title>View Member Details</title>
    <style>
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
            <button onclick="window.location.href='view_members.php'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">&larr; Back</button>
            <button onclick="window.print()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Print Page</button>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-center mb-6">Member Details</h1>
            <div class="space-y-4">
                <div class="text-center">
                    <?php if (!empty($member['profile_picture'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" class="rounded-full mx-auto w-24 h-24 object-cover">
                    <?php else: ?>
                        <div class="rounded-full mx-auto w-24 h-24 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['first_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['last_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($member['email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Occupation</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['occupation']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Auxiliary</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['auxiliary']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Residence</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['residence']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['dob']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['gender']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marital Status</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['marital_status']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Active Status</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['active_status']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Baptism Status</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['baptism_status']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 1 Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact1_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 1 Phone</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact1_phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 1 Relationship</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact1_relationship']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 2 Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact2_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 2 Phone</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact2_phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Point Of Contact 2 Relationship</label>
                        <input type="text" value="<?php echo htmlspecialchars($member['contact2_relationship']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                </div>
            </div>
            
            <!-- Children Section -->
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Children</h2>
                <?php if (!empty($children)): ?>
                    <div class="space-y-4">
                        <?php foreach ($children as $child): ?>
                            <div class="p-4 border rounded-md bg-gray-50">
                                <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($child['name']); ?></h3>
                                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($child['dob']); ?></p>
                                <p><strong>School/Class/Stage:</strong> <?php echo htmlspecialchars($child['school']); ?></p>
                                <p><strong>Gender:</strong> <?php echo htmlspecialchars($child['gender']); ?></p>
                                <p><strong>Residence:</strong> <?php echo htmlspecialchars($child['residence']); ?></p>
                                <?php if(!empty($child['phone'])): ?>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($child['phone']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No children linked to this member.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
