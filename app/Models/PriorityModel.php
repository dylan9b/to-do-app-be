<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Guid\Guid;

class PriorityModel extends Model
{
    // Table name
    protected $table = 'PRIORITIES';

    // Primary key
    protected $primaryKey = 'id';

    // Allowed fields for insert/update
    protected $allowedFields = ['priority'];

    // Optionally, you can enable timestamps if needed
    protected $useTimestamps = false;

    protected $useAutoIncrement = false;

    // Method to fetch priorities by id or all priorities
    public function getPriorities($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->findAll();
        }

        return $this->findAll();
    }


    public function insert($row = null, bool $returnID = true)
    {
        // Check if 'id' is missing and generate it
        if (empty($row['id'])) {
            $row['id'] = Guid::uuid4()->toString();  // Generate a UUID
        }

        // Call the parent insert method
        return parent::insert($row, $returnID);  // Perform the insert operation
    }
}
