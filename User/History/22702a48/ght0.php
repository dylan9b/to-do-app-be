<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'USERS'; // Table name
    protected $primaryKey = 'id'; // Primary key of the table

    protected $returnType     = 'array'; // Return type of the data
    protected $useSoftDeletes = false; // Not using soft deletes
    protected $allowedFields = ['email', 'password', 'roleId']; // Fields that can be inserted/updated

    // To enable timestamp columns like created_at and updated_at (optional)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // To handle validation rules (optional)
    protected $validationRules = [
        'email'    => 'required|valid_email|is_unique[USERS.email]',
        'password' => 'required|min_length[6]',
        'roleId'   => 'required|is_not_unique[ROLES.id]'
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already taken.'
        ],
        'roleId' => [
            'is_not_unique' => 'This role does not exist.'
        ]
    ];

    // Function to fetch users based on roleId (optional)
    public function getUsers($id = null)
    {
        if ($id) {
            // Query to fetch a specific user with their role
            return $this->select('u.id AS user_id, u.email, u.password, r.id AS role_id, r.role')
                ->from('USERS u')
                ->join('ROLES r', 'u.roleId = r.id', 'left')
                ->where('u.id', $id)
                ->first();
        } else {
            // Query to fetch all users with their roles
            return $this->select('u.id AS user_id, u.email, u.password, r.id AS role_id, r.role')
                ->from('USERS u')
                ->join('ROLES r', 'u.roleId = r.id', 'left')
                ->findAll();
        }
    }
}
