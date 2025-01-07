<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']); 
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="welcome_styles.css">
</head>
<body>
<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <div class="header-buttons">
        <?php if (!$isLoggedIn): ?>
            <button class="sign-in-button" onclick="openModal('login-modal')">Sign In</button>
        <?php else: ?>
            <a href="logout.php" class="sign-out-button">Logout</a>
        <?php endif; ?>
    </div>
</header>
<main>
    <h1>WELCOME</h1>
    <div class="content-container">
        <img src="images/logo.png" alt="Placeholder Image" class="welcome-image">
        <div class="welcome-text">
            <p>
                This website is part of a school project designed to help users identify defective products.
                You can search for products or scan their barcodes to check if they meet safety and quality standards.
            </p>
            <p>
                By registering an account, you can unlock additional features:
            </p>
            <ul>
                <li>View your search history.</li>
                <li>Add products to a personalized list for easier tracking.</li>
            </ul>
            <p>
                Or continue without registering with access to searching and scanning without additional features.
                <strong>Your safety is our priority.</strong>
            </p>
        </div>
    </div>
    <div class="button-container">
        <button class="register-button" onclick="openModal('register-modal')">Register Now</button>
        <button class="continue-button" onclick="location.href='index.php'">Continue without registering</button>
    </div>
</main>

<!-- Login Modal -->
<div class="modal" id="login-modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('login-modal')">&times;</span>
        <ul class="auth-tabs">
            <li class="active"><a href="#">Log In</a></li>
            <li><a href="#" onclick="switchToRegister()">Sign Up</a></li>
        </ul>
        <form id="login-form" method="POST" class="auth-form" onsubmit="handleLogin(event)">
            <div class="error-message" style="display:none; color: red;"></div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="auth-button">Log In</button>
        </form>
        <div class="divider">OR</div>
        <button class="google-auth-btn">
            <img src="images/google-icon.png" alt="Google Icon">
            Continue with Google
        </button>
    </div>
</div>

<!-- Register Modal -->
<div class="modal" id="register-modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('register-modal')">&times;</span>
        <ul class="auth-tabs">
            <li><a href="#" onclick="switchToLogin()">Log In</a></li>
            <li class="active"><a href="#">Sign Up</a></li>
        </ul>
        <form id="register-form" method="POST" class="auth-form" onsubmit="handleRegister(event)">
            <div class="error-message" style="display:none; color: red;"></div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <button type="submit" class="auth-button">Create an Account</button>
        </form>
        <div class="divider">OR</div>
        <button class="google-auth-btn">
            <img src="images/google-icon.png" alt="Google Icon">
            Continue with Google
        </button>
    </div>
</div>

<script src="scripts.js"></script>

</body>
</html>
