<?php

// Make sure you have installed the firebase/php-jwt package, or use any other JWT library
use \Firebase\JWT\JWT;

function verifyJwtToken($jwt)
{
    list($header, $payload, $signature) = explode('.', $jwt);

// Decode the base64url-encoded payload
$decodedPayload = base64_decode(strtr($payload, '-_', '+/'));
$decodedPayload = json_decode($decodedPayload, true);

// Log the decoded payload to inspect
log_message('error', 'Decoded JWT Payload: ' . json_encode($decodedPayload));

    $secretKey = 'your_secret_key';  // Ensure this is the correct secret key used to sign the JWT

    try {
        // Decode the JWT token using HS256 algorithm and skip processing 'kid'
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

        // Log successful decoding
        log_message('info', 'JWT decoded successfully');

        // Return the decoded token as an associative array
        return (array) $decoded;
    } catch (Exception $e) {
        // Log the error if decoding fails
        log_message('error', 'Error decoding JWT: ' . $e->getMessage());

        // Return null if decoding fails
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
