<?php
require_once 'regauth_module\db\config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM defective_products WHERE barcode = :barcode");
    $barcode = $_GET['barcode'] ?? '';
    $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        echo json_encode($results); // Vráť výsledky ako JSON
    } else {
        //pridať button: add to personalized list a ak na neho klikne tak sa pridá do neho a aj do databázy
        echo json_encode(['error' => 'Produkt nebol nájdený.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Chyba pri získavaní údajov: ' . $e->getMessage()]);
}
