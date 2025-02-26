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

    // Get all roles
    public function getRoles()
    {
        return $this->findAll();
    }
}
