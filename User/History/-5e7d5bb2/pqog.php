<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel'; // Link the UserModel
    protected $format    = 'json'; // Set the response format to JSON

    // Get users and their roles
    public function index()
    {
        if ($this->request->getMethod() !== 'options') {
            $jwt = getBearerToken();

            if ($jwt) {
                // Verify the JWT token
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    // Get the id from the request body
                    $jsonInput = $this->request->getJSON();
                    $id = isset($jsonInput->id) ? $jsonInput->id : null;

                    // Use the model to fetch the users
                    $users = $this->model->getUsers($id);

                    // Return the users data in JSON format
                    return $this->respond($users);
                } else {
                    // Token is invalid or expired
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                // No token provided
                return $this->failUnauthorized('Authorization token missing');
            }
        }
    }
}
