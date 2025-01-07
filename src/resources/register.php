<?php
session_start();
$error = ""; // Default error message
header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validation checks
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $response['error'] = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email address.";
    } elseif (strlen($password) < 8) {
        $response['error'] = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $response['error'] = "Passwords do not match.";
    } else {
        require 'db/config.php';

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                $response['error'] = "Email is already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $username = substr($email, 0, strpos($email, '@'));
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, username) VALUES (:email, :password_hash, 1, :username)");
                $stmt->execute(['email' => $email, 'password_hash' => $hashedPassword, 'username' => $username]);

                $response['success'] = true;
                $response['error'] = 'Register successful! Please log in.';
            }
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
        }
    }
    echo json_encode($response);
    exit;
}
?>

<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li><a href="login.php">Log In</a></li>
        <li class="active"><a href="#">Sign Up</a></li>
    </ul>

    <form method="POST" action="register.php" class="auth-form">
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

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password..." required>
        </div>

        <button type="submit" class="auth-button">Create an account</button>
    </form>

    <div class="divider">OR</div>

    <button class="google-auth-btn">
        <img src="images/google-icon.png" alt="Google Icon">
        Continue with Google
    </button>
</div>
</body>
</html>
-->