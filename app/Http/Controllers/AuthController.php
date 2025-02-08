<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\PaymentController;

class AuthController extends Controller
{

    protected $paymentController;
    protected $payuController;

    // Inject the PaymentController into the constructor
    public function __construct(PaymentController $paymentController, PayUController $payuController)
    {
        $this->paymentController = $paymentController;
        $this->payuController    = $payuController;


    }

    public function register(Request $request): JsonResponse
    {
        // Validate the user input
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'phone_number' => [
                'required',
                'string',
                'size:10',
                'regex:/^\d{10}$/',
                'unique:users'
            ],
            'role_id'      => 'required|exists:roles,id',
            'package'      => 'required|in:monthly,quarterly,yearly',
            'device_id'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Retrieve the package price from the roles table
        $role = Role::find($request->role_id);
        if (!$role) {
            return response()->json(['error' => 'Invalid role ID'], 422);
        }

        // Determine the price based on the selected package
        $price = match ($request->package) {
            'monthly'   => $role->price,
            'quarterly' => $role->quarterly_price,
            'yearly'    => $role->yearly_price,
            default     => 0,
        };

        // Create the user
        $device_id = $request->header('User-Agent');
        $user      = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone_number' => '+91'.$request->phone_number,
            'device_id'    => $device_id,
            'role_id'      => $request->role_id,
            'package'      => $request->package,
        ]);

        // Prepare the payment data
        $paymentData = [
            'subAmount'               => $price,
            'isPartialPaymentAllowed' => false,
            'description'             => "Payment for {$request->package} package",
            'source'                  => 'API',
            'user_id'                 => $user->id,
            'amount'                  => $price,
            'package'                 => $request->package,
            'role_id'                 => $request->role_id,
        ];

        // Retrieve the active payment gateway
        $activeGateway = Setting::get('payment_gateway', 'cashfree');
        $paymentRequest = Request::create('', 'POST', $paymentData);

        $paymentLink = match ($activeGateway) {
            'cashfree' => $this->paymentController->createOrder($paymentRequest),
            'payu'     => $this->payuController->createPaymentLink($paymentRequest),
            default    => response()->json(['error' => 'Payment gateway not configured'], 500),
        };

        if ($paymentLink instanceof JsonResponse) {
            $paymentData = $paymentLink->getData(true); // Convert response to array

            // Extract transaction_id and payment_link
            $transactionId = $paymentData['transaction_id'] ?? $paymentData['original']['transaction_id'] ?? null;
            $paymentUrl    = $paymentData['payment_link'] ?? $paymentData['original']['payment_link'] ?? null;


            // Return the success response
            return response()->json([
                'message'        => "User Registered Successfully. Please proceed with payment.",
                'transaction_id' => $transactionId,
                'payment_link'   => $paymentUrl
            ]);
        }

        // Handle non-JSON responses
        return response()->json(['error' => 'Unexpected payment response'], 500);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(
                [
                    'message_type' => "error",
                    'message'      => "Please enter valid credentials.",
                ]
            );
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Get device_id from User-Agent header or custom logic
        $device_id = $request->header('User-Agent'); // or use a custom fingerprinting logic

        // Update device_id if it's different or not present
        if ($user->device_id !== $device_id) {
            $user->device_id = $device_id;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user'         => $user, 'access_token' => $token, 'token_type' => 'Bearer',
                                 'message_type' => "success", 'message' => "User Logged In Successfully."
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message_type' => "success", 'message' => 'Logged out successfully']);
    }


    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|size:10|regex:/^\d{10}$/',
            'email'        => 'nullable|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$request->phone_number && !$request->email) {
            return response()->json(['message' => 'Phone number or email is required.'], 422);
        }

        if ($request->phone_number) {
            $user = User::where('phone_number', '+91'.$request->phone_number)->first();
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate OTP
        $otp = random_int(100000, 999999);

        // Store the OTP in the database or cache with expiration
        $user->otp            = $otp;
        $user->otp_expires_at = now()->addMinutes(10); // OTP expires in 10 minutes
        $user->save();

        if ($request->email) {
            $user->notify(new OtpNotification($otp));
        } else {
            // Send OTP via SMS
        }


        return response()->json([
            'otp'     => $otp,
            'message' => 'OTP sent successfully.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|size:10|regex:/^\d{10}$/',
            'email'        => 'nullable|string|email|max:255',
            'otp'          => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Determine the user based on provided contact information
        if ($request->phone_number) {
            $user = User::where('phone_number', '+91'.$request->phone_number)->first();
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        } else {
            return response()->json(['message' => 'Phone number or email is required.'], 422);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if OTP is valid and not expired
        if ($user->otp !== $request->otp || now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }

        // OTP is valid, generate token and log in the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Clear OTP from the user record
        $user->otp            = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'message_type' => 'success',
            'message'      => 'User logged in successfully with OTP.',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|size:10|regex:/^\d{10}$/',
            'email'        => 'nullable|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$request->phone_number && !$request->email) {
            return response()->json(['message' => 'Phone number or email is required.'], 422);
        }

        if ($request->phone_number) {
            $user = User::where('phone_number', '+91'.$request->phone_number)->first();
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate new OTP
        $otp = rand(100000, 999999);

        $user->otp            = $otp;
        $user->otp_expires_at = now()->addMinutes(10); // OTP expires in 10 minutes
        $user->save();

        if ($request->email) {
            $user->notify(new OtpNotification($otp));
        } elseif ($request->phone_number) {
            // Send OTP via SMS (implementation depends on your setup)
            // ...
        }

        return response()->json([
            'otp'     => $otp,
            'message' => 'OTP Resend Successfully.'
        ]);
    }

    // Add the following method to `laravel/app/Http/Controllers/AuthController.php`

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete the user account
        $user->delete();

        return response()->json(['message_type' => "success", 'message' => 'Account deleted successfully']);
    }

    // Add the following method to `laravel/app/Http/Controllers/AuthController.php`

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message_type' => "error", 'message' => 'User not authenticated.'], 401);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string|max:255', // Full name validation
            'email'        => 'nullable|string|email|max:255|unique:users,email,'.$user->id,
            'phone_number' => 'nullable|string|size:10|regex:/^\d{10}$/|unique:users,phone_number,'.$user->id,
            'address'      => 'nullable|string|max:500',
            'profile_pic'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Limit size to 2MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the user's profile
        if ($request->has('name')) {
            $user->name = $request->name; // Update full name
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone_number')) {
            $user->phone_number = '+91'.$request->phone_number;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_pic')) {
            // Delete the old profile picture if it exists
            if ($user->profile_pic && Storage::exists($user->profile_pic)) {
                Storage::delete($user->profile_pic);
            }

            // Save the new profile picture
            $file              = $request->file('profile_pic');
            $filePath          = $file->store('profile_pictures',
                'public'); // Store in the 'public' disk under 'profile_pictures'
            $user->profile_pic = $filePath;
        }

        // Save the user to the database
        $user->save();

        return response()->json(['message_type' => "success", 'message' => 'Profile updated successfully']);
    }


    public function IsvalidateToken(Request $request): JsonResponse
    {
        // Retrieve the user ID from the request
        $userId = $request->input('user_id');

        // Check if the user ID is provided
        if (!$userId) {
            return response()->json(['flag' => false, 'message' => 'User ID not provided'], 400);
        }

        // Fetch the user from the database based on the user ID
        $user = User::find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['flag' => false, 'message' => 'User not found'], 404);
        }

        // Check if the user has a valid API token
        if (!$user->api_token) {
            return response()->json(['flag' => false, 'message' => 'No token associated with this user'], 400);
        }

        // Check if the token has expired
        if ($user->plan_expiry < now()) {
            return response()->json(['flag' => false, 'message' => 'Token has expired'], 401);
        }

        // Check if the user is active
        if ($user->status !== 'activated') {
            return response()->json(['flag' => false, 'message' => 'User is not active'], 401);
        }

        // All conditions passed, return success response
        return response()->json(['flag' => true, 'message' => 'Token is valid. User is active and the token has not expired.']);
    }
}
