<?php
session_start();
$error = ""; // Default error message

// If the form is submitted, process the registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validation checks
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Placeholder for registration logic (e.g., database insertion)
        $error = "Registration logic not implemented yet."; // Remove this after adding logic
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include the styles file -->
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li><a href="login.php">Log In</a></li>
        <li class="active"><a href="#">Sign Up</a></li>
    </ul>

    <!-- Registration form -->
    <form method="POST" action="register.php" class="auth-form">
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
