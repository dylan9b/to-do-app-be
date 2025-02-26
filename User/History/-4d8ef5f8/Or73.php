<?php

namespace App\Controllers;

use App\Models\PriorityModel;

class PriorityController extends BaseController
{
    public function __construct()
    {
        // Load the Priorities model
        $this->PriorityModel = new PriorityModel();
    }

    // Display all priorities
    public function index()
    {
        $data['priorities'] = $this->PriorityModel->getAllPriorities();
        return view('priorities_view', $data); // Reference to your view
    }

    // Add a new priority
    public function create()
    {
        // Example: Inserting a new priority
        $this->PriorityModel->createPriority('High');
        return redirect()->to('/priorities'); // Redirect back to the list of priorities
    }

    // Delete a priority
    public function delete($id)
    {
        $this->PriorityModel->deletePriority($id);
        return redirect()->to('/priorities'); // Redirect back to the list of priorities
    }
}
