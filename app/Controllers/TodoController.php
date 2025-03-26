<?php

namespace App\Controllers;

use App\Models\TodoModel;
use CodeIgniter\RESTful\ResourceController;

class TodoController extends ResourceController
{
    protected $modelName = 'App\Models\TodoModel';
    protected $format = 'json';


    public function get()
    {
        if ($this->request->getMethod() !== 'options') {

            $jwt = getBearerToken();

            if ($jwt) {
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    // User info from decoded JWT
                    $userId = $decoded['userId'];
                    $roleId = $decoded['roleId'];

                    // Check if user has admin role (optional)
                    $isAdmin = $this->checkIfAdmin($roleId);

                    // Fetch JSON input data
                    $data = $this->request->getJSON();

                    // If no JSON object is sent, retrieve all todos for the user
                    if (empty((array) $data)) {
                        // Fetch all todos for the user (if not an admin)
                        $todoModel = new TodoModel();
                        $todos = $todoModel->getTodos(!$isAdmin ? $userId : null);

                        // Return todos as JSON response, ensuring an empty array is returned if no todos are found
                        return $this->respond($todos ?: []);
                    }

                    // Extract parameters from the JSON payload
                    $searchTerm = isset($data->searchTerm) ? $data->searchTerm : null;
                    $priorityId = isset($data->priorityId) ? $data->priorityId : null;
                    $isCompleted = isset($data->isCompleted) ? $data->isCompleted : null;
                    $isPinned = isset($data->isPinned) ? $data->isPinned : null;
                    $orderColumn = isset($data->orderColumn) ? $data->orderColumn : null;
                    $orderDirection = isset($data->orderDirection) ? $data->orderDirection : null;
                    $limit = isset($data->limit) ? $data->limit : null;
                    $offset = isset($data->offset) ? $data->offset : null;

                    // Fetch todos using the TodoModel
                    $todoModel = new TodoModel();
                    $todos = $todoModel->getTodos(!$isAdmin ? $userId : null, $searchTerm, $priorityId, $isCompleted, $isPinned, $orderColumn, $orderDirection, $limit, $offset);

                    // Return todos as JSON
                    return $this->respond($todos ?: []);
                } else {
                    // Token is invalid or expired
                    return $this->failUnauthorized('Invalid or expired token');
                }
            }
        } else {
            // No token provided
            return $this->failUnauthorized('Authorization token missing');
        }
    }

    public function update($id = null)
    {
        //Skip Authorization check for OPTIONS request
        if ($this->request->getMethod() !== 'options') {

            // Get the JWT from the Authorization header
            $jwt = getBearerToken();

            if ($jwt) {
                // Verify the token
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    $data = $this->request->getJSON();

                    // Check if the ID is provided in the data
                    if (!isset($data->id)) {
                        return $this->failValidationErrors('Todo ID is required');
                    }

                    // Get the todo ID
                    $todoId = $id ?: $data->id;

                    // Check if the todo item exists
                    $todo = $this->model->find($todoId);

                    if (!$todo) {
                        return $this->failNotFound('Todo with provided ID not found');
                    }

                    // Prepare fields for update (Only update fields that are provided)
                    $fieldsToUpdate = [];

                    if (isset($data->title)) {
                        $fieldsToUpdate['title'] = $data->title;
                    }
                    if (isset($data->isCompleted)) {
                        $fieldsToUpdate['isCompleted'] = $data->isCompleted;
                    }
                    if (isset($data->isPinned)) {
                        $fieldsToUpdate['isPinned'] = $data->isPinned;
                    }
                    if (isset($data->order)) {
                        $fieldsToUpdate['order'] = $data->order;
                    }
                    if (isset($data->dueDate)) {
                        $fieldsToUpdate['dueDate'] = $data->dueDate;
                    }
                    if (isset($data->priorityId)) {
                        $fieldsToUpdate['priorityId'] = $data->priorityId;
                    }

                    $fieldsToUpdate['updatedAt'] = date('Y-m-d');

                    // Update the Todo
                    $this->model->update($todoId, $fieldsToUpdate);

                    // Return a response with updated Todo data
                    $updatedTodo = $this->model->find($todoId);

                    $updatedTodo['isCompleted'] = (int) $updatedTodo['isCompleted'];
                    $updatedTodo['isPinned'] = (int) $updatedTodo['isPinned'];

                    return $this->respond([
                        'success' => true,
                        'message' => 'Todo updated successfully',
                        'todo' => $updatedTodo
                    ]);
                } else {
                    // Invalid or expired token
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                // No token provided
                return $this->failUnauthorized('Authorization token missing');
            }
        }
    }

    public function create()
    {
        // Skip Authorization check for OPTIONS request
        if ($this->request->getMethod() !== 'OPTIONS') {

            // Get JWT from Authorization header
            $jwt = getBearerToken();

            if (!$jwt) {
                return $this->failUnauthorized('Authorization token missing');
            }

            // Verify JWT token
            $decoded = verifyJwtToken($jwt);
            if (!$decoded) {
                return $this->failUnauthorized('Invalid or expired token');
            }

            // Extract userId from decoded token
            $userIdFromToken = $decoded['userId'];

            // Get JSON input data
            $data = $this->request->getJSON();

            // Validate required field: title
            if (empty(trim($data->title))) {
                return $this->failValidationErrors('Missing required field: title');
            }

            // Extract values from request body
            $title = trim($data->title);
            $dueDate = (!empty($data->dueDate)) ? $data->dueDate : NULL;
            $priorityId = (!empty($data->priorityId)) ? $data->priorityId : NULL;

            $data = [
                'title' => $title,
                'dueDate' => $dueDate,
                'priorityId' => $priorityId,
                'userId' => $userIdFromToken,
            ];

            $id = $this->model->insert($data, true);

            if ($id) {
                $newTodo = $this->model->find($id);
                $newTodo['isCompleted'] = (int) $newTodo['isCompleted'];
                $newTodo['isPinned'] = (int) $newTodo['isPinned'];

                return json_encode([
                    'success' => true,
                    'message' => 'Todo inserted successfully!',
                    'todo' => $newTodo
                ]);
            } else {

                return json_encode([
                    'success' => false,
                    'message' => 'Failed to insert todo'
                ]);
            }

        }
    }

    public function delete($id = null)
    {
        // Skip Authorization check for OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {

            // Get the JWT from the Authorization header
            $jwt = getBearerToken();

            if ($jwt) {
                // Verify the token (assuming verifyJwtToken() function is defined)
                $decoded = verifyJwtToken($jwt);

                if ($decoded) {
                    $data = $this->request->getJSON();

                    $todoId = $id ?: $data->id;
                    $deletedTodo = $this->model->delete($todoId);

                    return $this->respond([
                        'success' => true,
                        'message' => 'Todo deleted successfully',
                        'id' => $todoId
                    ]);

                } else {
                    return $this->failUnauthorized('Invalid or expired token');
                }
            } else {
                return $this->failUnauthorized('Authorization token missing');
            }
        }
    }

    // Helper function to check if the user is an admin
    private function checkIfAdmin($roleId)
    {
        // Example query to check if the user is an admin, adapt it to your schema
        $roleModel = new \App\Models\RoleModel(); // Assuming you have a Role model
        $role = $roleModel->find($roleId);

        return $role && $role['role'] === 'ADMIN';
    }
}
