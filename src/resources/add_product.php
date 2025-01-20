<?php
session_start();
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

$barcode = $_POST['barcode'] ?? null;
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$brand = $_POST['brand'] ?? null;
$uid = $_SESSION['user_id'];


if (!$barcode || !$name) {
    http_response_code(400);
    echo 'Barcode and name are required.';
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO user_submitted_products (name, user_id, barcode, product_description, brand) VALUES (:name, :user_id, :barcode, :description, :brand)');
    $stmt->execute([
        ':barcode' => $barcode,
        'user_id' => $uid,
        ':name' => $name,
        ':description' => $description,
        ':brand' => $brand
    ]);
    echo $_SESSION['user_id'];
    echo 'Product added successfully.';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Error adding product: ' . $e->getMessage();
}
