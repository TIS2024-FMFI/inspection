<?php
session_start();

$client = require __DIR__ . '/../../googleconfig.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Get user info
        $oauth2 = new Google\Service\Oauth2($client);
        $googleUser = $oauth2->userinfo->get();

        // Example: set session as if they're "logged in"
        $_SESSION['user_id']   = 'google_' . $googleUser->id; // or however you track the user
        $_SESSION['username']  = $googleUser->name;
        $_SESSION['email']     = $googleUser->email;
        $_SESSION['role']      = 'user'; // or fetch from DB if you want a more advanced setup

        // Redirect to welcome page
        header('Location: /welcome.php');
        exit;
    } else {
        echo "Error with token: " . $token['error'];
    }
} else {
    echo "No code found in URL.";
}
