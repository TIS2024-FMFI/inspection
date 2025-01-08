<?php
session_start();
$error = ""; // Default error message
header('Content-Type: application/json');
$response = ['success' => false, 'error' => '', 'debug' => []]; // Добавлено поле debug для отладочной информации

// Вывод текущего лимита памяти
$response['debug']['memory_limit'] = "Memory limit: " . ini_get('memory_limit');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Отладка для POST данных
    $response['debug']['post_data'] = $_POST;

    // Хэширование пароля в начале
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $response['debug']['hashed_password_immediately'] = $hashedPassword; // Добавлено сразу после хэширования

        // Проверка, что хэширование прошло успешно
        if (empty($hashedPassword)) {
            $response['error'] = "An error occurred while hashing the password.";
            $response['debug']['hash_error'] = "Hashing failed immediately: Empty hashed password.";
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
    } else {
        $response['error'] = "Password is empty.";
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    // Validation checks
    $response['debug']['password_before_validation'] = $password;
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $response['error'] = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email address.";
    } elseif (strlen($password) < 8) {
        $response['error'] = "Password must be at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $response['error'] = "Passwords do not match.";
    } else {
        try {
            require 'db/config.php';

            // Проверка подключения к базе данных
            if (!$pdo) {
                $response['debug']['db_connection'] = "Database connection failed.";
                throw new Exception("Database connection is null.");
            } else {
                $response['debug']['db_connection'] = "Database connection successful.";
            }

            // Отладка: проверка email
            $response['debug']['email_check'] = $email;

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            // Отладка: проверка существования email
            $response['debug']['email_exists'] = $stmt->rowCount() > 0 ? "Email already exists." : "Email not found in database.";

            if ($stmt->rowCount() > 0) {
                $response['error'] = "Email is already registered.";
            } else {
                // Создание имени пользователя
                $username = substr($email, 0, strpos($email, '@'));
                $response['debug']['generated_username'] = $username;

                // Отладка: SQL запрос
                $response['debug']['sql_preparation'] = [
                    'query' => "INSERT INTO users (email, password_hash, role, username) VALUES (:email, :password_hash, 1, :username)",
                    'parameters' => [
                        'email' => $email,
                        'password_hash' => $hashedPassword,
                        'username' => $username
                    ]
                ];

                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, username) VALUES (:email, :password_hash, 1, :username)");
                $insert_success = $stmt->execute([
                    'email' => $email,
                    'password_hash' => $hashedPassword,
                    'username' => $username
                ]);

                // Отладка: результат вставки
                if ($insert_success) {
                    $response['debug']['insert_status'] = "User inserted successfully.";
                    $response['success'] = true;
                    $response['error'] = 'Register successful! Please log in.';
                } else {
                    $response['debug']['insert_error'] = $stmt->errorInfo();
                }
            }

            // Проверка, действительно ли пользователь записан
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['debug']['user_from_db'] = $user ?: "User not found in database after insert.";
        } catch (PDOException $e) {
            // Отладка: ошибка базы данных
            $response['error'] = "Database error: " . $e->getMessage();
            $response['debug']['db_error'] = $e->getMessage();
        } catch (Exception $e) {
            // Общая отладка: исключения
            $response['error'] = "An unexpected error occurred.";
            $response['debug']['general_error'] = $e->getMessage();
        }
    }

    // Финальная отладка
    $response['debug']['final_response'] = $response;

    echo json_encode($response, JSON_PRETTY_PRINT); // Вывод JSON в читаемом формате
    exit;
}
?>




<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="auth-container">
    <ul class="auth-tabs">
        <li><a href="login.php">Log In</a></li>
        <li class="active"><a href="#">Sign Up</a></li>
    </ul>

    <form method="POST" action="register.php" class="auth-form">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address..." required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password..." required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password..." required>
        </div>

        <button type="submit" class="auth-button">Create an account</button>
    </form>

    <div class="divider">OR</div>

    <button class="google-auth-btn">
        <img src="images/google-icon.png" alt="Google Icon">
        Continue with Google
    </button>
</div>
</body>
</html>
-->