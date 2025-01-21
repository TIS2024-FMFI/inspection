<?php
session_start();

// If the user is not logged in, redirect to the main page
if (!isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link rel="stylesheet" href="history.css">
</head>
<body>

<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <div class="homepage-container">
        <h2 class="history-title">Scan History</h2>
        <img src="images/profile-pic.png" alt="Profile Picture" class="profile-pic-mobile" onclick="toggleProfileMenu()">
    </div>
    <div class="header-buttons">
        <div class="profile-menu-container">
            <img src="images/profile-pic.png" alt="Profile Picture" class="profile-pic" onclick="toggleProfileMenu()">
            <div class="profile-menu" id="profile-menu">
                <p class="profile-username"><?php echo $username; ?></p>
                <a href="welcome.php" class="profile-menu-item">Home</a>
                <a href="personalized_list.php" class="profile-menu-item">Personalized List</a>
                <a href="logout.php" class="profile-menu-item">Logout</a>
            </div>
        </div>
    </div>
</header>
<main>
    <div class="content-container">
        <div class="history-list">
            <?php
            $user_id = $_SESSION['user_id'];
            try {
                $stmt = $pdo->prepare("
        SELECT ph.date, ph.time, ph.barcode, ph.product_link, ph.status,
            COALESCE(dp.product_name, pl.name, '') AS name
        FROM product_history ph
        LEFT JOIN defective_products dp ON ph.barcode = dp.barcode
        LEFT JOIN user_submitted_products pl ON ph.barcode = pl.barcode
        WHERE ph.user_id = :user_id
        ORDER BY date, time
    ");
                $stmt->execute(['user_id' => $user_id]);
                $historyItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = $pdo->prepare("
    UPDATE product_history ph
    JOIN user_submitted_products usp ON ph.barcode = usp.barcode
    SET ph.name = usp.name
    WHERE ph.user_id = :user_id AND ph.name = ''
");
                $stmt->execute(['user_id' => $user_id]);

                // Display history items
                if (count($historyItems) > 0) {
                    foreach ($historyItems as $row) {
                        $statusClass = ($row['status'] == 0) ? 'green-background' : 'red-background';
                        echo '<div class="history-item ' . $statusClass . '">';
                        echo '<div class="history-details">';
                        echo 'Date: ' . htmlspecialchars($row['date']) . ' | ';
                        echo 'Time: ' . htmlspecialchars($row['time']) . ' | ';
                        echo 'Name: ' . (htmlspecialchars($row['name']) ?? '');
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
