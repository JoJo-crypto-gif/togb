<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Member</title>
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link rel="icon" href="img/OIP.ico" type="image/x-icon">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .hover-3d:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .hover-3d {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- Header -->
  <header class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-2xl font-semibold">Add New Member</h1>
      <div class="notifications">
        <a href="notifications.php" class="text-blue-500 hover:underline"><i class="fas fa-bell"></i> Notifications</a>
      </div>
    </div>
  </header>

  <div class="flex flex-1">
    <!-- Sidebar -->
    <nav class="w-64 bg-white h-screen shadow-md p-4">
      <ul class="mt-4">
        <li class="mb-2">
          <a href="index.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="mb-2">
          <a href="view_members.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-users"></i> View Members</a>
        </li>
        <li class="mb-2">
          <a href="add_member.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-user-plus"></i> Add Member</a>
        </li>
        <li class="mb-2">
          <a href="add_auxiliary_leader.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-user-tie"></i> Add Auxiliary Leader</a>
        </li>
        <li class="mb-2">
          <a href="view_auxiliary_leaders.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-users-cog"></i> View Auxiliary Leaders</a>
        </li>
        <li class="mb-2">
          <a href="view_church_workers.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-church"></i> Manage Church Workers</a>
        </li>
        <li class="mb-2">
          <a href="logout.php" class="block py-2 px-4 rounded hover:bg-gray-200 hover-3d"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6 bg-white">
      <form id="addMemberForm" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Member Details -->
          <div class="col-span-1">
            <label class="block text-gray-700">First Name:</label>
            <input type="text" name="first_name" required class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Last Name:</label>
            <input type="text" name="last_name" required class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Phone:</label>
            <input type="text" name="phone" required class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Email:</label>
            <input type="email" name="email" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Auxiliary:</label>
            <select name="auxiliary" required class="w-full p-2 border rounded hover-3d">
              <option value="men">Men's</option>
              <option value="women">Women's</option>
              <option value="youth">Youth</option>
            </select>
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Residence:</label>
            <input type="text" name="residence" required class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Date of Birth:</label>
            <input type="date" name="dob" required class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Gender:</label>
            <select name="gender" required class="w-full p-2 border rounded hover-3d">
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Marital Status:</label>
            <select name="marital_status" required class="w-full p-2 border rounded hover-3d">
              <option value="single">Single</option>
              <option value="married">Married</option>
              <option value="divorced">Divorced</option>
              <option value="widowed">Widowed</option>
            </select>
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Active Status:</label>
            <select name="active_status" required class="w-full p-2 border rounded hover-3d">
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Baptism Status:</label>
            <select name="baptism_status" required class="w-full p-2 border rounded hover-3d">
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-gray-700">Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-2">
            <label class="block text-gray-700">Occupation:</label>
            <input type="text" name="occupation" class="w-full p-2 border rounded hover-3d">
          </div>

          <!-- Emergency Contacts -->
          <div class="col-span-2 mt-4">
            <h3 class="text-lg font-semibold text-gray-700">Emergency Contacts</h3>
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Contact 1 Name:</label>
            <input type="text" name="contact1_name" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Relationship:</label>
            <input type="text" name="contact1_relationship" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Contact 1 Phone:</label>
            <input type="text" name="contact1_phone" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Contact 2 Name:</label>
            <input type="text" name="contact2_name" value="none" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Relationship:</label>
            <input type="text" name="contact2_relationship" value="none" class="w-full p-2 border rounded hover-3d">
          </div>
          <div class="col-span-1">
            <label class="block text-gray-700">Contact 2 Phone:</label>
            <input type="text" name="contact2_phone" value="none" class="w-full p-2 border rounded hover-3d">
          </div>
        </div>

        <!-- Children Section -->
        <div class="mt-6">
          <h3 class="text-lg font-bold text-gray-700">Children</h3>
          <div id="childrenContainer"></div>
          <button type="button" id="addChildBtn" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Child</button>
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
          <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Member</button>
        </div>
      </form>
      <p id="responseMessage" class="mt-4 text-center"></p>
    </main>
  </div>

  <!-- JavaScript for Dynamic Children and Form Submission -->
  <script>
    document.getElementById("addChildBtn").addEventListener("click", function() {
      let childIndex = document.querySelectorAll(".child-entry").length;
      let childHtml = `
        <div class="child-entry border p-4 rounded mt-4 bg-gray-50">
          <h4 class="font-semibold">Child ${childIndex + 1}</h4>
          <div class="grid grid-cols-2 gap-4 mt-2">
            <div>
              <label class="block text-sm font-medium">Child Name</label>
              <input type="text" name="children[${childIndex}][name]" required class="w-full border rounded p-2">
            </div>
            <div>
              <label class="block text-sm font-medium">Date of Birth</label>
              <input type="date" name="children[${childIndex}][dob]" required class="w-full border rounded p-2">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4 mt-2">
            <div>
              <label class="block text-sm font-medium">School/Class/Stage</label>
              <input type="text" name="children[${childIndex}][school]" class="w-full border rounded p-2">
            </div>
            <div>
              <label class="block text-sm font-medium">Gender</label>
              <select name="children[${childIndex}][gender]" class="w-full border rounded p-2">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4 mt-2">
            <div>
              <label class="block text-sm font-medium">Residence</label>
              <input type="text" name="children[${childIndex}][residence]" class="w-full border rounded p-2">
            </div>
            <div>
              <label class="block text-sm font-medium">Phone (Optional)</label>
              <input type="text" name="children[${childIndex}][phone]" class="w-full border rounded p-2">
            </div>
          </div>
          <button type="button" class="remove-child bg-red-600 text-white px-2 py-1 rounded mt-2">Remove</button>
        </div>`;
      document.getElementById("childrenContainer").insertAdjacentHTML("beforeend", childHtml);
    });

    document.getElementById("childrenContainer").addEventListener("click", function(event) {
      if (event.target.classList.contains("remove-child")) {
        event.target.parentElement.remove();
      }
    });

    document.getElementById("addMemberForm").addEventListener("submit", function(event) {
      event.preventDefault();
      let formData = new FormData(this);

      // Collect children data into an array
      let childrenData = [];
      document.querySelectorAll(".child-entry").forEach((child, index) => {
        let childObj = {
          name: child.querySelector(`[name="children[${index}][name]"]`).value,
          dob: child.querySelector(`[name="children[${index}][dob]"]`).value,
          school: child.querySelector(`[name="children[${index}][school]"]`).value,
          gender: child.querySelector(`[name="children[${index}][gender]"]`).value,
          residence: child.querySelector(`[name="children[${index}][residence]"]`).value,
          phone: child.querySelector(`[name="children[${index}][phone]"]`).value
        };
        childrenData.push(childObj);
      });
      formData.append("children", JSON.stringify(childrenData));

      fetch("insert_member.php", {
  method: "POST",
  body: formData
})
.then(response => response.text())  // Get the raw text
.then(text => {
  console.log("Raw response:", text);  // Log the raw response to the console
  try {
    const data = JSON.parse(text);
    document.getElementById("responseMessage").textContent = data.message;
    document.getElementById("responseMessage").className = data.status === "success" ? "text-green-600" : "text-red-600";
    if(data.status === "success"){
      alert("Member added successfully!"); // Alert on success
      document.getElementById("addMemberForm").reset();
      document.getElementById("childrenContainer").innerHTML = "";
    }
  } catch (err) {
    console.error("JSON parse error:", err);
  }
})
.catch(error => {
  console.error("Error:", error);
  document.getElementById("responseMessage").textContent = "An error occurred.";
  document.getElementById("responseMessage").className = "text-red-600";
  alert("An error occured"); // Alert on error
});

    });
  </script>
</body>
</html>
