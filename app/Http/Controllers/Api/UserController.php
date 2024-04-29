<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Roles
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        $registerUserData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8'
        ]);
        $user = User::create([
            'name' => $registerUserData['name'],
            'email' => $registerUserData['email'],
            'password' => Hash::make($registerUserData['password']),
        ]);

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'User Created',
            'access_token' => $token
        ], 200);
    }

    public function login(Request $request)
    {
        $loginUserData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|min:8'
        ]);

        $user = User::where('email', $loginUserData['email'])->first();

        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Logged In',
            'access_token' => $token,
            'user' => $user
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            $data = [
                'status' => 404,
                'message' => 'User not found'
            ];

            return response()->json($data, 404);
        }

        $data = [
            'status' => 200,
            'message' => 'User found',
            'user' => $user
        ];

        return response()->json($data, 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged Out'
        ], 200);
    }

    public function users(Request $request)
    {
        $page = request('page') ?? 1;
        $q = request('q') ?? '';

        // $patients = Doctor::where('name', 'like', "%{$q}%")->paginate(10, $page);
        // $patients = Doctor::all();
        $users = User::paginate(10);

        if ($q) {
            $patients = User::where('name', 'like', "%{$q}%")->paginate(10);
        }

        if ($users->isEmpty()) {
            $data = [
                'status' => 200, // Or adjust based on your logic for no results
                'msg' => 'No users found matching search term: ' . $q,
                'users' => [], // Set an empty array for patients
                'total' => 0, // Set total to 0
                'page' => $page,
                'limit' => 10
            ];

            return response()->json($data, 200);
        }

        $data = [
            'status' => 200,
            'msg' => 'users matching search term: ' . $q,
            'users' => $users->items(),
            // 'total' => $patients->total(),
            'total' => 10,
            'page' => $page,
            'limit' => 10
        ];

        // dd($data);

        return response()->json($data, 200);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Token Refreshed',
            'access_token' => $token,
            'user' => $user
        ], 200);
    }

    public function updateRoleUser(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|string|in:admin,user'
            ]);

            if ($request->user()->role != 'admin') {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }
            $user = $request->user();
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'message' => 'Role Updated',
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Invalid Role'
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            $data = [
                'status' => 404,
                'message' => 'User not found'
            ];

            return response()->json($data, 404);
        }

        $user->update($request->all());

        $data = [
            'status' => 200,
            'message' => 'User updated successfully',
            'data' => $user
        ];

        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            $data = [
                'status' => 404,
                'message' => 'User not found'
            ];

            return response()->json($data, 404);
        }

        $user->delete();

        $data = [
            'status' => 200,
            'message' => 'User deleted successfully'
        ];

        return response()->json($data, 200);
    }

    public function getRoles(Request $request)
    {

        $roles = [
            new Roles(1, 'admin'),
            new Roles(2, 'user'),
            new Roles(3, 'patient'),
            new Roles(4, 'doctor'),
        ];


        return response()->json([
            'message' => 'Roles',
            'roles' => $roles
        ], 200);
    }

    public function updatePartial(Request $request, $id)
    {
        $patient = User::find($id);

        if (!$patient) {
            $data = [
                'status' => 404,
                'message' => 'User not found'
            ];

            return response()->json($data, 404);
        }

        if ($patient->role == 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'role' => '',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        if ($request->has('name')) {
            $patient->name = $request->name;
        }

        if ($request->has('role')) {
            $patient->role = $request->role;
        }

        $patient->save();

        $data = [
            'status' => 200,
            'message' => 'Patient updated successfully',
            'data' => $patient
        ];

        return response()->json($data, 200);
    }

    public function isAdmin(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->role != 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }

    public function isAdminOrDoctor(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->role != 'admin' && $user->role != 'doctor') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
