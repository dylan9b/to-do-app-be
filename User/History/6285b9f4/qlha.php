<?php

namespace App\Models;

use CodeIgniter\Model;

class PriorityModel extends Model
{
    // Define the table name
    protected $table      = 'priorities';
    
    // Define the primary key
    protected $primaryKey = 'id';

    // Specify the allowed fields (columns in the table that can be inserted or updated)
    protected $allowedFields = ['priority'];

    // Automatically handle timestamps (set to true if you want created_at and updated_at columns)
    protected $useTimestamps = false;

    // Define validation rules if needed
    protected $validationRules = [
        'priority' => 'required|max_length[255]'
    ];

    // Return an array with the fields' validation messages
    protected $validationMessages = [
        'priority' => [
            'required' => 'Priority field is required.',
            'max_length' => 'Priority cannot be longer than 255 characters.'
        ]
    ];

    // Optionally, you can define custom methods to interact with the priorities table.
    public function createPriority($priority)
    {
        return $this->insert(['priority' => $priority]);
    }

    public function getAllPriorities()
    {
        return $this->findAll();
    }

    public function getPriorityById($id)
    {
        return $this->find($id);
    }

    public function updatePriority($id, $priority)
    {
        return $this->update($id, ['priority' => $priority]);
    }

    public function deletePriority($id)
    {
        return $this->delete($id);
    }
}
