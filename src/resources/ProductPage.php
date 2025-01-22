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
        $sql = "SELECT * FROM defective_products WHERE id = :id ";

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
    <title>Search Page</title>
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
    <h2 class="mb-4">Name: <?php echo htmlspecialchars($product['product_name']); ?></h2>
        
        <div class="card p-4 shadow-lg">
            <h4 class="mb-3">General Information</h4>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['product_category'] ?? ''); ?></p>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand'] ?? ''); ?></p>
            <p><strong>Model/Type Number:</strong> <?php echo htmlspecialchars($product['model_type_number'] ?? ''); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['product_description'] ?? ''); ?></p>
            <p><strong>Production Dates:</strong> <?php echo htmlspecialchars($product['production_dates'] ?? ''); ?></p>
            <p><strong>Country of Origin:</strong> <?php echo htmlspecialchars($product['country_of_origin'] ?? ''); ?></p>
            <p><strong>Notifying Country:</strong> <?php echo htmlspecialchars($product['notifying_country'] ?? ''); ?></p>
            <p><strong>Alert Number:</strong> <?php echo htmlspecialchars($product['alert_number'] ?? ''); ?></p>
            <p><strong>Type of Alert:</strong> <?php echo htmlspecialchars($product['type_of_alert'] ?? ''); ?></p>
            <p><strong>Alert Type:</strong> <?php echo htmlspecialchars($product['alert_type'] ?? ''); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($product['type'] ?? ''); ?></p>
            <p><strong>Alert Submitted By:</strong> <?php echo htmlspecialchars($product['alert_submitted_by'] ?? ''); ?></p>
            <p><strong>Counterfeit:</strong> <?php echo $product['counterfeit'] ? 'Yes' : 'No'; ?></p>
            <hr>
            <h4 class="mb-3">Risk Information</h4>
            <p><strong>Risk Type: </strong> <?php echo htmlspecialchars($product['risk_type'] ?? ''); ?></p>
            <p><strong>Hazard Type: </strong> <?php echo htmlspecialchars($product['hazard_type'] ?? ''); ?></p>
            <p><strong>Hazard Causes: </strong> <?php echo htmlspecialchars($product['hazard_causes'] ?? ''); ?></p>
            <p><strong>Risk Description: </strong> <?php echo htmlspecialchars($product['risk_description'] ?? ''); ?></p>
            <p><strong>Risk Legal Provision: </strong> <?php echo htmlspecialchars($product['risk_legal_provision'] ?? ''); ?></p>
            
            <hr>
            <h4 class="mb-3">Measures Taken</h4>
            <p><strong>Measures Operators:</strong> <?php echo htmlspecialchars($product['measures_operators'] ?? ''); ?></p>
            <p><strong>Measures Authorities:</strong> <?php echo htmlspecialchars($product['measures_authorities'] ?? ''); ?></p>
            <p><strong>Compulsory Measures:</strong> <?php echo htmlspecialchars($product['compulsory_measures'] ?? ''); ?></p>
            <p><strong>Voluntary Measures:</strong> <?php echo htmlspecialchars($product['voluntary_measures'] ?? ''); ?></p>
            <p><strong>Found and Measures Taken In:</strong> <?php echo htmlspecialchars($product['found_and_measures_taken_in'] ?? ''); ?></p>

            <hr>
            <h4 class="mb-3">Additional Details</h4>
            <p><strong>OECD Portal Category:</strong> <?php echo htmlspecialchars($product['oecd_portal_category'] ?? ''); ?></p>
            <p><strong>Recall Code:</strong> <?php echo htmlspecialchars($product['recall_code'] ?? ''); ?></p>
            <p><strong>Company Recall Code:</strong> <?php echo htmlspecialchars($product['company_recall_code'] ?? ''); ?></p>
            <p><strong>Company Recall Page:</strong> <a href="<?php echo htmlspecialchars($product['company_recall_page'] ?? ''); ?>" target="_blank">Link</a></p>
            <p><strong>Case URL:</strong> <a href="<?php echo htmlspecialchars($product['case_url'] ?? ''); ?>" target="_blank">Link</a></p>
            <p><strong>Barcode:</strong> <?php echo htmlspecialchars($product['barcode'] ?? ''); ?></p>
            <p><strong>Batch Number:</strong> <?php echo htmlspecialchars($product['batch_number'] ?? ''); ?></p>
            <p><strong>Published On:</strong> <?php echo htmlspecialchars($product['published_on'] ?? ''); ?></p>

            <?php if (!empty($product['images'])): ?>
                <hr>
                <h4 class="mb-3">Images</h4>
                <img src="<?php echo htmlspecialchars($product['images'] ?? ''); ?>" class="img-fluid" alt="Product Image">
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include 'login_signup_popup_widget.html'; ?>

<script src="scripts.js"></script>

</body>
</html>

