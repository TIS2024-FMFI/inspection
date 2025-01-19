<?php
// Database connection
$host = 'localhost';
$dbname = 'safety_app';
$username_db = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

try {
    $barcode = $_GET['barcode'] ?? '';


    $stmt = $pdo->prepare("SELECT * FROM defective_products WHERE barcode = :barcode");
    $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
    $stmt->execute();
    $defectiveResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($defectiveResults) {
        echo json_encode(['status' => 'defective', 'data' => $defectiveResults]);
        exit;
    }


    $stmt = $pdo->prepare("SELECT * FROM user_submitted_products WHERE barcode = :barcode");
    $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
    $stmt->execute();
    $personalizedResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($personalizedResults) {
        echo json_encode(['status' => 'exists_in_personalized', 'message' => 'Product is not defective and already in personalized list.']);
        exit;
    }

    echo json_encode(['status' => 'not_found', 'message' => 'Produkt nebol nájdený.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Chyba pri získavaní údajov: ' . $e->getMessage()]);
}
