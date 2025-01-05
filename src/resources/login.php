<?php
global $pdo;
session_start();
require 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    try {
        // Fetch the user from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Use password_verify to compare the input password with the hashed password
            if (password_verify($password, $user['password_hash'])) {
                // Store user information in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // Redirect to the index page
                header('Location: index.php');
                exit;
            } else {
                $error = "The email address or password you entered is incorrect. Please try again...";
            }
        } else {
            $error = "The email address or password you entered is incorrect. Please try again...";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li class="active"><a href="#">Log In</a></li>
        <li><a href="register.php">Sign Up</a></li>
    </ul>

    <form method="POST" action="login.php" class="auth-form">

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address..." required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password..." required>
        </div>
    <button type="submit" class="auth-button">Log In</button>
</form>

</div>
</body>
</html>