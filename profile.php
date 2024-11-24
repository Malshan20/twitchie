<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, paid_status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle payment cancellation if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_payment'])) {
    $update_sql = "UPDATE users SET paid_status = 'unpaid' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();

    // Refresh the user data after updating
    header("Location: profile.php");
    exit();
}

// Handle email and username update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];

    $update_profile_sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    $update_profile_stmt = $conn->prepare($update_profile_sql);
    $update_profile_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    $update_profile_stmt->execute();

    header("Location: profile.php");
    exit();
}

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    // Redirect to the password reset page or handle password reset logic here
    header("Location: password_reset.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Profile</title>
</head>

<body class=" text-gray-200" style="background: url('https://img.freepik.com/free-photo/glowing-sky-sphere-orbits-starry-galaxy-generated-by-ai_188544-15599.jpg?t=st=1728058867~exp=1728062467~hmac=4f09482be791922b73f495f5b4e603d64e4930a473ab629fcaefc6f8a6ffea42&w=1060') no-repeat center center fixed; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;">

    <!-- Navigation Bar -->
    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <div class="flex items-center space-x-4">
            <h1 class="text-white text-2xl">Twitchie</h1>
            <a href="index.php" class="text-gray-400 hover:text-white">Home</a>
            <div class="relative">
                <input type="text" placeholder="Search..." class="bg-gray-700 text-gray-200 p-2 rounded-md">
            </div>
            <div class="relative">
                <button class="text-gray-400 hover:text-white" onclick="document.getElementById('notification-dropdown').classList.toggle('hidden')">
                    Notifications
                </button>
                <div id="notification-dropdown" class="absolute right-0 bg-gray-800 rounded-md p-4 shadow-lg hidden" style="width: 250px;">
                    <div id="notifications-container">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
            </div>
            <a href="live.php" class="text-gray-400 hover:text-white">Live Stream</a>
        </div>
        <div>
            <a href="premium_content.php" class="text-gray-400 hover:text-white mr-2">More Videos </a>
            <a href="help.php" class="text-gray-400 hover:text-white mr-2">Help |</a>

            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="profile.php"><span class="text-gray-400 hover:text-white"">Welcome, <?php echo $_SESSION['user_name']; ?></span></a>
                <a href=" logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg max-w-xl mx-auto">
            <h1 class="text-4xl font-extrabold mb-6 text-center text-blue-500">Profile Information</h1>

            <!-- Display user details -->
            <div class="text-lg space-y-4">
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-gray-300">Username:</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                            class="w-full p-2 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-300">Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="w-full p-2 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <p class="text-gray-400"><span class="font-semibold text-blue-500">Paid Status:</span>
                            <?php echo htmlspecialchars($user['paid_status']); ?></p>
                    </div>

                    <!-- Update Profile Button -->
                    <div class="text-center mt-6">
                        <button type="submit" name="update_profile" class="bg-green-500 text-white px-6 py-3 rounded-full hover:bg-green-600">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reset Password Button -->
            <div class="text-center mt-8">
                <form method="POST">
                    <button type="submit" name="reset_password" class="bg-purple-600 text-white px-6 py-3 rounded-full hover:bg-purple-700">
                        Reset Password
                    </button>
                </form>
            </div>

            <!-- Payment cancellation button if status is "paid" -->
            <?php if ($user['paid_status'] === 'paid'): ?>
                <form method="POST" class="mt-8 text-center">
                    <button type="submit" name="cancel_payment" class="bg-red-500 text-white px-6 py-3 rounded-full hover:bg-red-600">
                        Cancel Payment
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>