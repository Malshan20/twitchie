<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $is_live = $data['is_live'] ?? 0;
    $stream_title = $data['stream_title'] ?? null;
    $creator_id = $_SESSION['user_id'];

    $sql = "UPDATE users SET is_live = ?, stream_title = ?, stream_start = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $is_live, $stream_title, $creator_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true]);
}
