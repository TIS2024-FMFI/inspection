<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'No email available';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <h2 class="homepage-title">Homepage</h2>
    <div class="header-buttons">
        <?php if ($isLoggedIn): ?>
            <div class="profile-menu-container">
                <img src="images/profile-pic.png"
                     alt="Profile Picture"
                     class="profile-pic"
                     onclick="toggleProfileMenu()">

                <div class="profile-menu" id="profile-menu">
                    <p class="profile-username">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'No username'); ?>
                    </p>
                    <a href="personalized_list.php" class="profile-menu-item">Personalized List</a>
                    <a href="history.php" class="profile-menu-item">History</a>
                    <a href="logout.php" class="profile-menu-item">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <button class="sign-in-button" onclick="openModal('login-modal')">Sign In</button>
        <?php endif; ?>
    </div>
</header>


<main>
    <div class="content-container">
        <h1>Search for defective products</h1>
        <div class="search-container">
            <form action="SearchPage.php" method="GET" class="d-flex gap-2">
                <input type="text" name="search" placeholder="Search" class="form-control">
            </form>
        </div>
        <p>or scan them...</p>
        <button id="scan-button">Scan</button>
    </div>

    <?php if ($isLoggedIn && $userRole === 'admin'): ?>
        <div class="admin-panel">
            <a href="scrape_sites.php" class="scrape-sites-button">Scrape Sites</a>
        </div>
    <?php endif; ?>
</main>


<!-- Login Modal -->
<div class="modal" id="login-modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('login-modal')">&times;</span>
        <ul class="auth-tabs">
            <li class="active"><a href="#" onclick="switchToLogin()">Log In</a></li>
            <li><a href="#" onclick="switchToRegister()">Sign Up</a></li>
        </ul>
        <form action="login.php" method="POST" class="auth-form">
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
        <form action="register.php" method="POST" class="auth-form">
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