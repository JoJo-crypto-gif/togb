<!-- edit_member.php -->
<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM members WHERE id=$id";
    $result = $conn->query($sql);
    $member = $result->fetch_assoc();
}
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
    <title>Edit member details</title>

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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <h1 class="text-2xl font-semibold">Edit Member Details</h1>
        <div class="notifications">
            <a href="notifications.php" class="text-blue-500 hover:underline"><i class="fas fa-bell"></i> Notifications</a>
        </div>
    </div>
</header>

<div class="flex flex-1">
    <!-- Sidebar -->
    <nav class="sidebar w-64 bg-white h-screen shadow-md overflow-hidden relative">
        <div class="p-4">
            <ul class="mt-4">
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
                <li>
                    <a href="add_auxiliary_leader.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-user-tie"></i>
                        <span class="sidebar-text">Add Auxiliary Leader</span>
                    </a>
                </li>
                <li>
                    <a href="view_auxiliary_leaders.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-users-cog"></i>
                        <span class="sidebar-text">View Auxiliary Leaders</span>
                    </a>
                </li>
                <li>
                    <a href="view_church_workers.php" class="block py-2.5 px-4 rounded hover:bg-gray-200 hover-3d">
                        <i class="fas fa-church"></i>
                        <span class="sidebar-text">Manage Church Workers</span>
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
        <?php if (isset($member)): ?>
        <form id="edit-member-form" enctype="multipart/form-data" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-1">
            <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
            <label for="first_name" class="block text-gray-700">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $member['first_name']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">
            <label for="last_name" class="block text-gray-700">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $member['last_name']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">
            <label for="phone" class="block text-gray-700">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $member['phone']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">
        <label for="email" class="block text-gray-700">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $member['email']; ?>" class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
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
            <input type="text" id="residence" name="residence" value="<?php echo $member['residence']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">    
            <label for="dob" class="block text-gray-700">Date of Birth:</label>
            <input type="date" id="dob" name="dob" value="<?php echo $member['dob']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">    
            <label for="occupation" class="block text-gray-700">Occupation</label>
            <input type="text" id="occupation" name="occupation" value="<?php echo $member['occupation']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
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
            <input type="text" id="contact1_name" name="contact1_name" value="<?php echo $member['contact1_name']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">    
            <label for="contact1_phone" class="block text-gray-700">POC1 Phone:</label>
            <input type="text" id="contact1_phone" name="contact1_phone" value="<?php echo $member['contact1_phone']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">    
            <label for="contact1_relationship" class="block text-gray-700">POC1 Relationship:</label>
            <input type="text" id="contact1_relationship" name="contact1_relationship" value="<?php echo $member['contact1_relationship']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">    
            <label for="contact2_name" class="block text-gray-700">POC2 Name:</label>
            <input type="text" id="contact2_name" name="contact2_name" value="<?php echo $member['contact2_name']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-1">    
            <label for="contact2_phone" class="block text-gray-700">POC2 Phone:</label>
            <input type="text" id="contact2_phone" name="contact2_phone" value="<?php echo $member['contact2_phone']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2">    
            <label for="contact2_relationship" class="block text-gray-700">POC2 Relationship:</label>
            <input type="text" id="contact2_relationship" name="contact2_relationship" value="<?php echo $member['contact2_relationship']; ?>" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2 flex justify-center mt-5">
            <input type="submit" value="Update Member" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
        </div>
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
    });
    </script>
</body>
</html>
