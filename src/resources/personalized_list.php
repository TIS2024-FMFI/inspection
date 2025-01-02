<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db/config.php'; // Include database configuration

// Fetch products for the user
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = :user_id");
    $stmt->execute(['user_id' => 1]); // Replace 1 with the logged-in user's ID
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized List</title>
    <link rel="stylesheet" href="personalizedlist_styles.css">
</head>
<body>
<header>
    <div class="tabs">
        <button class="tab active" onclick="showTab('my-list')">Personalized List</button>
<!--        <button class="tab" onclick="showTab('history')">History</button>-->
    </div>

    <!-- Вместо div.profile-header оставляем практически то же, но оборачиваем в profile-menu-container -->
    <div class="profile-header profile-menu-container">
        <img src="images/profile-pic.png"
             alt="Profile Picture"
             class="profile-pic"
             onclick="toggleProfileMenu()">

        <!-- Выпадающее меню -->
        <div class="profile-menu" id="profile-menu">
            <!-- Email -->
            <p class="profile-email">
                <!-- Если в PHP-коде уже есть $_SESSION['email'], можно вывести здесь -->
                <?php echo htmlspecialchars($_SESSION['email'] ?? 'No email'); ?>
            </p>
            <a href="index.php" class="profile-menu-item">Home</a>
            <a href="/history.php" class="profile-menu-item">History</a>
            <a href="logout.php" class="profile-menu-item">Logout</a>

        </div>
    </div>
</header>
<main>
    <div class="content" id="my-list">
        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><strong>Barcode:</strong> <?php echo htmlspecialchars(isset($product['barcode']) ? $product['barcode'] : 'N/A'); ?></p>
                        <p><strong>Brand:</strong> <?php echo htmlspecialchars(isset($product['brand']) ? $product['brand'] : 'N/A'); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars(isset($product['description']) ? $product['description'] : 'No description available.'); ?></p>
                        <button class="edit-btn">Edit</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="content hidden" id="history">
        <p>History tab content goes here.</p>
    </div>
</main>
<script src="scripts.js"></script>
</body>
</html>

