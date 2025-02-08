<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class PaymentGatewayController extends Controller
{
    protected PaymentController $paymentController;
    protected PayUController $payuController;

    public function __construct(PaymentController $paymentController, PayUController $payuController)
    {
        $this->paymentController = $paymentController;
        $this->payuController = $payuController;
    }

    public function processPayment(Request $request)
    {
        // Retrieve the active payment gateway from settings
        $activeGateway = Setting::get('payment_gateway', 'cashfree'); // Default to cashfree

        // Delegate to the appropriate controller based on the active gateway
        if ($activeGateway === 'cashfree') {
            return $this->paymentController->createOrder($request);
        } elseif ($activeGateway === 'payu') {
            return $this->payuController->createPaymentLink($request);
        }

        return response()->json(['error' => 'Payment gateway not configured'], 500);
    }
    public function getActiveGateway()
    {
        $activeGateway = Setting::get('payment_gateway', 'cashfree');
        return response()->json(['active_gateway' => $activeGateway]);
    }

    public function setPaymentGateway(Request $request)
    {
        // Validate the input (must be either 'cashfree' or 'payu')
        $validated = $request->validate([
            'payment_gateway' => 'required|in:cashfree,payu',
        ]);

        // Set the active gateway in the 'settings' table
        Setting::set('payment_gateway', $validated['payment_gateway']);

        return response()->json([
            'message' => 'Payment gateway updated successfully!',
            'active_gateway' => $validated['payment_gateway']
        ]);
    }

}
