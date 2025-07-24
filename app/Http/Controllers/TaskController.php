<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
 * Display a list of tasks
 *
 * Developers see tasks assigned to them. Team leads see tasks they assigned.
 *
 * @authenticated
 * @response 200 [
 *   {
 *     "id": 1,
 *     "title": "Fix login bug",
 *     "description": "Error after login",
 *     "project_id": 1,
 *     "assigned_to": 5,
 *     "created_by": 3,
 *     "due_time": "2025-08-01",
 *     "status": "pending",
 *     "created_at": "2025-07-23T08:30:00Z",
 *     "updated_at": "2025-07-23T08:30:00Z"
 *   }
 * ]
 */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'developer') {
            return Task::where('assigned_to', $user->id)->get();
        }

        if ($user->role === 'team_lead') {
            return Task::where('created_by', $user->id)->get();
        }

        return response()->json(['message' => 'Unauthorized.'], 403);
    }

 /**
 * Create a new task
 *
 * Only team leads can assign tasks within their own projects.
 *
 * @authenticated
 * @bodyParam title string required The task title. Example: Design homepage
 * @bodyParam description string Optional description of the task. Example: Include hero section
 * @bodyParam project_id int required ID of the project. Example: 1
 * @bodyParam assigned_to int required Developer's user ID. Example: 5
 * @bodyParam due_time date required Deadline for the task. Must be today or later. Example: 2025-08-10
 * @response 201 {
 *   "message": "Task created successfully.",
 *   "task": {
 *     "id": 1,
 *     "title": "Design homepage",
 *     "project_id": 1,
 *     "assigned_to": 5,
 *     "status": "pending"
 *   }
 * }
 * @response 403 {
 *   "message": "Only team leads can assign tasks."
 * }
 * */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'team_lead') {
            return response()->json(['message' => 'Only team leads can assign tasks.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'due_time' => 'required|date|after_or_equal:today',
        ]);

        $developer = User::find($validated['assigned_to']);
        if ($developer->role !== 'developer') {
            return response()->json(['message' => 'Assigned user must be a developer.'], 422);
        }

        $project = Project::find($validated['project_id']);
        if ($project->assigned_to != $user->id) {
            return response()->json(['message' => 'You can only assign tasks in your own projects.'], 403);
        }

        $task = Task::create([
            ...$validated,
            'created_by' => $user->id,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Task created successfully.', 'task' => $task], 201);
    }
/**
 * Show a single task
 *
 * Returns the details of a specific task.
 *
 * @authenticated
 * @urlParam task int required The ID of the task. Example: 1
 * @response 200 {
 *   "id": 1,
 *   "title": "Design homepage",
 *   "description": "Include hero section",
 *   "project_id": 1,
 *   "assigned_to": 5,
 *   "due_time": "2025-08-10",
 *   "status": "pending"
 * }
 */
     public function show(Task $task)
    {
        return $task;
    }
/**
 * Update a task
 *
 * - Team leads can update title, description, deadline, and status.
 * - Developers can update only task status.
 *
 * @authenticated
 * @urlParam task int required The ID of the task. Example: 1
 * @bodyParam title string The updated task title. Example: Fix login
 * @bodyParam description string The updated task description. Example: Resolve session timeout issue
 * @bodyParam deadline date The new due date. Example: 2025-08-20
 * @bodyParam status string Status of the task. Must be one of: pending, in_progress, completed. Example: in_progress
 * @response 200 {
 *   "message": "Task updated by team lead.",
 *   "task": {
 *     "id": 1,
 *     "status": "in_progress"
 *   }
 * }
 * @response 403 {
 *   "message": "Unauthorized."
 * }
 */
    public function update(Request $request, Task $task)
    {
        $user = $request->user();

        if ($user->role === 'team_lead' && $task->created_by === $user->id) {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'sometimes|date|after_or_equal:today',
                'status' => 'sometimes|in:pending,in_progress,completed',
            ]);

            $task->update($validated);
            return response()->json(['message' => 'Task updated by team lead.', 'task' => $task]);
        }

        if ($user->role === 'developer' && $task->assigned_to === $user->id) {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed',
            ]);

            $task->status = $validated['status'];
            $task->save();

            return response()->json(['message' => 'Task status updated.', 'task' => $task]);
        }

        return response()->json(['message' => 'Unauthorized.'], 403);
    }

/**
 * Delete a task
 *
 * Only team leads can delete tasks they created.
 *
 * @authenticated
 * @urlParam task int required The ID of the task. Example: 1
 * @response 200 {
 *   "message": "Task deleted."
 * }
 * @response 403 {
 *   "message": "Only the assigning team lead can delete this task."
 * }
 */
    public function destroy(Request $request, Task $task)
    {
        $user = $request->user();

        if ($user->role !== 'team_lead' || $task->created_by !== $user->id) {
            return response()->json(['message' => 'Only the assigning team lead can delete this task.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }
}
