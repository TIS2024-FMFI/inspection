<?php
session_start();
require_once 'db\config.php';


// Retrieve POST data
$barcode = $_POST['barcode'] ?? null;
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$brand = $_POST['brand'] ?? null;
$uid = $_SESSION['user_id'];


// Validate mandatory fields
if (!$barcode || !$name) {
    http_response_code(400);
    echo 'Barcode and name are required.';
    exit;
}

// Prepare and execute the SQL statement
try {
    $stmt = $pdo->prepare('INSERT INTO user_submitted_products (name, user_id, barcode, product_description, brand) VALUES (:name, :user_id, :barcode, :description, :brand)');
    $stmt->execute([
        ':barcode' => $barcode,
        'user_id' => $uid,
        ':name' => $name,
        ':description' => $description,
        ':brand' => $brand
    ]);
    // Respond with success
    echo $_SESSION['user_id'];
    echo 'Product added successfully.';
} catch (PDOException $e) {
    // Handle SQL error
    http_response_code(500);
    echo 'Error adding product: ' . $e->getMessage();
}
