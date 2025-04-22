<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class PriorityController extends ResourceController
{
    protected $modelName = 'App\Models\PriorityModel'; // Use the PriorityModel
    protected $format = 'json'; // Set the response format to JSON

    public function create()
    {

        // Data to be inserted (only include 'priority')
        $data = [
            'priority' => 'delete',
        ];

        // Insert the record into the database
        $id = $this->model->insert($data, true); // Passing true to return the inserted ID

        if ($id) {
            // Retrieve the newly inserted record using the generated ID
            $newPriority = $this->model->find($id);  // Find the inserted record using its ID

            return json_encode([
                'success' => true,
                'message' => 'Priority inserted successfully',
                'data' => $newPriority // Returning the newly created instance
            ]);
        } else {
            return json_encode([
                'message' => 'Failed to insert priority'
            ]);
        }
    }


    public function index()
    {
        // Skip authorization check for OPTIONS requests
        if ($this->request->getMethod() !== 'options') {
            // Get the JWT token from the Authorization header using the helper function
            $jwt = getBearerToken();

            if ($jwt) {
                // Verify the JWT token using the helper function
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    // Get the data from the body (JSON)
                    $jsonInput = $this->request->getJSON();

                    // Check if the body is present and if 'id' is set
                    $id = isset($jsonInput->id) ? $jsonInput->id : null;

                    // Fetch priorities using the model
                    $priorities = $this->model->getPriorities($id);

                    // Return the result as a JSON response
                    return $this->respond($priorities);
                } else {
                    // Token is invalid or expired
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                // No token provided
                return $this->failUnauthorized('Authorization token missing');
            }
        }

        return $this->fail('Method not allowed');
    }
}
