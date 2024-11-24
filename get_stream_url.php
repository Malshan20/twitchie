<?php
include 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$creator_id = $data['creator_id'] ?? null;

if ($creator_id) {
    // Fetch the stream URL for the given creator
    $sql = "SELECT stream_url FROM live_streams WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $creator_id);
    $stmt->execute();
    $stmt->bind_result($stream_url);
    $stmt->fetch();
    
    if ($stream_url) {
        echo json_encode(['success' => true, 'stream_url' => $stream_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Stream not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid creator ID']);
}

$conn->close();
?>
