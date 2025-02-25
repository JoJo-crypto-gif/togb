<?php
// view_children.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../config.php';

// --- Build the WHERE clause based on GET parameters ---

$where = "1=1";

// Search by child name (partial match)
if (isset($_GET['name']) && $_GET['name'] !== '') {
    $name = $conn->real_escape_string($_GET['name']);
    $where .= " AND name LIKE '%$name%'";
}

// Filter by church class
if (isset($_GET['church_class']) && $_GET['church_class'] !== '') {
    $church_class = $conn->real_escape_string($_GET['church_class']);
    $where .= " AND church_class = '$church_class'";
}

// Filter by gender
if (isset($_GET['gender']) && $_GET['gender'] !== '') {
    $gender = $conn->real_escape_string($_GET['gender']);
    $where .= " AND gender = '$gender'";
}

// --- Pagination Setup ---
$limit = 30;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination
$sql_count = "SELECT COUNT(*) as total FROM children WHERE $where";
$result_count = $conn->query($sql_count);
$total = 0;
if ($result_count && $row = $result_count->fetch_assoc()) {
    $total = (int)$row['total'];
}
$total_pages = ceil($total / $limit);

// --- Retrieve children with filters and pagination ---
$sql = "SELECT * FROM children WHERE $where ORDER BY created_at DESC LIMIT $offset, $limit";
$result = $conn->query($sql);
$children = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
}
$conn->close();

// Function to build query string for links (preserving filters)
function buildQueryString($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Helper function for pagination navigation display
function paginationLinks($current, $total) {
    $html = '<div class="flex justify-center space-x-2 mt-4">';
    
    // Show "Previous" link if not on first page
    if ($current > 1) {
        $html .= '<a href="'.buildQueryString($current - 1).'" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>';
    }
    
    // We'll show a few pages around the current page plus first and last page.
    $range = 2; // number of pages to show before/after current page
    $start = max(1, $current - $range);
    $end   = min($total, $current + $range);
    
    // If start is greater than 1, show first page and dots
    if ($start > 1) {
        $html .= '<a href="'.buildQueryString(1).'" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">1</a>';
        if ($start > 2) {
            $html .= '<span class="px-2">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current) {
            $html .= '<span class="px-3 py-1 bg-blue-500 text-white rounded">' . $i . '</span>';
        } else {
            $html .= '<a href="'.buildQueryString($i).'" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">' . $i . '</a>';
        }
    }
    
    // If end is less than total, show dots and last page
    if ($end < $total) {
        if ($end < $total - 1) {
            $html .= '<span class="px-2">...</span>';
        }
        $html .= '<a href="'.buildQueryString($total).'" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">' . $total . '</a>';
    }
    
    // Show "Next" link if not on last page
    if ($current < $total) {
        $html .= '<a href="'.buildQueryString($current + 1).'" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>';
    }
    
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Children</title>
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
   <div class="container mx-auto p-4">
      <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
         <h1 class="text-2xl font-bold mb-4 sm:mb-0">Children List</h1>
         <a href="dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back to Dashboard</a>
      </div>
      
      <!-- Search/Filter Form -->
      <form method="GET" class="bg-white p-4 rounded shadow mb-6">
         <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
               <label class="block text-sm font-medium text-gray-700">Search Name</label>
               <input type="text" name="name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>" class="mt-1 block w-full border rounded p-2" placeholder="Child name">
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700">Church Class</label>
               <select name="church_class" class="mt-1 block w-full border rounded p-2">
                  <option value="">All</option>
                  <option value="Toddlers" <?php if(isset($_GET['church_class']) && $_GET['church_class'] == "Toddlers") echo 'selected'; ?>>Toddlers</option>
                  <option value="Intermediate I" <?php if(isset($_GET['church_class']) && $_GET['church_class'] == "Intermediate I") echo 'selected'; ?>>Intermediate I</option>
                  <option value="Intermediate II" <?php if(isset($_GET['church_class']) && $_GET['church_class'] == "Intermediate II") echo 'selected'; ?>>Intermediate II</option>
                  <option value="Intermediate III" <?php if(isset($_GET['church_class']) && $_GET['church_class'] == "Intermediate III") echo 'selected'; ?>>Intermediate III</option>
                  <option value="Teens" <?php if(isset($_GET['church_class']) && $_GET['church_class'] == "Teens") echo 'selected'; ?>>Teens</option>
               </select>
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700">Gender</label>
               <select name="gender" class="mt-1 block w-full border rounded p-2">
                  <option value="">All</option>
                  <option value="Male" <?php if(isset($_GET['gender']) && $_GET['gender'] == "Male") echo 'selected'; ?>>Male</option>
                  <option value="Female" <?php if(isset($_GET['gender']) && $_GET['gender'] == "Female") echo 'selected'; ?>>Female</option>
               </select>
            </div>
         </div>
         <div class="mt-4">
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Search</button>
         </div>
      </form>
      
      <!-- Children Table -->
      <div class="overflow-x-auto">
         <table class="min-w-full bg-white border">
            <thead>
               <tr>
                  <th class="py-2 px-4 border">ID</th>
                  <th class="py-2 px-4 border">Name</th>
                  <th class="py-2 px-4 border">DOB</th>
                  <th class="py-2 px-4 border">Church Class</th>
                  <th class="py-2 px-4 border">Phone</th>
                  <th class="py-2 px-4 border">Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php if(count($children) > 0): ?>
                  <?php foreach($children as $child): ?>
                     <tr class="text-center">
                        <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['id']); ?></td>
                        <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['name']); ?></td>
                        <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['dob']); ?></td>
                        <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['church_class']); ?></td>
                        <td class="py-2 px-4 border"><?php echo htmlspecialchars($child['phone']); ?></td>
                        <td class="py-2 px-4 border">
                           <a href="view_child.php?id=<?php echo $child['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">View</a>
                           <a href="edit_child.php?id=<?php echo $child['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded ml-2">Edit</a>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               <?php else: ?>
                  <tr>
                     <td colspan="6" class="py-4 text-center">No children found.</td>
                  </tr>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
      
      <!-- Pagination Navigation -->
      <?php if ($total_pages > 1): ?>
         <?php echo paginationLinks($page, $total_pages); ?>
      <?php endif; ?>
   </div>
</body>
</html>
