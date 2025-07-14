<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Profile</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-header {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
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
        .edit-profile {
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .profile-header img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <?php
            session_start();
            include 'db.php';
            if (!isset($_SESSION['user_id'])) {
                echo '<script>window.location.href = "login.php";</script>';
                exit;
            }
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("
                SELECT username, profile_picture, 
                       (SELECT COUNT(*) FROM followers WHERE followed_id = ?) as followers,
                       (SELECT COUNT(*) FROM followers WHERE follower_id = ?) as following
                FROM users WHERE id = ?
            ");
            $stmt->bind_param("iii", $user_id, $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            ?>
            <img src="<?php echo $user['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <div class="profile-stats">
                <div><strong><?php echo $user['following']; ?></strong> Following</div>
                <div><strong><?php echo $user['followers']; ?></strong> Followers</div>
            </div>
            <button class="edit-profile" onclick="window.location.href='edit_profile.php'">Edit Profile</button>
        </div>
        <?php
        $stmt = $conn->prepare("
            SELECT t.id, t.content, t.created_at, u.username, u.profile_picture
            FROM tweets t
            JOIN users u ON t.user_id = u.id
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
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
                        <button onclick="editTweet(<?php echo $tweet['id']; ?>)">Edit</button>
                        <button onclick="deleteTweet(<?php echo $tweet['id']; ?>)">Delete</button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <script>
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
