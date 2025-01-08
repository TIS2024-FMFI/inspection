<?php
global $pdo;
session_start();
require 'db/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из POST
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Проверка заполненности полей
    if (empty($email) || empty($password)) {
        $response['error'] = "Please fill in all fields.";
        echo json_encode($response);
        exit;
    }

    try {
        // Поиск пользователя по email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Проверка пароля
            if (password_verify($password, $user['password_hash'])) {
                // Установка данных сессии при успешном входе
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


<!--
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li class="active"><a href="#">Log In</a></li>
        <li><a href="register.php">Sign Up</a></li>
    </ul>

    <form method="POST" action="login.php" class="auth-form">

        /*
        // <?php if (!empty($error)): ?>
        //    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        // <?php endif; ?>
        */

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address..." required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password..." required>
        </div>
    <button type="submit" class="auth-button">Log In</button>
</form>

</div>
</body>
</html>
-->