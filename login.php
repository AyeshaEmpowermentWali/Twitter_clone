<?php
ob_start(); // Start output buffering to prevent header issues
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background: #1da1f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #e1e8ed;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #1da1f2;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .login-container button:hover {
            background: #1a91da;
        }
        .login-container a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #1da1f2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login to Twitter Clone</h2>
        <form method="POST" action="login.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
            <a href="register.php">Don't have an account? Sign Up</a>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include 'db.php';
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $password = $_POST['password'];
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true); // Prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $stmt->close();
                    header("Location: index.php");
                    echo '<script>window.location.href = "index.php";</script>'; // Fallback
                    exit();
                } else {
                    echo '<p style="color: red; text-align: center;">Invalid password</p>';
                }
            } else {
                echo '<p style="color: red; text-align: center;">User not found</p>';
            }
            $stmt->close();
        }
        ob_end_flush(); // Flush output buffer
        ?>
    </div>
</body>
</html>
