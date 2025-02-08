<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Transaction::query();

        if (!$user->hasRole('Super admin')) {
            $query->where('user_id', $user->id);
        }
        $transactions = $query->paginate(15);

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No transactions found for this user'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'data' => TransactionResource::collection($transactions),
            'message' => 'Transactions retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required|in:1,2',
            'order_id' => 'nullable|integer',
            'tracking_id' => 'nullable|integer',
            'payment_mode' => 'nullable|string',
            'billing_info' => 'nullable|string',
            'trans_date' => 'nullable|date',
            'discount_value' => 'nullable|numeric',
            'stripe_charge_id' => 'nullable|string|max:64',
            'payment_intent' => 'nullable|string',
            'payment_intent_client_secret' => 'nullable|string',
            'charged_amount' => 'required|numeric',
            'description' => 'nullable|string',
            'plan_id' => 'nullable|exists:roles,id',
            'plan_validity' => 'nullable|string|max:45',
        ]);

        $transaction = Transaction::create($validatedData);
        return response()->json([
            'status' => 'success',
            'data' => new TransactionResource($transaction),
            'message' => 'Transaction created successfully'
        ], Response::HTTP_CREATED);
    }


    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        if (!$user->hasRole('Super admin') && $transaction->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to view this transaction.'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'status' => 'success',
            'data' => new TransactionResource($transaction->load(['user', 'plan'])),
            'message' => 'Transaction retrieved successfully'
        ]);
    }


    public function update(Request $request, Transaction $transaction)
    {
        $validatedData = $request->validate([
            'payment_method' => 'sometimes|in:1,2',
            'order_id' => 'nullable|integer',
            'tracking_id' => 'nullable|integer',
            'payment_mode' => 'nullable|string',
            'billing_info' => 'nullable|string',
            'trans_date' => 'nullable|date',
            'discount_value' => 'nullable|numeric',
            'stripe_charge_id' => 'nullable|string|max:64',
            'payment_intent' => 'nullable|string',
            'payment_intent_client_secret' => 'nullable|string',
            'charged_amount' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'plan_id' => 'nullable|exists:roles,id',
            'plan_validity' => 'nullable|string|max:45',
        ]);

        $transaction->update($validatedData);
        return response()->json([
            'status' => 'success',
            'data' => new TransactionResource($transaction),
            'message' => 'Transaction updated successfully'
        ]);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully'
        ], Response::HTTP_OK);
    }
}
