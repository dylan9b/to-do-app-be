<?php

namespace App\Controllers;

use App\Models\PriorityModel;
use CodeIgniter\RESTful\ResourceController;

class PriorityController extends ResourceController
{
    protected $modelName = 'App\Models\PriorityModel'; // Use the PriorityModel
    protected $format    = 'json'; // Set the response format to JSON

    public function __construct()
    {
        // Load the JWT helper for token verification
        helper('jwt');
    }

    // Get priorities based on an optional id
    public function index($id = null)
    {
        // Check the request method is not OPTIONS (we skip authorization for OPTIONS)
        if ($this->request->getMethod() !== 'options') {
            // Get the JWT token from the Authorization header
            $jwt = $this->getBearerToken();

            if ($jwt) {
                // Verify the JWT token
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    // Fetch priorities using the model
                    $priorities = $this->model->getPriorities($id);

                    // Return the result as a JSON response
                    return $this->respond($priorities);
                } else {
                    // Invalid or expired token
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                // No token provided
                return $this->failUnauthorized('Authorization token missing');
            }
        }

        return $this->failMethod('Method not allowed');
    }

    // Helper method to retrieve the bearer token from the Authorization header
    private function getBearerToken()
    {
        $authorizationHeader = $this->request->getHeader('Authorization');
        if ($authorizationHeader) {
            $header = $authorizationHeader->getValue();
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
