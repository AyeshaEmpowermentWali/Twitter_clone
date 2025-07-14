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
    $stmt = $conn->prepare("DELETE FROM tweets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $tweet_id, $user_id);
    $stmt->execute();
    echo '<script>window.location.href = "index.php";</script>';
}
?>
