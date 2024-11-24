<?php
include 'db.php'; // Include your database connection

// Handle admin response submission for both fans and creators
if (isset($_POST['response']) && isset($_POST['contact_type']) && isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];
    $admin_response = $_POST['response'];
    $contact_type = $_POST['contact_type'];

    // Get the current admin responses
    if ($contact_type === 'fan') {
        $query = "SELECT admin_response FROM tickets WHERE id = $contact_id";
    } elseif ($contact_type === 'creator') {
        $query = "SELECT admin_response FROM contacts WHERE id = $contact_id";
    }

    $result = $conn->query($query);
    $current_response = $result->fetch_assoc()['admin_response'] ?? '';

    // Prepare new response format
    $timestamp = date('Y-m-d H:i:s');
    $new_response = ($current_response ? json_decode($current_response, true) : []);
    $new_response[] = [
        'response' => $admin_response,
        'timestamp' => $timestamp,
        'from' => 'admin' // to identify the sender
    ];

    // Update the appropriate table based on the contact type
    if ($contact_type === 'fan') {
        $query = "UPDATE tickets SET admin_response = '" . json_encode($new_response) . "' WHERE id = $contact_id";
    } elseif ($contact_type === 'creator') {
        $query = "UPDATE contacts SET admin_response = '" . json_encode($new_response) . "', updated_at = NOW() WHERE id = $contact_id";
    }

    if ($conn->query($query) === TRUE) {
        echo "Response submitted successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch fans' contacts (from the tickets table)
$query_fans = "SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
$result_fans = $conn->query($query_fans);

// Fetch creators' contacts (from the contacts table)
$query_creators = "SELECT c.*, u.username FROM contacts c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC";
$result_creators = $conn->query($query_creators);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-white p-4 flex justify-between items-center">
        <div class="text-2xl font-bold text-gray-700">Manage Contacts</div>
        <div>
            <ul class="flex space-x-4">
                <li><a href="admin_dashboard.php" class="text-gray-600 hover:text-black">Home</a></li>
                <li><a href="a_creators.php" class="text-gray-600 hover:text-black">Creators</a></li>
                <li><a href="a_fans.php" class="text-gray-600 hover:text-black">Fans</a></li>
                <li><a href="a_earing.php" class="text-gray-600 hover:text-black">Earnings</a></li>
                <li><a href="a_contact.php" class="text-gray-600 hover:text-black">Contacts</a></li>
                <li><a href="logout_admin.php" class="text-red-600 hover:text-black">Log Out</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4">

        <!-- Fans' Contacts Section -->
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Fans' Contacts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php while ($row = $result_fans->fetch_assoc()) { ?>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold mb-2">Chat with <?php echo $row['username']; ?></h3>

                <div class="border border-gray-300 rounded-lg h-64 overflow-y-auto p-4 mb-4">
                    <!-- Display question -->
                    <div class="bg-gray-100 p-2 rounded-md mb-3">
                        <strong><?php echo $row['username']; ?>:</strong>
                        <p><?php echo $row['question']; ?></p>
                    </div>

                    <!-- Display admin responses -->
                    <?php
                    $admin_responses = json_decode($row['admin_response'], true);
                    if (!empty($admin_responses)) {
                        foreach ($admin_responses as $response) {
                            echo '<div class="bg-green-100 p-2 rounded-md mb-3 text-right">';
                            echo '<strong>You:</strong>';
                            echo '<p>' . htmlspecialchars($response['response']) . '</p>';
                            echo '<small class="text-gray-500">' . $response['timestamp'] . '</small>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <!-- Chat Input for Response -->
                <form method="POST" class="flex">
                    <input type="hidden" name="contact_type" value="fan">
                    <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="response" class="border border-gray-300 rounded-l-lg w-full p-2" placeholder="Type your response...">
                    <button type="submit" class="bg-blue-500 text-white rounded-r-lg px-4">Send</button>
                </form>
            </div>
            <?php } ?>
        </div>

        <!-- Creators' Contacts Section -->
        <h2 class="text-3xl font-semibold text-gray-800 mb-6 mt-12">Creators' Contacts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php while ($row = $result_creators->fetch_assoc()) { ?>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-xl font-bold mb-2">Chat with <?php echo $row['username']; ?></h3>

                <div class="border border-gray-300 rounded-lg h-64 overflow-y-auto p-4 mb-4">
                    <!-- Display creator's message -->
                    <div class="bg-gray-100 p-2 rounded-md mb-3">
                        <strong><?php echo $row['username']; ?>:</strong>
                        <p><?php echo $row['message']; ?></p>
                    </div>

                    <!-- Display admin responses -->
                    <?php
                    $admin_responses = json_decode($row['admin_response'], true);
                    if (!empty($admin_responses)) {
                        foreach ($admin_responses as $response) {
                            echo '<div class="bg-green-100 p-2 rounded-md mb-3 text-right">';
                            echo '<strong>You:</strong>';
                            echo '<p>' . htmlspecialchars($response['response']) . '</p>';
                            echo '<small class="text-gray-500">' . $response['timestamp'] . '</small>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <!-- Chat Input for Response -->
                <form method="POST" class="flex">
                    <input type="hidden" name="contact_type" value="creator">
                    <input type="hidden" name="contact_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="response" class="border border-gray-300 rounded-l-lg w-full p-2" placeholder="Type your response...">
                    <button type="submit" class="bg-blue-500 text-white rounded-r-lg px-4">Send</button>
                </form>
            </div>
            <?php } ?>
        </div>

    </div>

</body>
</html>
