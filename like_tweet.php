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
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND tweet_id = ?");
    $stmt->bind_param("ii", $user_id, $tweet_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND tweet_id = ?");
    } else {
        $stmt = $conn->prepare("INSERT INTO likes (user_id, tweet_id) VALUES (?, ?)");
    }
    $stmt->bind_param("ii", $user_id, $tweet_id);
    $stmt->execute();
    echo '<script>window.location.href = "index.php";</script>';
}
?>
