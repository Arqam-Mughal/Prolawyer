<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;

class ForgetPasswordController extends Controller
{
    // Step 1: Request to reset password (via email)
    public function forget_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = $request->input('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message_type' => "failure", 'message' => "User not found."], 404);
        }

        // Generate a unique reset token
        $token = Str::random(60);

        // Store the reset token in the password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($token), // Store hashed token
                'created_at' => Carbon::now()
            ]
        );

        // Generate the password reset link
        $resetLink = url('/api/reset-password?token='.$token.'&email='.urlencode($email));

        // Send the reset link to the user's email
        Mail::to($email)->send(new PasswordResetMail($resetLink));

        return response()->json([
            'message_type' => "success",
            'message'      => "Password reset link has been sent to your email."
        ], 200);
    }

    // Step 2: Reset password with validation for strong passwords
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8', // Minimum 8 characters
                'regex:/[A-Z]/',      // Must contain at least one uppercase letter
                'regex:/[a-z]/',      // Must contain at least one lowercase letter
                'regex:/[0-9]/',      // Must contain at least one numeric digit
                'regex:/[@$!%*#?&]/', // Must contain a special character
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = $request->input('email');
        $newPassword = $request->input('password');

        // Check if the user with the given email exists
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User with the given email not found.'], 404);
        }

        // Update the user's password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Optionally, delete the reset token from the database if needed
        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json(['message' => 'Password reset successfully'], 200);
    }

    // Step 3: Change password for logged-in users
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user(); // Get currently authenticated user
        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('password');

        // Check if the current password is correct
        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        // Update the user's password
        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }


}
