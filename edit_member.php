<?php
// edit_member.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM members WHERE id=$id";
    $result = $conn->query($sql);
    $member = $result->fetch_assoc();
    
    // Fetch children linked to this member (using member_children linking table)
    $sql_children = "SELECT c.* FROM children c 
                     JOIN member_children mc ON c.id = mc.child_id 
                     WHERE mc.member_id = $id";
    $result_children = $conn->query($sql_children);
    $children = [];
    if ($result_children) {
        while ($row = $result_children->fetch_assoc()) {
            $children[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="img/OIP.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            height: 100vh;
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Edit Member Details</h1>
        <div class="notifications">
            <a href="notifications.php" class="text-blue-500 hover:underline">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </div>
    </div>
</header>

<div class="flex flex-1">
    <!-- Sidebar -->
    <nav class="sidebar w-64 bg-white h-screen shadow-md overflow-hidden relative">
        <div class="p-4">
            <ul class="mt-4">
                <!-- Sidebar links -->
                <li>
                    <a href="index.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-home"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="view_members.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-users"></i>
                        <span class="sidebar-text">View Members</span>
                    </a>
                </li>
                <li>
                    <a href="add_member.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-plus"></i>
                        <span class="sidebar-text">Add Member</span>
                    </a>
                </li>
                <!-- ... additional sidebar links ... -->
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
        <?php if (isset($member)): ?>
        <form id="edit-member-form" enctype="multipart/form-data" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Member Fields -->
                <div class="col-span-1">
                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                    <label for="first_name" class="block text-gray-700">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">
                    <label for="last_name" class="block text-gray-700">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">
                    <label for="phone" class="block text-gray-700">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-2">
                    <label for="profile_picture" class="block text-gray-700">Profile Photo:</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d" accept="image/*">
                </div>
                <div class="col-span-1">
                    <label for="auxiliary" class="block text-gray-700">Auxiliary:</label>
                    <select id="auxiliary" name="auxiliary" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                        <option value="men" <?php if ($member['auxiliary'] == 'men') echo 'selected'; ?>>Men's</option>
                        <option value="women" <?php if ($member['auxiliary'] == 'women') echo 'selected'; ?>>Women's</option>
                        <option value="youth" <?php if ($member['auxiliary'] == 'youth') echo 'selected'; ?>>Youth</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="residence" class="block text-gray-700">Place of Residence:</label>
                    <input type="text" id="residence" name="residence" value="<?php echo htmlspecialchars($member['residence']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">    
                    <label for="dob" class="block text-gray-700">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($member['dob']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">    
                    <label for="occupation" class="block text-gray-700">Occupation</label>
                    <input type="text" id="occupation" name="occupation" value="<?php echo htmlspecialchars($member['occupation']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">
                    <label for="gender" class="block text-gray-700">Gender:</label>
                    <select id="gender" name="gender" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                        <option value="male" <?php if ($member['gender'] == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if ($member['gender'] == 'female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="marital_status" class="block text-gray-700">Marital Status:</label>
                    <select id="marital_status" name="marital_status" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                        <option value="single" <?php if ($member['marital_status'] == 'single') echo 'selected'; ?>>Single</option>
                        <option value="married" <?php if ($member['marital_status'] == 'married') echo 'selected'; ?>>Married</option>
                        <option value="divorced" <?php if ($member['marital_status'] == 'divorced') echo 'selected'; ?>>Divorced</option>
                        <option value="widowed" <?php if ($member['marital_status'] == 'widowed') echo 'selected'; ?>>Widowed</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="active_status" class="block text-gray-700">Active Status:</label>
                    <label for="active_yes" class="inline-flex items-center mt-1 hover-3d"> 
                        <input type="radio" id="active_yes" name="active_status" value="yes" <?php if ($member['active_status'] == 'yes') echo 'checked'; ?> class="form-radio text-blue-600">
                        <span class="ml-2">Yes</span>
                    </label>
                    <label for="active_no" class="inline-flex items-center mt-1 ml-6 hover-3d">
                        <input type="radio" id="active_no" name="active_status" value="no" <?php if ($member['active_status'] == 'no') echo 'checked'; ?> class="form-radio text-blue-600">
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <div class="col-span-1">
                    <label for="baptism_status" class="block text-gray-700">Baptism Status:</label>
                    <label for="baptism_yes" class="inline-flex items-center mt-1 hover-3d">
                        <input type="radio" id="baptism_yes" name="baptism_status" value="yes" <?php if ($member['baptism_status'] == 'yes') echo 'checked'; ?> class="form-radio text-blue-600">
                        <span class="ml-2">Yes</span>
                    </label>
                    <label for="baptism_no" class="inline-flex items-center mt-1 ml-6 hover-3d">
                        <input type="radio" id="baptism_no" name="baptism_status" value="no" <?php if ($member['baptism_status'] == 'no') echo 'checked'; ?> class="form-radio text-blue-600">
                        <span class="ml-2">No</span>
                    </label>
                </div>
                <div class="col-span-1">    
                    <label for="contact1_name" class="block text-gray-700">POC1 Name:</label>
                    <input type="text" id="contact1_name" name="contact1_name" value="<?php echo htmlspecialchars($member['contact1_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">    
                    <label for="contact1_phone" class="block text-gray-700">POC1 Phone:</label>
                    <input type="text" id="contact1_phone" name="contact1_phone" value="<?php echo htmlspecialchars($member['contact1_phone']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-2">    
                    <label for="contact1_relationship" class="block text-gray-700">POC1 Relationship:</label>
                    <input type="text" id="contact1_relationship" name="contact1_relationship" value="<?php echo htmlspecialchars($member['contact1_relationship']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">    
                    <label for="contact2_name" class="block text-gray-700">POC2 Name:</label>
                    <input type="text" id="contact2_name" name="contact2_name" value="<?php echo htmlspecialchars($member['contact2_name']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-1">    
                    <label for="contact2_phone" class="block text-gray-700">POC2 Phone:</label>
                    <input type="text" id="contact2_phone" name="contact2_phone" value="<?php echo htmlspecialchars($member['contact2_phone']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
                <div class="col-span-2">    
                    <label for="contact2_relationship" class="block text-gray-700">POC2 Relationship:</label>
                    <input type="text" id="contact2_relationship" name="contact2_relationship" value="<?php echo htmlspecialchars($member['contact2_relationship']); ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
                </div>
            </div>
            
            <!-- Children Section -->
            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4">Children Information</h2>
                <div id="children-container">
                    <?php if(isset($children) && count($children) > 0): ?>
                        <?php foreach($children as $child): ?>
                            <div class="child-entry mb-4 p-4 bg-gray-50 border rounded">
                                <input type="hidden" name="children[<?php echo $child['id']; ?>][id]" value="<?php echo $child['id']; ?>">
                                <div class="mb-2">
                                    <label class="block text-gray-700">Child Name:</label>
                                    <input type="text" name="children[<?php echo $child['id']; ?>][name]" value="<?php echo htmlspecialchars($child['name']); ?>" required class="w-full p-2 border rounded">
                                </div>
                                <div class="mb-2 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700">Date of Birth:</label>
                                        <input type="date" name="children[<?php echo $child['id']; ?>][dob]" value="<?php echo htmlspecialchars($child['dob']); ?>" required class="w-full p-2 border rounded">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700">School/Class/Stage:</label>
                                        <input type="text" name="children[<?php echo $child['id']; ?>][school]" value="<?php echo htmlspecialchars($child['school']); ?>" required class="w-full p-2 border rounded">
                                    </div>
                                </div>
                                <div class="mb-2 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700">Gender:</label>
                                        <select name="children[<?php echo $child['id']; ?>][gender]" required class="w-full p-2 border rounded">
                                            <option value="Male" <?php if($child['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                            <option value="Female" <?php if($child['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700">Residence:</label>
                                        <input type="text" name="children[<?php echo $child['id']; ?>][residence]" value="<?php echo htmlspecialchars($child['residence']); ?>" required class="w-full p-2 border rounded">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="block text-gray-700">Phone (Optional):</label>
                                    <input type="text" name="children[<?php echo $child['id']; ?>][phone]" value="<?php echo htmlspecialchars($child['phone']); ?>" class="w-full p-2 border rounded">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No children linked to this member.</p>
                    <?php endif; ?>
                </div>
                <!-- Button to add a new child entry dynamically -->
                <button type="button" id="addChildBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Add Child</button>
            </div>
            
            <div class="col-span-2 flex justify-center mt-5">
                <input type="submit" value="Update Member" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
            </div>
        </form>
        <?php else: ?>
            <p>Member not found.</p>
        <?php endif; ?>
    </main>
    
    <script>
        $(document).ready(function() {
            $('#edit-member-form').submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'update_member.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var res = JSON.parse(response);
                        alert(res.message);
                        if (res.status === 'success') {
                            window.location.href = 'view_members.php';
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the member.');
                    }
                });
            });
            
            // Dynamically add a new child entry when "Add Child" is clicked
            $('#addChildBtn').click(function() {
                var childCount = $('#children-container .child-entry').length;
                var newId = 'new_' + (childCount + 1); // temporary unique identifier for new child entries
                var childHTML = `
                    <div class="child-entry mb-4 p-4 bg-gray-50 border rounded">
                        <input type="hidden" name="children[${newId}][id]" value="">
                        <div class="mb-2">
                            <label class="block text-gray-700">Child Name:</label>
                            <input type="text" name="children[${newId}][name]" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700">Date of Birth:</label>
                                <input type="date" name="children[${newId}][dob]" required class="w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-gray-700">School/Class/Stage:</label>
                                <input type="text" name="children[${newId}][school]" required class="w-full p-2 border rounded">
                            </div>
                        </div>
                        <div class="mb-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700">Gender:</label>
                                <select name="children[${newId}][gender]" required class="w-full p-2 border rounded">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700">Residence:</label>
                                <input type="text" name="children[${newId}][residence]" required class="w-full p-2 border rounded">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700">Phone (Optional):</label>
                            <input type="text" name="children[${newId}][phone]" class="w-full p-2 border rounded">
                        </div>
                    </div>
                `;
                $('#children-container').append(childHTML);
            });
        });
    </script>
</body>
</html>
