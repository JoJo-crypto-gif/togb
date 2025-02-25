<?php
// add_child.php – Form to add a child with parent details (including profile picture)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include '../config.php';  // Adjust path as needed

// Fetch member full names for autocomplete suggestions
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS fullname FROM members ORDER BY first_name ASC";
$result = $conn->query($sql);
$memberNames = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $memberNames[] = $row['fullname'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Child</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4">Add Child</h2>
    <form id="addChildForm" enctype="multipart/form-data">
      <!-- Child Details -->
      <div class="mb-4">
         <label class="block text-gray-700">Child Name</label>
         <input type="text" name="name" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Date of Birth</label>
         <input type="date" name="dob" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">School/Class/Stage</label>
         <input type="text" name="school" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Gender</label>
         <select name="gender" required class="w-full border p-2 rounded">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
         </select>
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Residence</label>
         <input type="text" name="residence" required class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Phone (Optional)</label>
         <input type="text" name="phone" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Church Class</label>
         <select name="church_class" required class="w-full border p-2 rounded">
            <option value="Toddlers">Toddlers</option>
            <option value="Intermediate I">Intermediate I</option>
            <option value="Intermediate II">Intermediate II</option>
            <option value="Intermediate III">Intermediate III</option>
            <option value="Teens">Teens</option>
         </select>
      </div>
      
      <!-- Child Profile Picture -->
      <div class="mb-4">
         <label class="block text-gray-700">Profile Picture</label>
         <input type="file" name="profile_picture" accept="image/*" class="w-full border p-2 rounded">
      </div>
      
      <!-- Parent 1 -->
      <div class="mb-4">
         <label class="block text-gray-700">Parent 1 Name</label>
         <input list="membersList" name="parent1_name" id="parent1_name" placeholder="Type parent's name" class="w-full border p-2 rounded">
         <datalist id="membersList">
           <?php foreach ($memberNames as $name): ?>
             <option value="<?php echo htmlspecialchars($name); ?>">
           <?php endforeach; ?>
         </datalist>
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Parent 1 Phone</label>
         <input type="text" name="parent1_phone" id="parent1_phone" class="w-full border p-2 rounded" placeholder="Enter phone if needed">
      </div>

      <!-- Parent 2 -->
      <div class="mb-4">
         <label class="block text-gray-700">Parent 2 Name</label>
         <input list="membersList" name="parent2_name" id="parent2_name" placeholder="Type parent's name" class="w-full border p-2 rounded">
      </div>
      <div class="mb-4">
         <label class="block text-gray-700">Parent 2 Phone</label>
         <input type="text" name="parent2_phone" id="parent2_phone" class="w-full border p-2 rounded" placeholder="Enter phone if needed">
      </div>
      
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add Child</button>
    </form>
    <p id="childResponse" class="mt-4 text-center"></p>
  </div>
  
  <script>
    $('#addChildForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      $.ajax({
         url: 'insert_child.php',
         type: 'POST',
         data: formData,
         contentType: false,
         processData: false,
         success: function(response) {
            console.log("AJAX success response:", response);
            $('#childResponse').text(response.message)
              .removeClass()
              .addClass(response.status === 'success' ? 'text-green-600' : 'text-red-600');
            if (response.status === 'success') {
              $('#addChildForm')[0].reset();
            }
         },
         error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error:", textStatus, errorThrown);
            $('#childResponse').text('An error occurred.')
              .removeClass()
              .addClass('text-red-600');
         }
      });
    });
  </script>
</body>
</html>
