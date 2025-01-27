<?php
// reset_password.php

session_start();

define('DB_HOST', getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost');
define('DB_NAME', getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'safety_app');
define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : 'safety_app_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'safety_app_password'); // **Use environment variables in production**
define('PASSWORD_MIN_LENGTH', 8); // Minimum password length


// Function to connect to the database using PDO
function getDBConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // In production, avoid displaying sensitive error details
        die("Database connection failed: " . htmlspecialchars($e->getMessage()));
    }
}

// Function to validate the token
function validateToken($pdo, $token) {
    $sql = "SELECT prt.id, prt.user_id, prt.expires_at, u.email 
            FROM password_reset_tokens prt 
            JOIN users u ON prt.user_id = u.id 
            WHERE prt.token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $token]);
    $result = $stmt->fetch();

    if ($result) {
        // Check if token has expired
        $current_time = new DateTime();
        $expires_at = new DateTime($result['expires_at']);
        if ($current_time > $expires_at) {
            return ['valid' => false, 'message' => 'The reset link has expired. Please request a new one.'];
        }
        return ['valid' => true, 'user_id' => $result['user_id'], 'token_id' => $result['id'], 'email' => $result['email']];
    } else {
        return ['valid' => false, 'message' => 'Invalid reset token.'];
    }
}

// Function to update the user's password
function updatePassword($pdo, $user_id, $new_password) {
    // Hash the new password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the users table
    $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'password_hash' => $password_hash,
        'user_id' => $user_id
    ]);
}

// Function to invalidate the token
function invalidateToken($pdo, $token_id) {
    $sql = "DELETE FROM password_reset_tokens WHERE id = :token_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token_id' => $token_id]);
}

// Function to display the form
function displayForm($token, $error = '') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Password</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
            .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 5px; }
            .error { color: red; }
            .success { color: green; }
            input[type="password"], input[type="submit"] {
                width: 100%;
                padding: 10px;
                margin: 5px 0 15px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }
            input[type="submit"] {
                background-color: #5cb85c;
                color: white;
                border: none;
                cursor: pointer;
            }
            input[type="submit"]:hover {
                background-color: #4cae4c;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Reset Your Password</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="reset_password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                
                <input type="submit" value="Reset Password">
            </form>
        </div>
    </body>
    </html>
    <?php
}

// Function to display success message
function displaySuccess() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Password Reset Successful</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
            .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 5px; text-align: center; }
            .success { color: green; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2 class="success">Your password has been successfully reset.</h2>
            <p>You can now <a href="index.php">log in</a> with your new password.</p>
        </div>
    </body>
    </html>
    <?php
}

// Main Logic
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request - display the form
    if (!isset($_GET['token'])) {
        die("Invalid request.");
    }

    $token = $_GET['token'];

    // Validate the token
    $validation = validateToken($pdo, $token);
    if ($validation['valid']) {
        displayForm($token);
    } else {
        die(htmlspecialchars($validation['message']));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request - process the form submission
    if (!isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
        die("Invalid form submission.");
    }

    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the token again
    $validation = validateToken($pdo, $token);
    if (!$validation['valid']) {
        die(htmlspecialchars($validation['message']));
    }

    // Validate password
    if ($new_password !== $confirm_password) {
        displayForm($token, "Passwords do not match.");
        exit();
    }

    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        displayForm($token, "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.");
        exit();
    }

    // Optionally, add more password strength validations here

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Update the user's password
        updatePassword($pdo, $validation['user_id'], $new_password);

        // Invalidate the token
        invalidateToken($pdo, $validation['token_id']);

        // Commit transaction
        $pdo->commit();

        // Optionally, log the password reset event
        // $_SESSION['user_id'] = $validation['user_id'];
        // ... other logging mechanisms

        // Display success message
        displaySuccess();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        // In production, avoid displaying sensitive error details
        displayForm($token, "An error occurred while resetting your password. Please try again.");
    }
} else {
    // Unsupported HTTP method
    http_response_code(405); // Method Not Allowed
    header('Allow: GET, POST');
    echo "Method not allowed.";
}
?>
