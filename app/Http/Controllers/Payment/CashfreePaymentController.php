<?php

namespace App\Http\Controllers\Payment;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Session;

class CashfreePaymentController extends Controller
{
    public function store(Request $request)
    {
        $raw = str_replace('amp;', '', $request->items);
        parse_str($raw, $data);

        Session::put('user_register_data', $data);

        // Define credentials
        // $x_client_id = '33577461720b3b3889f946a2dc477533';
        // $x_client_secret = 'dc834fc3deace297969c8110914a6711ec50b157';
        $x_client_id = '40026701e0b9fa0b8929a81388762004';
        $x_client_secret = 'cfsk_ma_prod_e689378603c4ba8f5770ec03ea70f88c_805e6866';

        $price = 0;
        $plan = Role::find($data['plan_id']);

        // Set price based on plan duration
        if ($data['plan_duration'] == 1) {
            $price = $plan->price;
        } elseif ($data['plan_duration'] == 2) {
            $price = $plan->quarterly_price;
        } elseif ($data['plan_duration'] == 3) {
            $price = $plan->yearly_price;
        }

        // Generate order details
        $order_data = [
            'order_id' => 'order_' . rand(1111111111, 9999999999),
            'order_amount' => $price,
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => 'customer_' . rand(111111111, 999999999),
                'customer_name' => $data['name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'],
            ],
            'order_meta' => [
                'return_url' => env('APP_URL') . '/cashfree/payments/success/?order_id={order_id}',
            ],
        ];

        // Prepare the request payload
        $request_payload = [
            'order_id' => $order_data['order_id'],
            'order_amount' => $order_data['order_amount'],
            'order_currency' => $order_data['order_currency'],
            'customer_details' => $order_data['customer_details'],
            'order_meta' => $order_data['order_meta'],
        ];

        // $url = 'https://sandbox.cashfree.com/pg/orders'; // Correct endpoint for sandbox environment
        $url = 'https://api.cashfree.com/pg/orders'; // Correct endpoint for live environment

        // Setting up headers
        $headers = [
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
            "x-client-id: $x_client_id",
            "x-client-secret: $x_client_secret"
        ];

        // Initialize cURL session
        $curl = curl_init();

        // cURL options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_payload)); // Ensure the payload is properly JSON encoded

        // Execute cURL request
        $resp = curl_exec($curl);

        // Check for cURL errors
        if ($err = curl_error($curl)) {
            curl_close($curl);
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Close the cURL session
        curl_close($curl);

        // Decode response JSON
        $response_data = json_decode($resp, true);

        // Check if response contains error or success message
        if (isset($response_data['error'])) {
            return response()->json(['error' => $response_data['error']], 400);
        }

        // Return the response
         if (isset($response_data['payment_session_id'])) {
            return response()->json([
                'session_id' => $response_data['payment_session_id']
            ]);
        } else {
            return response()->json(['error' => 'Failed to create payment session.']);
        }

    }


    // Success handler after payment
    public function successs(Request $request)
    {
        // $x_client_id = '33577461720b3b3889f946a2dc477533';
        // $x_client_secret = 'dc834fc3deace297969c8110914a6711ec50b157';
        $x_client_id = '40026701e0b9fa0b8929a81388762004';
        $x_client_secret = 'cfsk_ma_prod_e689378603c4ba8f5770ec03ea70f88c_805e6866';

        $x_api_version = "2023-08-01";

        // $url = 'https://sandbox.cashfree.com/pg/orders/' . $request->order_id; // Replace with production URL in live environment
        $url = 'https://api.cashfree.com/pg/orders/' . $request->order_id; // Replace with test URL in test environment

        $headers = [
            "Content-Type: application/json",
            "x-api-version: $x_api_version",
            "x-client-id: $x_client_id",
            "x-client-secret: $x_client_secret"
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if ($err = curl_error($curl)) {
            curl_close($curl);
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        $response_data = json_decode($response, true);

        curl_close($curl);

        if (isset($response_data['order_status']) && $response_data['order_status'] == 'PAID') {

            if (Session::has('user_register_data')) {
                $data = Session::get('user_register_data');

                $role = explode('-', $data['role_id']);
                $role_id = $role[0];
                $price = 0;
                $expiry = '';
                $plan = Role::find($data['plan_id']);
                $validity = '';

                // Determine the plan validity and price based on plan duration
                if ($data['plan_duration'] == 1) {
                    $price = $plan->price;
                    $expiry = date("Y-m-d H:i:s", strtotime("+30 days"));
                    $validity = 'Monthly';
                } elseif ($data['plan_duration'] == 2) {
                    $price = $plan->quarterly_price;
                    $expiry = date("Y-m-d H:i:s", strtotime("+91 days"));
                    $validity = 'Quarterly';
                } elseif ($data['plan_duration'] == 3) {
                    $price = $plan->yearly_price;
                    $expiry = date("Y-m-d H:i:s", strtotime("+365 days"));
                    $validity = 'Yearly';
                }

                // Create a new user
                $user = new User();
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = $data['password'];
                $user->phone_number = $data['phone'];
                $user->address = $data['current_address'];
                $user->role_id = $role_id;
                $user->plan_expiry = $expiry;
                $user->package = strtolower($validity);
                $user->save();

                $user->assignRole($plan->name);

                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->payment_method = 1;
                $transaction->order_id = $response_data['order_id'];
                $transaction->trans_date = now();
                $transaction->charged_amount = $response_data['order_amount'];
                $transaction->description = 'User registration Fee ₹ ' . $response_data['order_amount'] . '.00';
                $transaction->plan_id = $role_id;
                $transaction->plan_validity = $validity;
                $transaction->save();

                Session::forget('user_register_data');
                Session::flash('success', 'User Registered Successfully!');

                return redirect()->route('backpack.auth.login');
            }
        } else {
            return response()->json(['error' => 'Payment not completed successfully'], 400);
        }
    }

}
