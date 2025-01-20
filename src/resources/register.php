<?php
session_start();
$error = ""; // Default error message
header('Content-Type: application/json');
$response = ['success' => false, 'error' => '', 'debug' => []];

$response['debug']['memory_limit'] = "Memory limit: " . ini_get('memory_limit');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $response['debug']['post_data'] = $_POST;

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $response['debug']['hashed_password_immediately'] = $hashedPassword;

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

            if (!$pdo) {
                $response['debug']['db_connection'] = "Database connection failed.";
                throw new Exception("Database connection is null.");
            } else {
                $response['debug']['db_connection'] = "Database connection successful.";
            }

            $response['debug']['email_check'] = $email;

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            $response['debug']['email_exists'] = $stmt->rowCount() > 0 ? "Email already exists." : "Email not found in database.";

            if ($stmt->rowCount() > 0) {
                $response['error'] = "Email is already registered.";
            } else {
                $username = substr($email, 0, strpos($email, '@'));
                $response['debug']['generated_username'] = $username;

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

                if ($insert_success) {
                    $response['debug']['insert_status'] = "User inserted successfully.";
                    $response['success'] = true;
                    $response['error'] = 'Register successful! Please log in.';
                } else {
                    $response['debug']['insert_error'] = $stmt->errorInfo();
                }
            }

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $response['debug']['user_from_db'] = $user ?: "User not found in database after insert.";
        } catch (PDOException $e) {
            $response['error'] = "Database error: " . $e->getMessage();
            $response['debug']['db_error'] = $e->getMessage();
        } catch (Exception $e) {
            $response['error'] = "An unexpected error occurred.";
            $response['debug']['general_error'] = $e->getMessage();
        }
    }

    $response['debug']['final_response'] = $response;

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
?>