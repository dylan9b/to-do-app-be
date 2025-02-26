<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Guid\Guid;

class TodoModel extends Model
{
    protected $table      = 'TODOS';             // Table name
    protected $primaryKey = 'id';                // Primary key field

    protected $allowedFields = ['title', 'userId', 'isCompleted', 'isPinned', 'order', 'dueDate', 'createdAt', 'updatedAt', 'priorityId']; // Ensure all fields are listed
    // Fields that can be inserted/updated

    protected $useTimestamps = false;

    protected $useAutoIncrement = false;

    // Validation rules
    protected $validationRules = [
        'title'      => 'required|max_length[255]',
        'userId'     => 'required|is_not_unique[USERS.id]',  // Ensure userId exists in USERS table
        'priorityId' => 'permit_empty|is_not_unique[PRIORITIES.id]', // Optional field, ensure it exists in PRIORITIES table if provided
        'dueDate'    => 'permit_empty|valid_date',  // Optional field, if provided, ensure it's a valid date
    ];

    // Error messages for validation
    protected $validationMessages = [
        'title' => [
            'required' => 'The title field is required.',
            'max_length' => 'The title can\'t be more than 255 characters.'
        ],
        'userId' => [
            'required' => 'User ID is required.',
            'is_not_unique' => 'The User ID does not exist in the USERS table.'
        ],
        'priorityId' => [
            'is_not_unique' => 'The Priority ID does not exist in the PRIORITIES table.'
        ],
        'dueDate' => [
            'valid_date' => 'The due date is not a valid date.'
        ],
    ];

    // Fetch todos based on user ID and filters
    public function getTodos($userId = null, $searchTerm = null, $priorityId = null, $isCompleted = null, $isPinned = null)
    {
        $builder = $this->builder();

        // Apply filters if provided
        if ($userId) {
            $builder->where('userId', $userId);
        }
        if ($searchTerm) {
            $builder->like('title', $searchTerm);
        }
        if ($priorityId) {
            $builder->where('priorityId', $priorityId);
        }
        if ($isCompleted !== null) {
            $builder->where('isCompleted', $isCompleted);
        }
        if ($isPinned !== null) {
            $builder->where('isPinned', $isPinned);
        }

        // Order by creation date (descending)
        $builder->orderBy('createdAt', 'DESC');

        // Return the results as an array
        $results = $builder->get()->getResultArray();

        // Cast values explicitly
        foreach ($results as &$result) {
            $result['isCompleted'] = (int) $result['isCompleted'];
            $result['isPinned'] = (int) $result['isPinned'];
        }

        return $results;
    }

    public function insert($row = null, bool $returnID = true)
    {
        $row['createdAt'] = date('Y-m-d');

        // Check if 'id' is missing and generate it
        if (empty($row['id'])) {
            $row['id'] = Guid::uuid4()->toString();  // Generate a UUID
        }

        // Call the parent insert method
        return parent::insert($row, $returnID);  // Perform the insert operation
    }
    
}

