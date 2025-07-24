<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
 * Register a new user
 *
 * Creates a user with name, email, and password. By default, no role is assigned. Must be assigned later by an admin.
 *
 * @bodyParam name string required Full name of the user. Example: John Doe
 * @bodyParam email string required Unique email address. Example: john@example.com
 * @bodyParam password string required Minimum 6 characters. Example: secret123
 *
 * @response 200 {
 *   "user": {
 *     "id": 1,
 *     "name": "John Doe",
 *     "email": "john@example.com",
 *     "role": null,
 *     "created_at": "2025-07-23T10:00:00.000000Z",
 *     "updated_at": "2025-07-23T10:00:00.000000Z"
 *   }
 * }
 */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['user' => $user]);
    }
/**
 * Login user
 *
 * Logs in a user and returns a bearer token on success.
 *
 * @bodyParam email string required Email of the user. Example: john@example.com
 * @bodyParam password string required Password of the user. Example: secret123
 *
 * @response 200 {
 *   "token": "1|XyzToken123abc"
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "email": ["Invalid credentials."]
 *   }
 * }
 */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
/**
 * Logout user
 *
 * Revokes the current access token for the logged-in user.
 *
 * @authenticated
 *
 * @response 200 {
 *   "message": "Logged out"
 * }
 */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
