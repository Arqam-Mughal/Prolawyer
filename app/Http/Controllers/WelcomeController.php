<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(){
        $roles = \App\Models\Role::with('permissions')->where('type','regular_user')->where('status', 1)->get();
        $payment_method = 1;
        return view('welcome',compact('roles','payment_method'));
    }

    public function checkEmail(Request $request)
    {
        if (isset($request->email)) {
            if (User::whereEmail($request->email)->exists()) {
                return response()->json(['exists' => 1]);
            } else {
                return response()->json(['exists' => 0]);
            }
        } else {
            return response()->json(['msg' => 'Email field not found']);
        }
    }
}
