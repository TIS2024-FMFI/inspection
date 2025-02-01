<?php
session_start();

// Load the Google client configuration
$client = require __DIR__ . '/../../googleconfig.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Get user info from Google
        $oauth2    = new Google\Service\Oauth2($client);
        $googleUser = $oauth2->userinfo->get();

        // --- Begin DB logic for Google users ---
        try {
            // Set DB configuration using environment variables or defaults
            $host        = getenv('DB_HOST') ?: 'localhost';
            $dbname      = getenv('DB_NAME') ?: 'safety_app';
            $username_db = getenv('DB_USER') ?: 'safety_app_user';
            $password_db = getenv('DB_PASSWORD') ?: 'safety_app_password';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $username_db, $password_db, $options);

            // Check if the user already exists using the Google email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $googleUser->email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Generate a username (using the part of the email before '@')
                $username = substr($googleUser->email, 0, strpos($googleUser->email, '@'));

                // Insert the new user record (adjust columns if needed)
                $insertStmt = $pdo->prepare("INSERT INTO users (email, role, username) VALUES (:email, :role, :username)");
                $insertStmt->execute([
                    'email'    => $googleUser->email,
                    'role'     => 'user',
                    'username' => $username
                ]);

                // Get the numeric ID of the inserted user
                $userId = $pdo->lastInsertId();
            } else {
                // Use the existing user record's numeric ID
                $userId = $user['id'];
            }

            // Now store the numeric user ID and other info in the session
            $_SESSION['user_id']  = $userId;
            $_SESSION['username'] = $googleUser->name;
            $_SESSION['email']    = $googleUser->email;
            $_SESSION['role']     = 'user';

        } catch (PDOException $e) {
            error_log("Database error during Google login: " . $e->getMessage());
            echo "An error occurred while logging in. Please try again later.";
            exit;
        }
        // --- End DB logic for Google users ---

        // Redirect to welcome page
        header('Location: /welcome.php');
        exit;
    } else {
        echo "Error with token: " . $token['error'];
    }
} else {
    echo "No code found in URL.";
}
