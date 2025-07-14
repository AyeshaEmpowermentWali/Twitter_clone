<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Edit Tweet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background: #f5f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .edit-tweet-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
        }
        .edit-tweet-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .edit-tweet-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #e1e8ed;
            border-radius: 5px;
            font-size: 16px;
            resize: none;
        }
        .edit-tweet-container button {
            width: 100%;
            padding: 10px;
            background: #1da1f2;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .edit-tweet-container button:hover {
            background: #1a91da;
        }
    </style>
</head>
<body>
    <div class="edit-tweet-container">
        <h2>Edit Tweet</h2>
        <?php
        session_start();
        include 'db.php';
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            echo '<script>window.location.href = "login.php";</script>';
            exit;
        }
        $tweet_id = $_GET['id'];
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT content FROM tweets WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $tweet_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($tweet = $result->fetch_assoc()) {
        ?>
            <form method="POST" action="edit_tweet.php?id=<?php echo $tweet_id; ?>">
                <textarea name="content" maxlength="280" required><?php echo htmlspecialchars($tweet['content']); ?></textarea>
                <button type="submit">Save Changes</button>
            </form>
        <?php
        } else {
            echo '<p style="color: red; text-align: center;">Tweet not found or unauthorized</p>';
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $_POST['content'];
            $stmt = $conn->prepare("UPDATE tweets SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $content, $tweet_id, $user_id);
            if ($stmt->execute()) {
                echo '<script>window.location.href = "index.php";</script>';
            } else {
                echo '<p style="color: red; text-align: center;">Update failed</p>';
            }
        }
        ?>
    </div>
</body>
</html>
