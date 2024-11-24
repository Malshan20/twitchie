<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.php');
    exit();
}

// Fetch live stream details
$creator_id = $_SESSION['user_id'];
$sql = "SELECT is_live, stream_title, stream_start FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$stmt->bind_result($is_live, $stream_title, $stream_start);
$stmt->fetch();
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
    <title>Advanced Live Stream</title>
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
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <nav class="flex items-center justify-between p-4 bg-gray-800">
        <h1 class="text-2xl">Live Stream Dashboard</h1>
        <div class="flex items-center space-x-4">
            <a href="creator_dashboard.php" class="text-gray-400 hover:text-white">Home</a>
            <a href="live_stream.php" class="text-gray-400 hover:text-white">Live Stream</a>
            <a href="upload_video.php" class="text-gray-400 hover:text-white">Upload Video</a>
            <a href="withdraw_rewards.php" class="text-gray-400 hover:text-white">Withdraw Rewards</a>
            <a href="contact_us_creator.php" class="text-gray-400 hover:text-white">Contact Us</a>
            <a href="logout.php" class="text-red-500 hover:text-white">Logout</a>
        </div>
    </nav>

    <div class="p-8">
        <h2 class="text-3xl font-bold mb-4">Live Streaming Control</h2>

        <div class="mb-6">
            <h3 class="text-xl font-semibold">
                <?php if ($is_live): ?>
                    You’re currently live with the title: <span class="text-blue-400"><?php echo htmlspecialchars($stream_title); ?></span>
                    <br>Stream started at: <?php echo date('Y-m-d H:i:s', strtotime($stream_start)); ?>
                <?php else: ?>
                    You’re currently offline.
                <?php endif; ?>
            </h3>
        </div>

        <div id="videoContainer" class="rounded-lg mb-4">
            <video id="videoElement" class="rounded-lg hidden"></video>
            <p id="loadingMessage" class="text-gray-400">Requesting camera and microphone access...</p>
        </div>

        <div id="controls" class="space-x-4">
            <button id="startStream" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md">Start Streaming</button>
            <button id="stopStream" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md hidden">Stop Streaming</button>
        </div>

        <div id="streamInfo" class="mt-4 hidden">
            <p class="text-gray-400">Current viewers: <span id="viewersCount">0</span></p>
            <p class="text-gray-400">Bitrate: <span id="bitrate">0 kbps</span></p>
        </div>

    </div>

    <script>
        let mediaRecorder;
        let liveStream;
        let isLive = <?php echo $is_live ? 'true' : 'false'; ?>;
        let streamTitle = '<?php echo htmlspecialchars($stream_title); ?>';
        let videoElement = document.getElementById('videoElement');
        let loadingMessage = document.getElementById('loadingMessage');
        let startStreamBtn = document.getElementById('startStream');
        let stopStreamBtn = document.getElementById('stopStream');
        let streamInfo = document.getElementById('streamInfo');

        async function startStream() {
            try {
                // Request camera and microphone access
                liveStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                videoElement.srcObject = liveStream;
                videoElement.play();
                videoElement.classList.remove('hidden');
                loadingMessage.classList.add('hidden');

                mediaRecorder = new MediaRecorder(liveStream);
                mediaRecorder.start();

                // Show stream information
                startStreamBtn.classList.add('hidden');
                stopStreamBtn.classList.remove('hidden');
                streamInfo.classList.remove('hidden');

                // Set is_live status in the database
                await fetch('update_stream_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_live: 1, stream_title: 'Your stream title' })
                });
                
                isLive = true;

                // Simulate viewer count and bitrate (you can enhance this with actual data)
                simulateViewerAndBitrate();

            } catch (error) {
                console.error('Error accessing media devices:', error);
                loadingMessage.textContent = 'Failed to access camera or microphone.';
            }
        }

        function stopStream() {
            mediaRecorder.stop();
            liveStream.getTracks().forEach(track => track.stop());

            startStreamBtn.classList.remove('hidden');
            stopStreamBtn.classList.add('hidden');
            streamInfo.classList.add('hidden');

            // Set is_live status to 0 in the database
            fetch('update_stream_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_live: 0 })
            });

            isLive = false;
        }

        function simulateViewerAndBitrate() {
            setInterval(() => {
                if (isLive) {
                    document.getElementById('viewersCount').textContent = Math.floor(Math.random() * 100);
                    document.getElementById('bitrate').textContent = `${Math.floor(Math.random() * 5000)} kbps`;
                }
            }, 2000);
        }

        startStreamBtn.addEventListener('click', startStream);
        stopStreamBtn.addEventListener('click', stopStream);
    </script>
</body>
</html>
