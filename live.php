<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a fan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fan') {
    header('Location: login.php');
    exit();
}

// Fetch live creators
$sql = "SELECT id, username, stream_title FROM users WHERE is_live = 1 AND role = 'creator'";
$result = $conn->query($sql);

$liveCreators = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $liveCreators[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="resources/favicon.png" type="image/x-icon">
    <title>Watch Live Stream</title>
    <style>
        #videoContainer {
            width: 100%;
            height: 500px;
            background-color: black;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #videoElement {
            max-width: 100%;
            height: auto;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-900 text-white">

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
                <a href="logout.php" class="text-red-400 hover:text-white">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-400 hover:text-white">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="p-8">
        <h2 class="text-3xl font-bold mb-4">Live Streams</h2>

        <?php if (count($liveCreators) > 0): ?>
            <div class="mb-6">
                <h3 class="text-xl font-semibold">Creators currently live:</h3>
                <ul class="list-disc pl-5 mt-2">
                    <?php foreach ($liveCreators as $creator): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($creator['username']); ?></strong> -
                            <?php echo htmlspecialchars($creator['stream_title']); ?>
                            <button class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md ml-4"
                                onclick="watchStream(<?php echo $creator['id']; ?>)">
                                Watch
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p class="text-gray-400">No creators are currently live.</p>
        <?php endif; ?>

        <div id="videoContainer" class="rounded-lg hidden">
            <p id="loadingMessage" class="text-gray-400">Loading stream...</p>
        </div>

    </div>

    <script>
        let videoElement = document.getElementById('videoElement');
        let videoContainer = document.getElementById('videoContainer');
        let loadingMessage = document.getElementById('loadingMessage');

        function watchStream(creatorId) {
            videoContainer.classList.remove('hidden');
            loadingMessage.textContent = 'Loading stream...';

            // Fetch the stream URL from the server
            fetch('get_stream_url.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        creator_id: creatorId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        videoElement.src = data.stream_url;
                        videoElement.play();
                        loadingMessage.classList.add('hidden');
                    } else {
                        loadingMessage.textContent = 'Failed to load stream.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching stream URL:', error);
                    loadingMessage.textContent = 'Failed to load stream.';
                });
        }
    </script>
</body>

</html>