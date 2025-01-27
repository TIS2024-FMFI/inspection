<?php
require __DIR__ . '/vendor/autoload.php';

$client = new Google\Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('https://opensciencedata.eu/auth/google/callback');
$client->addScope(['email','profile']);

return $client;
