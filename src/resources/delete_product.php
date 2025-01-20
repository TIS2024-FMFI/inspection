<?php
session_start();
require_once 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['id'])) {
        $productId = $input['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM user_submitted_products WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                ':id' => $productId,
                ':user_id' => $_SESSION['user_id']
            ]);

            if ($stmt->rowCount() > 0) {
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
?>
