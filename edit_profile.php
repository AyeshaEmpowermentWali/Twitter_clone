<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Edit Profile</title>
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
        .edit-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .edit-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .edit-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #e1e8ed;
            border-radius: 5px;
            font-size: 16px;
        }
        .edit-container button {
            width: 100%;
            padding: 10px;
            background: #1da1f2;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .edit-container button:hover {
            background: #1a91da;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Edit Profile</h2>
        <?php
        session_start();
        include 'db.php';
        if (!isset($_SESSION['user_id'])) {
            echo '<script>window.location.href = "login.php";</script>';
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        ?>
        <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit">Save Changes</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $profile_picture = $user['profile_picture'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['name']) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $profile_picture = $target_dir . basename($_FILES['profile_picture']['name']);
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
            }
            $stmt = $conn->prepare("UPDATE users SET username = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $profile_picture, $user_id);
            if ($stmt->execute()) {
                echo '<script>window.location.href = "profile.php";</script>';
            } else {
                echo '<p style="color: red; text-align: center;">Update failed</p>';
            }
        }
        ?>
    </div>
</body>
</html>
