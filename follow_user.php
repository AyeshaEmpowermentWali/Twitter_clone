<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
$user_id = $_SESSION['user_id'];
$followed_id = $_GET['user_id'];
$stmt = $conn->prepare("SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?");
$stmt->bind_param("ii", $user_id, $followed_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
} else {
    $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
}
$stmt->bind_param("ii", $user_id, $followed_id);
$stmt->execute();
echo '<script>window.location.href = "index.php";</script>';
?>
