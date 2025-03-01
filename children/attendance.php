<?php
session_start();
if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: teacher_login.php");
    exit();
}

include '../config.php';

// Validate class_id is provided
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    die("Invalid request: class_id is required.");
}
$class_id = intval($_GET['class_id']);

// Get the date from GET parameter or default to today's date
$date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Fetch church class details
$sql_class = "SELECT * FROM church_classes WHERE id = ?";
$stmt_class = $conn->prepare($sql_class);
$stmt_class->bind_param("i", $class_id);
$stmt_class->execute();
$result_class = $stmt_class->get_result();
$church_class = $result_class->fetch_assoc();
$stmt_class->close();

if (!$church_class) {
    die("Class not found.");
}

$class_name = $church_class['class_name'];

// Fetch children whose church_class matches the class name
$sql_children = "SELECT * FROM children WHERE church_class = ?";
$stmt_children = $conn->prepare($sql_children);
$stmt_children->bind_param("s", $class_name);
$stmt_children->execute();
$result_children = $stmt_children->get_result();
$children = [];
while ($row = $result_children->fetch_assoc()) {
    $children[] = $row;
}
$stmt_children->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Take Attendance - <?php echo htmlspecialchars($class_name); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Attendance for <?php echo htmlspecialchars($class_name); ?></h1>
      <a href="class_details.php?id=<?php echo $class_id; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back to Class Details</a>
    </div>
    
    <!-- Date Selection -->
    <div class="mb-4">
      <label for="attendance_date" class="block text-gray-700">Attendance Date:</label>
      <input type="date" id="attendance_date" name="attendance_date" value="<?php echo $date; ?>" class="mt-1 border p-2 rounded">
    </div>
    
    <!-- Attendance Form -->
    <form id="attendanceForm">
      <!-- Hidden fields -->
      <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
      <input type="hidden" name="attendance_date" id="hidden_attendance_date" value="<?php echo $date; ?>">
      
      <table class="min-w-full bg-white border">
         <thead>
            <tr class="bg-gray-200">
               <th class="py-2 px-4 border">Child ID</th>
               <th class="py-2 px-4 border">Name</th>
               <th class="py-2 px-4 border">Status</th>
            </tr>
         </thead>
         <tbody>
            <?php if (count($children) > 0): ?>
               <?php foreach ($children as $child): ?>
                  <tr class="text-center">
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['id']); ?></td>
                     <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['name']); ?></td>
                     <td class="py-2 px-4 border">
                        <label class="inline-flex items-center mr-4">
                           <input type="radio" name="attendance[<?php echo $child['id']; ?>]" value="Present" required class="form-radio text-green-500">
                           <span class="ml-1">Present</span>
                        </label>
                        <label class="inline-flex items-center">
                           <input type="radio" name="attendance[<?php echo $child['id']; ?>]" value="Absent" required class="form-radio text-red-500">
                           <span class="ml-1">Absent</span>
                        </label>
                     </td>
                  </tr>
               <?php endforeach; ?>
            <?php else: ?>
               <tr>
                  <td colspan="3" class="py-4 text-center">No children found for this class.</td>
               </tr>
            <?php endif; ?>
         </tbody>
      </table>
      
      <div class="mt-4">
         <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Submit Attendance</button>
      </div>
    </form>
    
    <div id="attendanceResponse" class="mt-4 text-center"></div>
  </div>
  
  <script>
    // Update hidden attendance_date when date input changes
    $('#attendance_date').on('change', function(){
      $('#hidden_attendance_date').val($(this).val());
    });
    
    // AJAX submission for attendance form
    $('#attendanceForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      $.ajax({
         url: 'update_attendance.php', // Endpoint for processing attendance updates
         type: 'POST',
         data: formData,
         contentType: false,
         processData: false,
         success: function(response) {
            console.log("Attendance response:", response);
            // Assuming response returns JSON { status: 'success', message: 'Attendance saved' }
            $('#attendanceResponse').text(response.message)
              .removeClass()
              .addClass(response.status === 'success' ? 'text-green-600' : 'text-red-600');
         },
         error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error:", textStatus, errorThrown);
            $('#attendanceResponse').text('An error occurred while saving attendance.')
              .removeClass()
              .addClass('text-red-600');
         }
      });
    });
  </script>
</body>
</html>
