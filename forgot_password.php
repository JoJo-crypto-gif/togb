<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Forgot Password</title>
<style>
    .hover-3d:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .hover-3d {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header  class="bg-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center gap-100%">
        <h1 class="text-2xl font-semibold">Forgot Password</h1>
        <div class="notifications">
            <a href="login.php" class="text-blue-500 hover:underline"><i class="fas fa-sign-out-alt"></i> Back To Login</a>
        </div>
    </div>
    </header>
    <main class="flex-1 p-6 bg-white">
        <form id="forgot-password-form" action="send_reset_link.php" method="post" class="max-w-4xl mx-auto bg-gray-100 p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-2">
            <label for="phone" class="block text-gray-700">Phone:</label>
            <input type="text" id="phone" name="phone" required class="w-full p-2 border border-gray-300 rounded mt-1 hover-3d">
        </div>
        <div class="col-span-2 flex justify-center">
            <input type="submit" value="Send Reset Link" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 hover-3d">
        </div>
        </div>
        </form>
    </main>

</body>
</html>
