<?php
require_once 'db\config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM defective_products WHERE barcode = :barcode");
    $barcode = $_GET['barcode'] ?? '';
    $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        echo json_encode($results); // Vráť výsledky ako JSON
    } else {
        http_response_code(404); // Set HTTP status code to 404
        echo json_encode(['error' => 'Produkt nebol nájdený.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Set HTTP status code to 500 for server errors
    echo json_encode(['error' => 'Chyba pri získavaní údajov: ' . $e->getMessage()]);
}
