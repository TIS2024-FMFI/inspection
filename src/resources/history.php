<?php
session_start();

// If the user is not logged in, redirect to the main page
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Prepare variables from the session to display in the header
$isLoggedIn = isset($_SESSION['user_id']);
$email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'No email available';
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History</title>
    <!-- Link to the dedicated CSS file (assuming it resides in the same folder as this file) -->
    <link rel="stylesheet" href="history.css">
</head>
<body>

<header>
    <!-- Logo located one level above, e.g., ../images/logo.png -->
    <img src="images/logo.png" alt="Logo" class="logo">

    <div class="header-buttons">
        <?php if ($isLoggedIn): ?>
            <!-- Container for the avatar icon and dropdown menu -->
            <div class="profile-menu-container">
                <!-- Profile picture (requires a file at regauth_module/images/profile-pic.png) -->
                <img src="images/profile-pic.png"
                     alt="Profile Picture"
                     class="profile-pic"
                     onclick="toggleProfileMenu()">

                <!-- Dropdown menu -->
                <div class="profile-menu" id="profile-menu">
                    <p class="profile-email"><?php echo $email; ?></p>

                    <!-- Home link (return to index.php) -->
                    <a href="index.php" class="profile-menu-item">Home</a>
                    <!-- Current page (History) -->
                    <a href="history.php" class="profile-menu-item">History</a>
                    <!-- Logout link -->
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
        <h1>History of Scans</h1>
        <p>This page can display the list of scanned products or search history, etc.</p>

        <div class="history-list">
            <p>No scan history yet.</p>
        </div>
    </div>
</main>

<!-- Link to your scripts.js file (which should contain toggleProfileMenu() and more) -->
<script src="scripts.js"></script>
</body>
</html>
