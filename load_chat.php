<?php
session_start();
include 'db.php';

// Fetch messages only for the logged-in user from the contacts table
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, name, message, admin_response FROM contacts WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($messages);
?>
