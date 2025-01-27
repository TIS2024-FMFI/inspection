<?php
// File: /var/www/html/resources/googlelogin.php

session_start();

// 1. Load the configured Google client
$client = require __DIR__ . '/googleconfig.php';

// 2. Generate the Google OAuth URL
$authUrl = $client->createAuthUrl();

// 3. Redirect the user to Google
header('Location: ' . $authUrl);
exit;
