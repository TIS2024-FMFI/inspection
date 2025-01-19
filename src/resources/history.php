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

// Database connection
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'safety_app';
$username_db = getenv('DB_USER') ?: 'root';
$password_db = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History</title>
    <link rel="stylesheet" href="history.css">
</head>
<body>

<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <h2 class="history-title">Scan History</h2>
    <div class="header-buttons">
        <?php if ($isLoggedIn): ?>
            <div class="profile-menu-container">
                <img src="images/profile-pic.png"
                     alt="Profile Picture"
                     class="profile-pic"
                     onclick="toggleProfileMenu()">

                <div class="profile-menu" id="profile-menu">
                    <p class="profile-username"><?php echo $username; ?></p>
                    <a href="index.php" class="profile-menu-item">Home</a>
                    <a href="personalized_list.php" class="profile-menu-item">Personalized List</a>
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
        <div class="history-list">
            <?php
            // Fetch history items for the logged-in user
            $user_id = $_SESSION['user_id'];
            try {
                $stmt = $pdo->prepare("SELECT date, time, barcode, product_link FROM product_history WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user_id]);
                $historyItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Display history items
                if (count($historyItems) > 0) {
                    foreach ($historyItems as $row) {
                        echo '<div class="history-item">';
                        echo '<div class="history-details">';
                        echo 'Date: ' . htmlspecialchars($row['date']) . ' | ';
                        echo 'Time: ' . htmlspecialchars($row['time']) . ' | ';
                        echo 'Barcode: ' . (htmlspecialchars($row['barcode']) ?? 'Not Present');
                        echo '</div>';
                        echo '<div class="history-link">';
                        echo '<a href="' . htmlspecialchars($row['product_link']) . '" target="_blank">Product Link</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No history items found.</p>';
                }
            } catch (PDOException $e) {
                echo '<p>Error fetching history: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </div>
</main>

<script src="scripts.js"></script>
</body>
</html>
