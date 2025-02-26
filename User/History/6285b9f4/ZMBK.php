<?php

namespace App\Models;

use CodeIgniter\Model;

class PriorityModel extends Model
{
    // Table name
    protected $table = 'Priorities';

    // Primary key
    protected $primaryKey = 'id';

    // Allowed fields for insert/update
    protected $allowedFields = ['priority'];

    // Optionally, you can enable timestamps if needed
    protected $useTimestamps = false;

    // Method to fetch priorities by id or all priorities
    public function getPriorities($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->findAll();
        }

        return $this->findAll();
    }
}
