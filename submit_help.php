<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $user_id = $_POST['user_id']; // Assuming user_id is also being sent in the POST request

    // Insert the help request into the contacts table
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, message, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $email, $message, $user_id); // Corrected the bind_param to include the integer type for user_id

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
}
?>
