<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
    file_put_contents('debug.log', "Session user_id not set at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Homepage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background: #f5f8fa;
            color: #14171a;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
        }
        .sidebar {
            width: 30%;
            padding: 20px;
        }
        .profile-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .profile-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .main-content {
            width: 70%;
            padding: 20px;
        }
        .tweet-box {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .tweet-box textarea {
            width: 100%;
            border: none;
            resize: none;
            font-size: 16px;
            padding: 10px;
            outline: none;
        }
        .tweet-box button {
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .tweet-box button:hover {
            background: #1a91da;
        }
        .tweet {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
        }
        .tweet img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .tweet-content {
            flex: 1;
        }
        .tweet-actions button {
            background: none;
            border: none;
            color: #657786;
            cursor: pointer;
            margin-right: 15px;
        }
        .tweet-actions button:hover {
            color: #1da1f2;
        }
        .comment-form {
            display: none;
            margin-top: 10px;
        }
        .comment-form textarea {
            width: 100%;
            border: 1px solid #e1e8ed;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
        }
        .comment-form button {
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 5px 15px;
            border-radius: 15px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar, .main-content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="profile-card">
                <?php
                include 'db.php';
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                ?>
                <img src="<?php echo $user['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile">
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                <a href="profile.php">View Profile</a>
                <br>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div class="tweet-box">
                <form method="POST" action="post_tweet.php">
                    <textarea name="content" placeholder="What's happening?" maxlength="280" required></textarea>
                    <button type="submit">Tweet</button>
                </form>
            </div>
            <?php
            $stmt = $conn->prepare("
                SELECT t.id, t.content, t.created_at, u.username, u.profile_picture, 
                       (SELECT COUNT(*) FROM likes WHERE tweet_id = t.id) as like_count,
                       (SELECT COUNT(*) FROM comments WHERE tweet_id = t.id) as comment_count
                FROM tweets t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN followers f ON f.followed_id = t.user_id
                WHERE f.follower_id = ? OR t.user_id = ?
                ORDER BY t.created_at DESC
            ");
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($tweet = $result->fetch_assoc()) {
            ?>
                <div class="tweet">
                    <img src="<?php echo $tweet['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile">
                    <div class="tweet-content">
                        <strong><?php echo htmlspecialchars($tweet['username']); ?></strong>
                        <small><?php echo $tweet['created_at']; ?></small>
                        <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                        <div class="tweet-actions">
                            <button onclick="likeTweet(<?php echo $tweet['id']; ?>)">Like (<?php echo $tweet['like_count']; ?>)</button>
                            <button onclick="toggleComment(<?php echo $tweet['id']; ?>)">Comment (<?php echo $tweet['comment_count']; ?>)</button>
                            <?php if ($tweet['user_id'] == $user_id) { ?>
                                <button onclick="editTweet(<?php echo $tweet['id']; ?>)">Edit</button>
                                <button onclick="deleteTweet(<?php echo $tweet['id']; ?>)">Delete</button>
                            <?php } ?>
                        </div>
                        <div class="comment-form" id="comment-form-<?php echo $tweet['id']; ?>">
                            <form method="POST" action="post_comment.php">
                                <input type="hidden" name="tweet_id" value="<?php echo $tweet['id']; ?>">
                                <textarea name="content" placeholder="Add a comment..." required></textarea>
                                <button type="submit">Comment</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <script>
        function likeTweet(tweetId) {
            fetch('like_tweet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'tweet_id=' + tweetId
            }).then(() => location.reload());
        }
        function toggleComment(tweetId) {
            document.getElementById('comment-form-' + tweetId).style.display = 
                document.getElementById('comment-form-' + tweetId).style.display === 'none' ? 'block' : 'none';
        }
        function editTweet(tweetId) {
            window.location.href = 'edit_tweet.php?id=' + tweetId;
        }
        function deleteTweet(tweetId) {
            if (confirm('Are you sure you want to delete this tweet?')) {
                fetch('delete_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'tweet_id=' + tweetId
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
