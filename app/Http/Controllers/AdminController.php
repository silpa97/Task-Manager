<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
 * Assign a role to a user
 *
 * Only admins can assign roles to other users like project manager, team lead, or developer.
 *
 * @authenticated
 * @bodyParam user_id int required The ID of the user to assign a role to. Example: 2
 * @bodyParam role string required The role to assign. Allowed values: admin, project_manager, team_lead, developer. Example: project_manager
 *
 * @response 200 {
 *   "message": "Role assigned successfully.",
 *   "user": {
 *     "id": 2,
 *     "name": "Jane Smith",
 *     "role": "project_manager"
 *   }
 * }
 *
 * @response 403 {
 *   "message": "Unauthorized. Only admins can assign roles."
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "role": [
 *       "The selected role is invalid."
 *     ]
 *   }
 * }
 */
    public function assignRole(Request $request)
    {
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['admin', 'project_manager', 'team_lead', 'developer'])],
        ]);
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can assign roles.'], 403);
        }

        $user = User::findOrFail($request->user_id);
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Role assigned successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ]);
    }
}
