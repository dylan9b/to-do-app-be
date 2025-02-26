<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Google\Client as GoogleClient;

class AuthController extends ResourceController
{
    protected $helpers = ['url', 'form'];
    protected $db; // Database connection
    private $secretKey;

    // Constructor to load necessary services
    public function __construct()
    {
        // Load the database connection
        $this->db = db_connect(); // Initialize the database connection

        $this->secretKey = config('Config')->secretKey;
    }


    // Login Method (POST request)
    public function login()
    {
        // Get JSON input 
        // data
        $data = $this->request->getJSON();

        // Validate input
        if (!isset($data->email) || !isset($data->password)) {
            return $this->failValidationErrors("Invalid input. Email and password are required.");
        }

        $email = $data->email;
        $password = $data->password;

        // Validate email format
        if (!filter_var($email, filter: FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors("Invalid email format.");
        }

        // Load database
        // $db = Database::connect();
        $builder = $this->db->table('USERS');
        $builder->where('email', $email);
        $userQuery = $builder->get();

        if ($userQuery->getNumRows() === 0) {
            return $this->failUnauthorized("Invalid email or password.");
        }

        // Fetch the user record
        $user = $userQuery->getRow();
        $hashedPassword = $user->password;
        $userId = $user->id;
        $roleId = $user->roleId;

        // Verify password
        if (!password_verify($password, $hashedPassword)) {
            return $this->failUnauthorized("Invalid email or password.");
        }


        // Access Token (short-lived)
        $accessPayload = [
            "userId" => $userId,
            "email" => $email,
            "roleId" => $roleId,
            "iat" => time(),
            "exp" => time() + (60 * 60), // Access token expires in 1 hour
        ];
        $accessToken = JWT::encode($accessPayload, $this->secretKey, 'HS256');

        // Refresh Token (long-lived)
        $refreshPayload = [
            "userId" => $userId,
            "email" => $email,
            "roleId" => $roleId,
            "iat" => time(),
            "exp" => time() + (60 * 60 * 24 * 7), // Refresh token expires in 7 days
        ];
        $refreshToken = JWT::encode($refreshPayload, $this->secretKey, 'HS256');

        // Prepare the expiry date for the access token
        $expiryDate = date("Y-m-d H:i:s", $accessPayload['exp']);

        // Send the successful login response with tokens
        return $this->respond([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
                'expiryDate' => $expiryDate,
            ]
        ], 200);
    }

    public function register()
    {
        // Get JSON input data
        $data = $this->request->getJSON();

        // Validate input
        if (!isset($data->email) || !isset($data->password)) {
            return $this->failValidationErrors("Invalid input. Email and password are required.");
        }

        $email = $data->email;
        $password = $data->password;

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors("Invalid email format.");
        }

        // Check if email already exists
        $builder = $this->db->table('USERS');
        $builder->where('email', $email);
        $userQuery = $builder->get();

        if ($userQuery->getNumRows() > 0) {
            return $this->failValidationErrors("Email already exists.");
        }

        // Check if the 'USER' role exists in the roles table
        $roleBuilder = $this->db->table('ROLES');
        $roleBuilder->where('role', 'USER');
        $roleResult = $roleBuilder->get()->getRow();

        if (!$roleResult) {
            return $this->failValidationErrors("Role 'USER' not found.");
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into the USERS table
        $dataToInsert = [
            'email' => $email,
            'password' => $hashedPassword,
            'roleId' => $roleResult->id
        ];

        $insertBuilder = $this->db->table('USERS');
        $insertResult = $insertBuilder->insert($dataToInsert);

        if ($insertResult) {
            return $this->respond([
                'success' => true,
                'message' => 'Registration successful.'
            ], 201);
        }

        return $this->failServerError('Error inserting user into database.');
    }

    public function loginGoogle()
    {
        $googleClientID = config('Config')->googleClientID;

        if ($this->request->getMethod() === 'options') {
            return $this->respond(null, 200);
        }

        log_message('error', 'request method: ' . $this->request->getMethod());

        // Handle POST requests
        if ($this->request->getMethod() === 'POST') {
            $data = json_decode($this->request->getBody(), true);

            if (isset($data['tokenId'])) {
                $idToken = $data['tokenId'];

                // Initialize Google Client
                $client = new GoogleClient();
                $client->setClientId($googleClientID);

                try {
                    // Verify the ID token with Google
                    $ticket = $client->verifyIdToken($idToken);

                    if ($ticket) {
                        $googleData = $ticket;
                        $email = $googleData['email'];

                        // Check if the email exists in the database
                        $builder = $this->db->table('USERS');
                        $user = $builder->where('email', $email)->limit(1)->get()->getRowArray();

                        if ($user) {
                            // User exists, generate JWT token
                            $userId = $user['id'];
                            $roleId = $user['roleId'];

                            $accessPayload = [
                                "userId" => $userId,
                                "email" => $email,
                                "roleId" => $roleId,
                                "iat" => time(),
                                "exp" => time() + (60 * 60), // Token expires in 1 hour
                            ];

                            $accessToken = JWT::encode($accessPayload, $this->secretKey, 'HS256');
                            $expiryDate = date("Y-m-d H:i:s", $accessPayload['exp']);

                            return $this->respond([
                                "success" => true,
                                "message" => "Login successful.",
                                "data" => [
                                    "accessToken" => $accessToken,
                                    "expiryDate" => $expiryDate,
                                ]
                            ], 200);
                        } else {
                            // User does not exist, create new user
                            $roleBuilder = $this->db->table('ROLES');
                            $role = $roleBuilder->where('role', 'USER')->limit(1)->get()->getRowArray();

                            if ($role) {
                                $roleId = $role['id'];

                                // Generate random password and hash it
                                $randomPassword = bin2hex(random_bytes(15 / 2));
                                $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);

                                $newUserId = bin2hex(random_bytes(16)); // Generate a new unique userId
                                $userData = [
                                    'id' => $newUserId,
                                    'email' => $email,
                                    'password' => $hashedPassword,
                                    'roleId' => $roleId
                                ];

                                $this->db->table('USERS')->insert($userData);

                                // Generate JWT token
                                $accessPayload = [
                                    "userId" => $newUserId,
                                    "email" => $email,
                                    "roleId" => $roleId,
                                    "iat" => time(),
                                    "exp" => time() + (60 * 60), // Token expires in 1 hour
                                ];

                                $accessToken = JWT::encode($accessPayload, $this->secretKey, 'HS256');
                                $expiryDate = date("Y-m-d H:i:s", $accessPayload['exp']);

                                return $this->respond([
                                    "success" => true,
                                    "message" => "Login with Google was successful.",
                                    "data" => [
                                        "accessToken" => $accessToken,
                                        "expiryDate" => $expiryDate,
                                    ]
                                ], 200);
                            } else {
                                return $this->failNotFound("Role 'USER' not found.");
                            }
                        }
                    } else {
                        return $this->failUnauthorized("Invalid Google ID token.");
                    }
                } catch (\Exception $e) {
                    return $this->failServerError("Error verifying token: " . $e->getMessage());
                }
            } else {
                return $this->failValidationErrors("tokenId is required.");
            }
        }

        return $this->fail("Method not allowed.", 405);
    }
}
