<?php

namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\RESTful\ResourceController;

class RoleController extends ResourceController
{
    protected $modelName = 'App\Models\RoleModel'; // Link the RoleModel
    protected $format    = 'json'; // Set the response format to JSON

    public function index()
    {
        // Skip authorization check for OPTIONS requests
        if ($this->request->getMethod() !== 'options') {
            // Get the JWT token from the Authorization header using the helper function
            $jwt = getBearerToken();

            if ($jwt) {
                // Verify the JWT token using the helper functio
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    // Get the data from the body (JSON)
                    $jsonInput = $this->request->getJSON();

                    // Check if the body is present and if 'id' is set
                    $id = isset($jsonInput->id) ? $jsonInput->id : null;

                    // Fetch roles using the model
                    $roles = $this->model->getRoles($id);

                    // Return the result as a JSON response
                    return $this->respond($roles);
                } else {
                    // Token is invalid or expired
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                // No token provided
                return $this->failUnauthorized('Authorization token missing');
            }
        }

        return $this->failMethod('Method not allowed');
    }
}
