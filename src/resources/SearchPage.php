<?php
// Connect to database
$host = 'localhost';
$dbname = 'safety_app';
$user = 'root'; 
$password = ''; 

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

if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $sort = trim($_GET['sort']);
    
    if (!empty($searchQuery)) {
        try {
            $sql = "SELECT id, product_name, published_on, hazard_causes, images FROM defective_products WHERE product_name LIKE :search";
            // Sort
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
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Search Page</title>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<main>
    <form class="d-flex mt-3 md-5 justify-content-between align-items-center gap-2 px-3" role="search" method="GET" action="SearchPage.php">

            <input type="text" name="search" class="form-control" placeholder="Search Products" aria-label="Search" aria-describedby="button-addon2" value=" <?php if (isset($_GET['search'])) { echo htmlspecialchars($searchQuery); } else { echo ''; } ?>">
            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
            
            <select name="sort" class="btn btn-outline-secondary" onchange="this.form.submit()">
                <option value=""  >Sort By</option>
                <option value="name_asc"  <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'selected' : ''; ?>>By name A-Z</option>
                <option value="name_desc"  <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'selected' : ''; ?>>By name Z-A</option>
            </select>
    </form>


    <?php 
        if (!empty($searchQuery)) {
            echo "<h4 class='mt-4 ms-4'>Search Results for: " . htmlspecialchars($searchQuery) . "</h4>";
        }
        if (!empty($results)) {
            echo '<div class="container my-4">';
            echo '<div class="row row-cols-1 row-cols-md-3 g-4 justify-content-start">';
            foreach ($results as $product) {
                echo <<<HTML
                        <div class="card my-2 ms-4" style="width: 18rem;">
                            <img src="..." class="card-img-top" alt="...">

                            <div class="card-body">
                                <h5 class="card-title">{$product['product_name']} </h5>
                                <p class="card-text">Reported date: {$product['published_on']} </p>
                                <p class="card-text">Hazard Causes: {$product['hazard_causes']} </p>
                            </div>

                            <div class="card-body">
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

</body>
</html>