<?php

// Make sure you have installed the firebase/php-jwt package, or use any other JWT library
use \Firebase\JWT\JWT;

// Helper function to decode and verify the JWT token
function verifyJwtToken($jwt)
{
    log_message('jwt', 'JWT Code ' . print_r($jwt, true));

    // The secret key used for encoding and decoding the JWT token
    $secretKey = 'your_secret_key';  // Make sure to change this to a secure key

    try {
        // Decode the JWT token using the secret key
        $decoded = JWT::decode($jwt, $secretKey);

         // Debugging: print the decoded token
         log_message('debug', 'Decoded JWT: ' . print_r($decoded, true));

        // Return the decoded token as an associative array
        return (array) $decoded;
    } catch (Exception $e) {

         // If the token is invalid or expired, return null
         log_message('error', 'JWT Decode Error: ' . $e->getMessage());
        return null;
    }
}

// Helper function to extract the Bearer token from the Authorization header
function getBearerToken()
{
    $authorizationHeader = getallheaders()['Authorization'] ?? '';

    log_message('debug', ' Authorization Header: ' . $authorizationHeader);

    if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
        return $matches[1]; // Return the token if it's found
    }

    return null; // Return null if no token found
}
