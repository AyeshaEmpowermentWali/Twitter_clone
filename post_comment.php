<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $tweet_id = $_POST['tweet_id'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("INSERT INTO comments (tweet_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $tweet_id, $user_id, $content);
    if ($stmt->execute()) {
        echo '<script>window.location.href = "index.php";</script>';
    } else {
        echo '<script>alert("Failed to post comment"); window.location.href = "index.php";</script>';
    }
}
?>
