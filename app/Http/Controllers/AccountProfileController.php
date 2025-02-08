<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountProfileController extends Controller
{


    //Delete own profile account
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        $user?->delete();

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    //delete user account by admin
    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User account deleted successfully.']);
        }

        return response()->json(['message' => 'User not found.'], 404);
    }


    public function myAccount()
    {
        $user = Auth::user();
        $plan = $user->roles;
        $totalCases = CaseModel::where('client_id', $user->id)->count();
        $maxCases =  $user->role ? $user->role->no_cases : 0;

        $plan1 = $user->roles->isNotEmpty() ? $user->roles->first()->name : 'No Plan'; // Get role name

        return response()->json([
            'name' => $user->name,
            'plan'  => $plan1,
            'total_cases' => $totalCases . ' out of ' . $maxCases,
            'date_of_joining' => $user->created_at->toDateString(),




        ]);
    }


}
