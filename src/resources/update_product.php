<?php
session_start();
require_once 'db/config.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'], $data['name'], $data['brand'], $data['description'])) {
        $stmt = $pdo->prepare('UPDATE user_submitted_products SET name = ?, brand = ?, product_description = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['brand'], $data['description'], $data['id']]);

        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
