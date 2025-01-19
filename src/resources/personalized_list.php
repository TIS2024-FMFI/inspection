<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db/config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM user_submitted_products WHERE user_id = :user_id");
    $stmt->execute(['user_id' => 1]);
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
    <img src="images/logo.png" alt="Logo" class="logo">

    <!-- Контейнер для заголовка и мобильной иконки -->
    <div class="homepage-container">
        <h2 class="homepage-title personalized-list-title">Personalized List</h2>
        <!-- Мобильная версия иконки -->
        <img src="images/profile-pic.png" alt="Profile Picture" class="profile-pic-mobile" onclick="toggleProfileMenu()">
    </div>

    <!-- Десктопная версия иконки -->
    <div class="profile-header profile-menu-container">
        <img src="images/profile-pic.png"
             alt="Profile Picture"
             class="profile-pic"
             onclick="toggleProfileMenu()">

        <!-- Меню профиля -->
        <div class="profile-menu" id="profile-menu">
            <p class="profile-username">
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'No username'); ?>
            </p>
            <a href="index.php" class="profile-menu-item">Home</a>
            <a href="history.php" class="profile-menu-item">Scan History</a>
            <a href="logout.php" class="profile-menu-item">Logout</a>
        </div>
    </div>
</header>
<main>
    <div class="content" id="my-list">
        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-id="<?php echo htmlspecialchars($product['id']); ?>">
                        <div class="view-mode">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><strong>Barcode:</strong> <?php echo htmlspecialchars(isset($product['barcode']) ? $product['barcode'] : 'N/A'); ?></p>
                            <p><strong>Brand:</strong> <?php echo htmlspecialchars(isset($product['brand']) ? $product['brand'] : 'N/A'); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars(isset($product['description']) ? $product['description'] : 'No description available.'); ?></p>
                            <button class="edit-btn">Edit</button>
                        </div>
                        <div class="edit-mode hidden">
                            <h3> Name:</h3>
                            <input type="text" class="edit-name" value="<?php echo htmlspecialchars($product['name']); ?>" />
                            <p><strong>Brand:</strong></p>
                            <input type="text" class="edit-brand" value="<?php echo htmlspecialchars($product['brand']); ?>" />
                            <p><strong>Description:</strong></p>
                            <textarea class="edit-description"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            <button class="save-btn">Save</button>
                            <button class="cancel-btn">Cancel</button>
                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.product-card');
                card.querySelector('.view-mode').classList.add('hidden');
                card.querySelector('.edit-mode').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.save-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const card = button.closest('.product-card');
                const id = card.dataset.id;
                const name = card.querySelector('.edit-name').value;
                const brand = card.querySelector('.edit-brand').value;
                const description = card.querySelector('.edit-description').value;

                // Send the data to the server to update
                const response = await fetch('update_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name, brand, description })
                });

                if (response.ok) {
                    // Update the UI with the new values
                    card.querySelector('h3').textContent = name;
                    card.querySelector('.view-mode p:nth-child(3)').innerHTML = `<strong>Brand:</strong> ${brand}`;
                    card.querySelector('.view-mode p:nth-child(4)').innerHTML = `<strong>Description:</strong> ${description}`;
                    card.querySelector('.view-mode').classList.remove('hidden');
                    card.querySelector('.edit-mode').classList.add('hidden');
                } else {
                    alert('Failed to save changes.');
                }
            });
        });

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.product-card');
                card.querySelector('.view-mode').classList.remove('hidden');
                card.querySelector('.edit-mode').classList.add('hidden');
            });
        });
    });
</script>
<script src="scripts.js"></script>
</body>
</html>

