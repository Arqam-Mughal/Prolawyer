<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PayUController extends Controller
{
    private $clientId = '0fcfe26ba59da7a0cac95ea767f31ce5f5915ad7740e755b07b4a21ed2623557';
    private $clientSecret = 'cd600581e83143e1d01255ac4bb03df6a4ad77422a771386890daf4876776ae3';
    private $merchantId = '8359381';
    private $accessToken;

    // Function to authenticate and obtain the access token
    private function authenticate()
    {
        $client = new Client(['base_uri' => 'https://uat-accounts.payu.in/', 'timeout' => 10.0]);


        try {
            $response = $client->request('POST', 'oauth/token', [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => 'create_payment_links update_payment_links read_payment_links',
                ],
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $tokenData = json_decode($responseBody, true);

            if (isset($tokenData['access_token'])) {
                $this->accessToken = $tokenData['access_token'];
            } else {
                throw new Exception('Access token not found in response');
            }
        } catch (RequestException $e) {
            Log::error('Authentication failed', ['error' => $e->getMessage()]);
            throw new Exception('Authentication failed: ' . $e->getMessage());
        }
    }

    public function createPaymentLink(Request $request)
    {
        $requestData = $request->validate([
            'subAmount'               => 'required|numeric',
            'isPartialPaymentAllowed' => 'required',
            'description'             => 'required|string|max:255',
            'source'                  => 'required|string|max:50',
        ]);


        try {
            // Authenticate and get the access token
            $this->authenticate();

            if (!$this->accessToken) {
                return response()->json(['error' => 'Failed to obtain access token'], 500);
            }

            // Create the payment link
            $client = new Client(['base_uri' => 'https://uatoneapi.payu.in', 'timeout' => 10.0]);

            $response = $client->request('POST', '/payment-links', [
                'body'    => json_encode($requestData, JSON_THROW_ON_ERROR),
                'headers' => [
                    'accept'        => 'application/json',
                    'content-type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'merchantId'    => $this->merchantId,
                ],
            ]);

            // Parse the response from PayU
            $responseBody = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            // Extract the necessary data from the response
            $transactionId = $responseBody['result']['invoiceNumber'];

            $user_id = $request->user_id;
            $paymentLink = $responseBody['result']['paymentLink'] ?? null;

            DB::table('payment_transactions')->insert([
                'transaction_id' => $transactionId,
                'gateway'        => 'payu',  // Set according to the gateway used
                'amount'         => $requestData['subAmount'],
                'user_id'        => $user_id,  // Assuming the user is authenticated
                'currency'       => 'INR',  // Default currency
                'status'         => 'pending',
                'response'       => json_encode($responseBody, JSON_THROW_ON_ERROR),  // Store the response from PayU
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);


            // Return the payment link and transaction id to the client
            return response()->json([
                'transaction_id' => $transactionId,
                'payment_link'   => $paymentLink,
            ]);
        } catch (RequestException $e) {
            Log::error('Payment link creation failed', ['error' => $e->getMessage(), 'request' => $requestData]);
            return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        } catch (\JsonException $e) {
            Log::error('Error encoding JSON', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error encoding JSON: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            Log::error('Payment link creation failed', ['error' => $e->getMessage(), 'request' => $requestData]);
            return response()->json(['error' => 'Payment link creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request): ?JsonResponse
    {
        $data = $request->all();
        Log::info('Received callback data', $data);

        try {
            // Verify the callback signature
            if (!$this->verifySignature($data)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Fetch the transaction using the transaction_id
            $transaction = DB::table('payment_transactions')
                ->where('transaction_id', $data['transaction_id'])
                ->first();

            if (!$transaction) {
                // Log missing transaction and return an error
                Log::error('Transaction not found', ['transaction_id' => $data['transaction_id']]);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Update or create transaction record
            DB::table('payment_transactions')
                ->updateOrInsert(
                    ['transaction_id' => $data['transaction_id']],
                    [
                        'status'     => $data['status'],
                        'amount'     => $data['amount'] ?? 0.00,
                        'currency'   => $data['currency'] ?? 'INR',
                        'payment_mode' => $data['payment_mode'] ?? 'Unknown',
                        'response'   => json_encode($data, JSON_THROW_ON_ERROR),
                        'updated_at' => now(),
                    ]
                );

            // Check payment status
            if ($data['status'] === 'SUCCESS') {
                // Fetch the user using user_id from transaction
                $user = User::find($transaction->user_id);
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }

                // Calculate plan expiry based on package
                $package = $transaction->package ?? $user->package;
                if (!$package) {
                    return response()->json(['error' => 'Package not found for the user'], 404);
                }

                $planExpiry = $this->calculatePlanExpiry($package);

                // Update user status, plan expiry, and generate auth token
                $user->update([
                    'status'      => 'active',
                    'plan_expiry' => $planExpiry,
                    'api_token'   => Str::random(60), // Generate a new auth token
                ]);

                // Return success response with user data and token
                return response()->json([
                    'success'     => true,
                    'message'     => 'Payment successful, user activated, and plan assigned.',
                    'user'        => $user,
                    'auth_token'  => $user->api_token,
                ]);
            }

            // Handle payment failure
            return response()->json(['error' => 'Payment failed'], 400);
        } catch (Exception $e) {
            Log::error('Callback handling failed', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function calculatePlanExpiry($package): Carbon
    {
        $planExpiry = now();

        return match ($package) {
            'monthly'   => $planExpiry->addMonth(),
            'quarterly' => $planExpiry->addMonths(3),
            'yearly'    => $planExpiry->addYear(),
            default     => $planExpiry,
        };
    }



    public function verifySignature(): true
    {
        return true;
    }
}
