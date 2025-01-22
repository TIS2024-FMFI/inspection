<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'No email available';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '';

// Connect to database
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'safety_app';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $connect = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}

// Get data from database
$product = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    try {
        $sql = "SELECT * FROM defective_products WHERE id = :id";
        $stmt = $connect->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Query error: " . $e->getMessage());
    }
    
} else {
    die("Invalid product ID.");
}

if (!$product) {
    echo "<div class='container mt-4'><p>Product not found.</p></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="search_page_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Product Details</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <h2 class="homepage-title">Product</h2>
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
                    <a href="welcome.php" class="profile-menu-item">Home</a>
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
    <div class="container my-5">
        <h2 class="mb-4"><?php echo htmlspecialchars($product['product_name']); ?></h2>
        
        <div class="card p-4 shadow-lg">
            <h4 class="mb-3">General Information</h4>
            <p><strong>Product Info:</strong> <?php echo htmlspecialchars($product['product_info'] ?? ''); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['product_category'] ?? ''); ?></p>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand'] ?? ''); ?></p>
            <p><strong>Model/Type Number:</strong> <?php echo htmlspecialchars($product['model_type_number'] ?? ''); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['product_description'] ?? ''); ?></p>
            <p><strong>Production Dates:</strong> <?php echo htmlspecialchars($product['production_dates'] ?? ''); ?></p>
            <p><strong>Country of Origin:</strong> <?php echo htmlspecialchars($product['country_of_origin'] ?? ''); ?></p>
            <p><strong>Notifying Country:</strong> <?php echo htmlspecialchars($product['notifying_country'] ?? ''); ?></p>
            <p><strong>Alert Number:</strong> <?php echo htmlspecialchars($product['alert_number'] ?? ''); ?></p>
            <p><strong>Case URL:</strong> 
                <?php if (!empty($product['case_url'])): ?>
                    <a href="<?php echo htmlspecialchars($product['case_url']); ?>" target="_blank">Link</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </p>
            <p><strong>Batch Number:</strong> <?php echo htmlspecialchars($product['batch_number'] ?? ''); ?></p>
            <p><strong>Barcode:</strong> <?php echo htmlspecialchars($product['barcode'] ?? ''); ?></p>

            <hr>
            <h4 class="mb-3">Risk and Measures</h4>
            <p><strong>Risk Type:</strong> <?php echo htmlspecialchars($product['risk_type'] ?? ''); ?></p>
            <p><strong>Risk Information:</strong> <?php echo htmlspecialchars($product['risk_info'] ?? ''); ?></p>
            <p><strong>Measures Taken:</strong> <?php echo htmlspecialchars($product['measures'] ?? ''); ?></p>
            
            <hr>
            <h4 class="mb-3">Additional Details</h4>
            <p><strong>Recall Code:</strong> <?php echo htmlspecialchars($product['company_recall_code'] ?? ''); ?></p>
            <p><strong>Company Recall Page:</strong> 
                <?php if (!empty($product['company_recall_page'])): ?>
                    <a href="<?php echo htmlspecialchars($product['company_recall_page']); ?>" target="_blank">Link</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </p>
            <p><strong>Level:</strong> <?php echo htmlspecialchars($product['level'] ?? ''); ?></p>
            
            <?php if (!empty($product['images'])): ?>
                <hr>
                <h4 class="mb-3">Images</h4>
                <img src="<?php echo htmlspecialchars($product['images']); ?>" class="img-fluid" alt="Product Image">
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'login_signup_popup_widget.html'; ?>

<script src="scripts.js"></script>

</body>
</html>
