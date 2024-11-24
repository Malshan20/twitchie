<?php
include 'db.php'; // Include your database connection

// Handle actions (block, unblock, restrict, unrestrict)
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'block') {
        $query = "UPDATE users SET is_blocked = 1 WHERE id = $user_id";
    } elseif ($action === 'unblock') {
        $query = "UPDATE users SET is_blocked = 0 WHERE id = $user_id";
    } elseif ($action === 'restrict') {
        $query = "UPDATE users SET is_restricted = 1 WHERE id = $user_id";
    } elseif ($action === 'unrestrict') {
        $query = "UPDATE users SET is_restricted = 0 WHERE id = $user_id";
    }

    if ($conn->query($query) === TRUE) {
        echo "Action performed successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch fans where role is 'fans'
$query_fans = "SELECT * FROM users WHERE role = 'fan'";
$result_fans = $conn->query($query_fans);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fans</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
</head>

<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-white p-4 flex justify-between items-center">
        <div class="text-2xl font-bold text-gray-700">Manage Fans</div>
        <div>
            <ul class="flex space-x-4">
                <li><a href="admin_dashboard.php" class="text-gray-600 hover:text-black">Home</a></li>
                <li><a href="a_creators.php" class="text-gray-600 hover:text-black">Creators</a></li>
                <li><a href="a_fans.php" class="text-gray-600 hover:text-black">Fans</a></li>
                <li><a href="a_earing.php" class="text-gray-600 hover:text-black">Earnings</a></li>
                <li><a href="a_contact.php" class="text-gray-600 hover:text-black">Contact Us</a></li>
                <li><a href="logout_admin.php" class="text-red-600 hover:text-black">Log Out</a></li>
            </ul>
        </div>
    </nav>

        <!-- Fans Table -->
        <table class="min-w-full bg-white rounded-lg shadow-md mt-5">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm">Username</th>
                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm">Email</th>
                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm">Status</th>
                    <th class="w-1/4 py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_fans->fetch_assoc()) { ?>
                    <tr class="text-gray-700">
                        <td class="w-1/4 py-3 px-4"><?php echo $row['username']; ?></td>
                        <td class="w-1/4 py-3 px-4"><?php echo $row['email']; ?></td>
                        <td class="w-1/4 py-3 px-4">
                            <?php
                            if ($row['is_blocked'] == 1) {
                                echo '<span class="bg-red-500 text-white py-1 px-2 rounded-full text-xs">Blocked</span>';
                            } else {
                                echo '<span class="bg-green-500 text-white py-1 px-2 rounded-full text-xs">Active</span>';
                            }

                            if ($row['is_restricted'] == 1) {
                                echo '<span class="ml-2 bg-yellow-500 text-white py-1 px-2 rounded-full text-xs">Restricted</span>';
                            }
                            ?>
                        </td>
                        <td class="w-1/4 py-3 px-4">
                            <!-- Block/Unblock Button -->
                            <?php if ($row['is_blocked'] == 1) { ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="unblock">
                                    <button type="submit" class="bg-green-500 text-white py-1 px-4 rounded-full">Unblock</button>
                                </form>
                            <?php } else { ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="block">
                                    <button type="submit" class="bg-red-500 text-white py-1 px-4 rounded-full">Block</button>
                                </form>
                            <?php } ?>

                            <!-- Restrict/Unrestrict Button -->
                            <?php if ($row['is_restricted'] == 1) { ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="unrestrict">
                                    <button type="submit" class="bg-yellow-500 text-white py-1 px-4 rounded-full">Unrestrict</button>
                                </form>
                            <?php } else { ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="restrict">
                                    <button type="submit" class="bg-yellow-500 text-white py-1 px-4 rounded-full">Restrict</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>