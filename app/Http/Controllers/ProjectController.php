<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // List all projects
    public function index()
    {
        return Project::get();
    }

    /**
 * Create a new project
 * 
 * Only project managers can assign projects to team leads.
 * 
     * @bodyParam title string required The title of the project. Example: Website Redesign
     * @bodyParam description string The description of the project. Example: Redesign UI/UX for the landing page.
     * @bodyParam assigned_to int required The user ID of the team lead. Example: 3
     * @bodyParam end_date date required The deadline for the project. Example: 2025-09-23 18:30:44
     * @authenticated
 */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'project_manager') {
            return response()->json(['message' => 'Only project managers can assign projects.'], 403);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'end_date' => 'required|date|after:today',
        ]);

        $teamLead = User::find($validated['assigned_to']);
        if ($teamLead->role !== 'team_lead') {
            return response()->json(['message' => 'Assigned user must be a team lead.'], 422);
        }

          $project = Project::create([
        ...$validated,
        'created_by' => $user->id,
    ]);

        return response()->json(['message' => 'Project created successfully.', 'project' => $project], 201);
    }

    /**
 * Show a single project
 *
 * Returns detailed information about a specific project including its assigned team lead.
 *
 * @urlParam id int required The ID of the project. Example: 1
 * @authenticated
 * @response 200 {
 * "id": 1,
 *   "title": "abc",
 *   "description": "abcd abcd abcd",
 *   "end_date": "2025-09-23",
 *   "assigned_to": 3,
 *   "created_by": 2,
 *   "created_at": "2025-07-23T18:31:42.000000Z",
 *   "updated_at": "2025-07-23T18:31:42.000000Z",
 *   "team_lead": {
 *       "id": 3,
 *       "name": "TestUser2",
 *       "email": "test02@example.com",
 *       "email_verified_at": null,
 *       "created_at": "2025-07-23T18:21:49.000000Z",
 *       "updated_at": "2025-07-23T18:23:16.000000Z",
 *       "role": "team_lead"
 *   }
 * }
 */
    public function show($id)
    {
        $project = Project::with('teamLead')->findOrFail($id);
        return response()->json($project);
    }
/**
 * Update a project
 *
 * Allows a project manager to update project details like title, description, end date, or assigned team lead.
 *
 * @urlParam id int required The ID of the project to update. Example: 1
 * @authenticated
 * @bodyParam title string The updated title of the project. Example: Revamp Website UI
 * @bodyParam description string The updated description of the project. Example: Add animations and new design elements.
 * @bodyParam assigned_to int The ID of the team lead to assign the project to. Example: 3
 * @bodyParam end_date date The new project deadline. Example: 2025-09-25
 * @response 200 {
 *   "message": "Project updated.",
 *   "project": {
 *     "id": 1,
 *     "title": "Revamp Website UI",
 *     "description": "Add animations and new design elements.",
 *     "assigned_to": 3,
 *     "created_by": 1,
 *     "end_date": "2025-09-25",
 *     "created_at": "2025-07-23T10:00:00.000000Z",
 *     "updated_at": "2025-07-23T10:15:00.000000Z"
 *   }
 * }
 */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'project_manager') {
            return response()->json(['message' => 'Only project managers can update projects.'], 403);
        }

        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'sometimes|exists:users,id',
            'end_date' => 'sometimes|date|after:today',
        ]);

        if (isset($validated['assigned_to'])) {
            $teamLead = User::find($validated['assigned_to']);
            if ($teamLead->role !== 'team_lead') {
                return response()->json(['message' => 'Assigned user must be a team lead.'], 422);
            }
        }

        $project->update($validated);

        return response()->json(['message' => 'Project updated.', 'project' => $project]);
    }
/**
 * Delete a project
 *
 * Allows a project manager to delete a specific project.
 *
 * @urlParam id int required The ID of the project to delete. Example: 1
 * @authenticated
 * @response 200 {
 *   "message": "Project deleted."
 * }
 */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'project_manager') {
            return response()->json(['message' => 'Only project managers can delete projects.'], 403);
        }

        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }
}
