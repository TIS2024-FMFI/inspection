<?php
// Path to the Python script
//$pythonPath = '/app/python'; // **Update this path**
$script = 'forgot_password.py';

// Check if the script exists
$scriptPath = realpath("$script");
if ($scriptPath === false || !file_exists($scriptPath)) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Python script not found."]);
    exit();
}

// Get the raw POST data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Extract the email
$email = $data['email'] ?? null;

// Basic email validation
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "A valid email is required."]);
    exit();
}

// Command to execute the Python script using shell_exec
$escapedEmail = escapeshellarg($email);
$command = "python3 $escapedEmail 2>&1"; // Redirect stderr to stdout for capturing errors

// Execute the command and capture the output
$output = shell_exec($command);

// Check if output is empty
if ($output === null) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Failed to execute the Python script."]);
    exit();
}

// Decode the JSON output from the Python script
$jsonResult = json_decode($output, true);

// Check for JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    // Optionally log the error
    error_log("JSON decode error: " . json_last_error_msg());
    error_log("Python script output: " . $output);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Invalid response from Python script."]);
    exit();
}

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($jsonResult);
?>
