<?php
session_start();
require_once 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['id'])) {
        $productId = $input['id'];

        try {
            // Fetch barcode before deleting the product
            $stmt = $pdo->prepare("SELECT barcode FROM user_submitted_products WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                ':id' => $productId,
                ':user_id' => $_SESSION['user_id']
            ]);
            $barcode = $stmt->fetchColumn();

            if (!$barcode) {
                echo json_encode(['success' => false, 'message' => 'Product not found or not authorized to delete.']);
                exit;
            }

            // Delete the product from user_submitted_products
            $stmt = $pdo->prepare("DELETE FROM user_submitted_products WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                ':id' => $productId,
                ':user_id' => $_SESSION['user_id']
            ]);

            if ($stmt->rowCount() > 0) { // If the product was successfully deleted
                // Check if product_history still contains the same barcode
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_history WHERE barcode = :barcode AND user_id = :user_id");
                $stmt->execute(['barcode' => $barcode, 'user_id' => $_SESSION['user_id']]);
                $count = $stmt->fetchColumn();

                // If there are still records in product_history with the same barcode, delete them
                if ($count > 0) {
                    $stmt = $pdo->prepare("DELETE FROM product_history WHERE barcode = :barcode AND user_id = :user_id");
                    $stmt->execute(['barcode' => $barcode, 'user_id' => $_SESSION['user_id']]);
                }

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found or not authorized to delete.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid HTTP method.']);
}
