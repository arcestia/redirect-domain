<?php

require_once 'config.php';

function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Get random domain from the array
$randomDomain = $domains[array_rand($domains)];

// Generate random subdomain
$randomSubdomain = generateRandomString($subdomainLength);

// Construct the full URL
// $redirectUrl = "https://" . $randomSubdomain . "." . $randomDomain;
$redirectUrl = "https://".$randomDomain;

// Perform the redirect
header("Location: " . $redirectUrl);
exit();
