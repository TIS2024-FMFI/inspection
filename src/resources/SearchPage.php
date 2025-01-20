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
$searchQuery = '';
$results = [];
$sort = '';

function hasInvalidCharacters($input) {
    return preg_match('/[<>{};]/', $input);
}

if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'name_asc';

    if (preg_match('/[<>{};]/', $searchQuery)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showError('Invalid input detected. Please avoid using special characters.');
                });
              </script>";
    } elseif (!empty($searchQuery)) {
        try {
            $sql = "SELECT id, product_name, published_on, hazard_causes, images FROM defective_products WHERE product_name LIKE :search";

            if ($sort == 'name_asc') {
                $sql .= " ORDER BY product_name ASC";
            } elseif ($sort == 'name_desc') {
                $sql .= " ORDER BY product_name DESC";
            }

            $stmt = $connect->prepare($sql);
            $stmt->execute(['search' => "%$searchQuery%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage());
        }
    } elseif (empty($searchQuery)) {
        try {
            $sql = "SELECT id, product_name, published_on, hazard_causes, images FROM defective_products";

            if ($sort == 'name_asc') {
                $sql .= " ORDER BY product_name ASC";
            } elseif ($sort == 'name_desc') {
                $sql .= " ORDER BY product_name DESC";
            }

            $stmt = $connect->prepare($sql);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Query error: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="search_page_style.css">
    <title>Search Page</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <div class="homepage-container">
        <h2 class="homepage-title">Search</h2>
        <?php if ($isLoggedIn): ?>
            <img src="images/profile-pic.png" alt="Profile Picture" class="profile-pic-mobile" onclick="toggleProfileMenu()">
        <?php endif; ?>
    </div>
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
    <form class="d-flex mt-3 md-5 justify-content-between align-items-center gap-2 px-3" role="search" method="GET" action="SearchPage.php">
        <?php
            echo "<h4 class='no-wrap'>Search Results for: </h4>";
        ?>

        <input type="text" id="search-input" name="search" class="form-control" placeholder="Search Products" aria-label="Search" aria-describedby="button-addon2" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>

        <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Sort By
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name_asc'])); ?>" 
                        <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'aria-current="true"' : ''; ?>>
                            By name A-Z
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name_desc'])); ?>" 
                        <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'aria-current="true"' : ''; ?>>
                            By name Z-A
                        </a>
                    </li>
                </ul>
            </div>
    </form>

    <div id="error-popup" class="error-popup" style="display: none;">
        <p id="error-message"></p>
        <button class="close-btn" onclick="closeErrorPopup()">Close</button>
    </div>


    <?php 
        if (!empty($results)) {
            echo '<div class="container-fluid my-5">';
            echo '<div class="row row-cols-1 row-cols-md-3 g-4 justify-content-start">';
            foreach ($results as $product) {
                $imageSrc = ($product['images'] !== NULL) ? $product['images'] : 'images/No_Image_Available.jpg';
                
                echo <<<HTML
                        <div class="card my-2 ms-4" style="width: 20.5rem; height: 32rem; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); ">
                        <img src="{$imageSrc}" class="card-img-top" alt="..." >
                            <div class="card-body">
                                <h5 class="card-title" style="margin-bottom: 10px;">{$product['product_name']} </h5>
                                <p class="card-text" style="margin-bottom: 5px;"><strong>Reported date: </strong>{$product['published_on']} </p>
                                <p class="card-text" style="margin-bottom: 5px;"><strong>Hazard Causes: </strong>{$product['hazard_causes']} </p>
                            
                                <a href="ProductPage.php?id={$product['id']}" class="card-link">See details</a>
                            </div>
                        </div>
                HTML;
            }
        } else if (!empty($searchQuery)) {
            echo "<div class='container mt-4 ms-4'> <p> Product not found </p> </div>";
        }
    ?>

</main>

<?php include 'login_signup_popup_widget.html'; ?>

<script src="scripts.js"></script>
</body>
</html>