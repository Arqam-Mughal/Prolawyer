<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return RoleResource::collection(Role::where('type', 'regular_user')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'price' => 'required|numeric|min:0|max:999999.99',
            'quarterly_price' => 'required|numeric|min:0|max:999999.99',
            'yearly_price' => 'required|numeric|min:0|max:999999.99',
            // 'type' => 'required|string|max:191|in:regular_user,system_user',
            // 'no_cases' => 'required|integer|min:0',
            // 'status' => 'required|integer|in:0,1',
            // 'details' => 'nullable|string|max:191',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::create($request->only([
            'name',
            'price',
            'quarterly_price',
            'yearly_price',
            // 'type',
            // 'no_cases',
            // 'status',
            // 'details',
        ]) + ['type' => 'regular_user']);


        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'role' => new RoleResource($role)
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function permissions(Role $role)
    {
        return new RoleResource($role);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:191',
            'price' => 'numeric|min:0|max:999999.99',
            'quarterly_price' => 'numeric|min:0|max:999999.99',
            'yearly_price' => 'numeric|min:0|max:999999.99',
            // 'type' => 'required|string|max:191',
            // 'no_cases' => 'required|integer|min:0',
            // 'status' => 'required|integer|in:0,1',
            // 'details' => 'nullable|string|max:191',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role->update($request->only([
            'name',
            'price',
            'quarterly_price',
            'yearly_price',
            // 'type',
            // 'no_cases',
            // 'status',
            // 'details',
        ]));


        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'role' => new RoleResource($role)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();


        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.'
        ], 200);
    }

    /**
     * Get permissions of role
     */
    public function permissionsIndex($role)
    {
        $role = Role::find($role);


        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return PermissionResource::collection(
            Permission::whereIn('id', $role->permissions->pluck('id'))->paginate(10)
        );
    }

    /**
     * Update specific permission of role
     */
    public function permissionsUpdate(Role $role, Request $request)
    {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|string',
            // 'type' => 'required|string|max:191',
            // 'no_cases' => 'required|integer|min:0',
            // 'status' => 'required|integer|in:0,1',
            // 'details' => 'nullable|string|max:191',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $permissions = explode(',', $request->permissions);
        RolePermission::where('role_id', $role->id)->delete();

        $rp_insert = [];
        foreach ($permissions as $permission) {
            $rp_insert[] = [
                'permission_id' => $permission,
                'role_id'       => $role->id,
                'status'        => 1,
                'created_by'    => 1, // isko update karna padega $request->user->id
                'updated_by'    => 1, // isko update karna padega $request->user->id
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        RolePermission::insert($rp_insert);


        return response()->json([
            'success' => true,
            'message' => 'Role permission updated successfully.'
        ], 200);
    }

    private function getPlanData($plan)
    {
        $plans = [
            'monthly' => [
                'planType' => 'Monthly',
                'plans' => [
                    [
                        'name' => 'Basic',
                        'noOfCases' => 300,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => false,
                            'caseHistory' => false,
                            'toDoList' => true,
                            'connectedMatters' => false,
                            'caseExtraction' => false,
                            'courtFeeCalculator' => false,
                            'caseAssignmentWithinTeam' => false,
                        ],
                        'price' => 299,
                    ],
                    [
                        'name' => 'Standard',
                        'noOfCases' => 500,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => true,
                            'caseHistory' => true,
                            'toDoList' => true,
                            'connectedMatters' => true,
                            'caseExtraction' => true,
                            'courtFeeCalculator' => true,
                            'caseAssignmentWithinTeam' => false,
                        ],
                        'price' => 499,
                    ],
                    [
                        'name' => 'Premium',
                        'noOfCases' => 1000,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => true,
                            'caseHistory' => true,
                            'toDoList' => true,
                            'connectedMatters' => true,
                            'caseExtraction' => true,
                            'courtFeeCalculator' => true,
                            'caseAssignmentWithinTeam' => true,
                        ],
                        'price' => 799,
                    ],
                ]
            ],
            'yearly' => [
                'planType' => 'Yearly',
                'plans' => [
                    [
                        'name' => 'Basic',
                        'noOfCases' => 300,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => false,
                            'caseHistory' => false,
                            'toDoList' => true,
                            'connectedMatters' => false,
                            'caseExtraction' => false,
                            'courtFeeCalculator' => false,
                            'caseAssignmentWithinTeam' => false,
                        ],
                        'price' => 3499,
                    ],
                    [
                        'name' => 'Standard',
                        'noOfCases' => 500,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => true,
                            'caseHistory' => true,
                            'toDoList' => true,
                            'connectedMatters' => true,
                            'caseExtraction' => true,
                            'courtFeeCalculator' => true,
                            'caseAssignmentWithinTeam' => false,
                        ],
                        'price' => 5499,
                    ],
                    [
                        'name' => 'Premium',
                        'noOfCases' => 1000,
                        'features' => [
                            'dailyBoard' => true,
                            'calendar' => true,
                            'bareActs' => true,
                            'notes' => true,
                            'myClient' => true,
                            'caseHistory' => true,
                            'toDoList' => true,
                            'connectedMatters' => true,
                            'caseExtraction' => true,
                            'courtFeeCalculator' => true,
                            'caseAssignmentWithinTeam' => true,
                        ],
                        'price' => 9499,
                    ],
                ]
            ]
        ];

        return $plans[$plan];
    }
    public function getPlans(Request $request)
    {
        $plan = $request->input('plan');

        // Check if 'plan' is provided, return both if not specified
        if ($plan === 'monthly') {
            return response()->json([$this->getPlanData('monthly')]);
        }

        if ($plan === 'yearly') {
            return response()->json([$this->getPlanData('yearly')]);
        }

        // If no plan specified, return both monthly and yearly plans
        return response()->json([
            $this->getPlanData('monthly'),
            $this->getPlanData('yearly')
        ]);
    }

    public function rolesWithPermissions(Request $request)
    {
        // Fetch all available permissions from the database
        $allPermissions = Permission::where('status', 1)->get();

        // Fetch roles with type 'regular_user' and eager load permissions
        $roles = Role::where('type', 'regular_user')->with(['permissions' => function($query) {
            $query->where('permissions.status', 1); // Explicitly specify the table name
        }])->paginate(10);

        // Transform the roles into a format that includes permissions
        $rolesWithPermissions = $roles->map(function ($role) use ($allPermissions) {
            // Get role's assigned permission IDs
            $rolePermissionIds = $role->permissions->pluck('id')->toArray();

            // For each available permission, check if it exists in the role's permissions
            $permissionsWithAvailability = $allPermissions->map(function ($permission) use ($rolePermissionIds) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'is_available' => in_array($permission->id, $rolePermissionIds) // Check if the role has this permission
                ];
            });

            return [
                'id' => $role->id,
                'name' => $role->name,
                'price' => $role->price,
                'quarterly_price' => $role->quarterly_price,
                'yearly_price' => $role->yearly_price,
                'type' => $role->type,
                'no_cases' => $role->no_cases,
                'status' => $role->status,
                'permissions' => $permissionsWithAvailability // Use modified permissions list with availability
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $rolesWithPermissions,
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function upgradeOrDowngradePlan(Request $request): \Illuminate\Http\JsonResponse
    {
        // Step 1: Validate request input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:roles,id', // Plan refers to a role here
        ]);

        // Step 2: Find the user and the plan
        $user = User::findOrFail($request->user_id);
        $newPlan = Role::findOrFail($request->plan_id);

        // Step 3: Update user's plan
        $user->role_id = $newPlan->id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully.',
            'user' => $user,
            'new_plan' => $newPlan,
        ], 200);
    }



    /**
     * API to save the chosen plan for a user.
     */
    public function selectPlan(Request $request)
    {
        // Step 1: Validate input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:roles,id',
        ]);

        // Step 2: Find the user and the plan
        $user = User::findOrFail($request->user_id);
        $selectedPlan = Role::findOrFail($request->plan_id);

        // Step 3: Set the selected plan as the user's current plan
        $user->role_id = $selectedPlan->id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Plan selected successfully.',
            'user' => $user,
            'selected_plan' => $selectedPlan,
        ], 200);
    }


}
