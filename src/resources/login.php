<?php
global $pdo;
session_start();
require 'db/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $response['error'] = "Please fill in all fields.";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                $response['success'] = true;
            } else {
                $response['error'] = "The password you entered is incorrect. Please try again.";
            }
        } else {
            $response['error'] = "No account found with this email address.";
        }
    } catch (PDOException $e) {
        $response['error'] = "Database error: " . $e->getMessage();
    }
} else {
    $response['error'] = "Invalid request method.";
}

echo json_encode($response);
exit;
?>