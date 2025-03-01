<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// Ensure a valid child ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}
$child_id = intval($_GET['id']);

// Fetch child details
$sql = "SELECT * FROM children WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$result = $stmt->get_result();
$child = $result->fetch_assoc();
$stmt->close();

if (!$child) {
    die("Child not found.");
}

// Fetch linked parents from member_children table
$parents = []; // Stored as ['Parent1' => [...], 'Parent2' => [...]]
$sql_parents = "
    SELECT m.id, m.first_name, m.last_name, m.phone, mc.parent_type
    FROM members m
    JOIN member_children mc ON m.id = mc.member_id
    WHERE mc.child_id = ?";
$stmt_parents = $conn->prepare($sql_parents);
$stmt_parents->bind_param("i", $child_id);
$stmt_parents->execute();
$result_parents = $stmt_parents->get_result();
while ($row = $result_parents->fetch_assoc()){
    $ptype = $row['parent_type'];
    $parents[$ptype] = $row;
}
$stmt_parents->close();
$conn->close();

// Determine parent values; if linked, use member info; otherwise, use stored manual fields.
$parent1_name = isset($parents['Parent1']) ? $parents['Parent1']['first_name'] . " " . $parents['Parent1']['last_name'] : ($child['parent1_name'] ?? '');
$parent1_phone = isset($parents['Parent1']) ? $parents['Parent1']['phone'] : ($child['parent1_phone'] ?? '');
$parent2_name = isset($parents['Parent2']) ? $parents['Parent2']['first_name'] . " " . $parents['Parent2']['last_name'] : ($child['parent2_name'] ?? '');
$parent2_phone = isset($parents['Parent2']) ? $parents['Parent2']['phone'] : ($child['parent2_phone'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Child Details</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4">Edit Child Details</h2>
    <form id="editChildForm" enctype="multipart/form-data">
      <!-- Hidden field for child ID -->
      <input type="hidden" name="child_id" value="<?php echo $child_id; ?>">
      
      <!-- Child Details -->
      <div class="mb-4">
         <label class="block text-gray-700">Child Name</label>
         <input type="text" name="name" value="<?php echo htmlspecialchars($child['name']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Date of Birth</label>
         <input type="date" name="dob" value="<?php echo htmlspecialchars($child['dob']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">School/Class/Stage</label>
         <input type="text" name="school" value="<?php echo htmlspecialchars($child['school']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Gender</label>
         <select name="gender" required class="w-full border p-2 rounded">
            <option value="Male" <?php if($child['gender'] == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($child['gender'] == 'Female') echo 'selected'; ?>>Female</option>
         </select>
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Residence</label>
         <input type="text" name="residence" value="<?php echo htmlspecialchars($child['residence']); ?>" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Phone (Optional)</label>
         <input type="text" name="phone" value="<?php echo htmlspecialchars($child['phone']); ?>" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Church Class</label>
         <select name="church_class" required class="w-full border p-2 rounded">
            <option value="Toddlers" <?php if($child['church_class'] == 'Toddlers') echo 'selected'; ?>>Toddlers</option>
            <option value="Intermediate I" <?php if($child['church_class'] == 'Intermediate I') echo 'selected'; ?>>Intermediate I</option>
            <option value="Intermediate II" <?php if($child['church_class'] == 'Intermediate II') echo 'selected'; ?>>Intermediate II</option>
            <option value="Intermediate III" <?php if($child['church_class'] == 'Intermediate III') echo 'selected'; ?>>Intermediate III</option>
            <option value="Teens" <?php if($child['church_class'] == 'Teens') echo 'selected'; ?>>Teens</option>
         </select>
      </div>
      
      <!-- Profile Picture -->
      <div class="mb-4">
         <label class="block text-gray-700">Profile Picture</label>
         <?php if (!empty($child['profile_picture'])): ?>
            <img src="../uploads/children/<?php echo htmlspecialchars($child['profile_picture']); ?>" alt="Profile Picture" class="w-24 h-24 rounded mb-2">
         <?php else: ?>
            <p>No image available.</p>
         <?php endif; ?>
         <input type="file" name="profile_picture" accept="image/*" class="w-full border p-2 rounded">
      </div>
      
      <!-- Parent 1 -->
      <div class="mb-4">
         <label class="block text-gray-700">Parent 1 Name</label>
         <input type="text" name="parent1_name" value="<?php echo htmlspecialchars($parent1_name); ?>" placeholder="Type parent's name" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Parent 1 Phone</label>
         <input type="text" name="parent1_phone" value="<?php echo htmlspecialchars($parent1_phone); ?>" placeholder="Enter phone" class="w-full border p-2 rounded">
      </div>
      
      <!-- Parent 2 -->
      <div class="mb-4">
         <label class="block text-gray-700">Parent 2 Name</label>
         <input type="text" name="parent2_name" value="<?php echo htmlspecialchars($parent2_name); ?>" placeholder="Type parent's name" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Parent 2 Phone</label>
         <input type="text" name="parent2_phone" value="<?php echo htmlspecialchars($parent2_phone); ?>" placeholder="Enter phone" class="w-full border p-2 rounded">
      </div>
      
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Child</button>
    </form>
  </div>
  
  <script>
    // Use AJAX to submit the edit form
    $('#editChildForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      $.ajax({
         url: 'update_child.php',
         type: 'POST',
         data: formData,
         contentType: false,
         processData: false,
         success: function(response) {
            console.log("AJAX success response:", response);
            alert(response.message);
            if (response.status === 'success') {
              window.location.href = 'view_children.php';
            }
         },
         error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error:", textStatus, errorThrown);
            alert('An error occurred during update.');
         }
      });
    });
    
    // Attach the AJAX submission to the form on document ready
    $(document).ready(function(){
        $('#editChildForm').on('submit', function(e){
            e.preventDefault();
            // (AJAX call already defined above)
        });
    });
  </script>
</body>
</html>
