<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'ROLES'; // Name of the table
    protected $primaryKey = 'id'; // Primary key column

    // Columns to allow for mass assignment
    protected $allowedFields = ['role'];

    // We can also define validation rules for fields, but let's keep it simple for now
    protected $validationRules = [
        'role' => 'required|max_length[5]', // Adjust validation as needed
    ];

    
    public function getUsers($id = null)
    {
        if ($id) {
            // Fetch a single user with role info by ID
            $builder = $this->builder();
            $builder->select('u.id AS user_id, u.email, r.id AS role_id, r.role')
                    ->from('USERS u')
                    ->join('ROLES r', 'u.roleId = r.id')
                    ->where('u.id', $id);
            $query = $builder->get();
            $user = $query->getRowArray();

            if ($user) {
                // Format the user data to include the nested role object
                return [
                    'id' => $user['user_id'],
                    'email' => $user['email'],
                    'role' => [
                        'id' => $user['role_id'],
                        'role' => $user['role']
                    ]
                ];
            }
        } else {
            // Fetch all users with their roles
            $builder = $this->builder();
            $builder->select('u.id AS user_id, u.email, r.id AS role_id, r.role')
                    ->from('USERS u')
                    ->join('ROLES r', 'u.roleId = r.id');
            $query = $builder->get();
            $users = $query->getResultArray();

            $formattedUsers = [];
            foreach ($users as $user) {
                // Format each user data to include the nested role object
                $formattedUsers[] = [
                    'id' => $user['user_id'],
                    'email' => $user['email'],
                    'role' => [
                        'id' => $user['role_id'],
                        'role' => $user['role']
                    ]
                ];
            }
            return $formattedUsers;
        }

        return null; // Return null if no data is found
    }
}
