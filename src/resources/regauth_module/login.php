<?php
// If the form is submitted, process the login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is a placeholder for login processing logic (add your own logic here)
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // For testing, just check if fields are filled
    if (empty($email) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        // Add your user validation logic here
        $error = "Login logic not implemented yet."; // Remove this after adding login logic
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include the styles file -->
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li class="active"><a href="#">Log In</a></li>
        <li><a href="register.php">Sign Up</a></li>
    </ul>

    <!-- Login form -->
    <form method="POST" action="login.php" class="auth-form">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address..." required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password..." required>
            <a href="#" class="forgot-password">Forgot password?</a>
        </div>

        <button type="submit" class="auth-button">Log In</button>
    </form>

    <div class="divider">OR</div>

    <button class="google-auth-btn">
        <img src="google-icon.png" alt="Google Icon">
        Continue with Google
    </button>
</div>
</body>
</html>
