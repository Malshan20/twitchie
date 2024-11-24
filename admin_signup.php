<?php
include 'db.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert admin into the database
        $sql = "INSERT INTO admin (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password_hash);
        if ($stmt->execute()) {
            header("Location: admin_login.php"); // Redirect to login page
            exit();
        } else {
            $error = "Error: Could not sign up. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <script>
        // JavaScript for form validation
        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="bg-gray-800 flex justify-center items-center min-h-screen">
    <div class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-3xl text-white text-center mb-6">Admin Signup</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm();">
            <div class="mb-4">
                <label class="block text-gray-300">Username</label>
                <input type="text" name="username" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-300">Email</label>
                <input type="email" name="email" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-300">Password</label>
                <input type="password" id="password" name="password" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-300">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="text-center">
                <button type="submit" class="bg-gradient-to-r from-blue-500 to-green-500 text-white px-6 py-2 rounded-full hover:shadow-lg transform hover:scale-105 transition duration-300">
                    Sign Up
                </button>
            </div>
        </form>
        <div class="text-center mt-4">
            <a href="admin_login.php" class="text-blue-400">Already have an account? Log in</a>
        </div>
    </div>
</body>
</html>
