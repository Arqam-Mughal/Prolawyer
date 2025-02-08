<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::paginate(10));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'senior_lawyer_id' => 'nullable|exists:users,id',
            'plan_expiry' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userData = $request->only([
            'name',
            'email',
            'phone_number',
            'senior_lawyer_id',
            'plan_expiry',
        ]);
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => new UserResource($user)
        ], 200);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:191',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'senior_lawyer_id' => 'nullable|exists:users,id',
            'plan_expiry' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userData = $request->only([
            'name',
            'email',
            'phone_number',
            'senior_lawyer_id',
            'plan_expiry',
        ]);

        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => new UserResource($user)
        ], 200);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ], 200);
    }

    public function assignRole(Request $request, User $user) {
            $validator = Validator::make($request->all(), [
                'role' => 'required|string|exists:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $role = $request->input('role');

            if (!$user->hasRole($role)) {
                $user->assignRole($role);
                return response()->json([
                    'success' => true,
                    'message' => "Role '{$role}' assigned to user successfully.",
                    'user' => new UserResource($user)
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "User already has the role '{$role}'.",
                ], 422);
            }
        
    }
}
