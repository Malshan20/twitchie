<?php
include 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch admin details from the database
    $sql = "SELECT id, password_hash FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password_hash'])) {
            session_start();
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin_dashboard.php"); // Redirect to admin dashboard
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
</head>
<body class="bg-gray-800 flex justify-center items-center min-h-screen">
    <div class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-3xl text-white text-center mb-6">Admin Login</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-300">Email</label>
                <input type="email" name="email" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-300">Password</label>
                <input type="password" name="password" class="w-full p-3 mt-1 rounded-full bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="text-center">
                <button type="submit" class="bg-gradient-to-r from-blue-500 to-green-500 text-white px-6 py-2 rounded-full hover:shadow-lg transform hover:scale-105 transition duration-300">
                    Login
                </button>
            </div>
        </form>
        <div class="text-center mt-4">
            <a href="admin_signup.php" class="text-blue-400">Don't have an account? Sign up</a>
        </div>
    </div>
</body>
</html>
