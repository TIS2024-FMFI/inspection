<?php
session_start();
require_once 'db/config.php'; // Include the database configuration
header('Content-Type: application/json');

try {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'], $data['name'], $data['brand'], $data['description'])) {
        // Prepare and execute the update query using the $pdo from config.php
        $stmt = $pdo->prepare('UPDATE user_submitted_products SET name = ?, brand = ?, product_description = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['brand'], $data['description'], $data['id']]);

        echo json_encode(['success' => true]);
    } else {
        // Missing required data
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input.']);
    }
} catch (Exception $e) {
    // Handle errors (log them in production)
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
