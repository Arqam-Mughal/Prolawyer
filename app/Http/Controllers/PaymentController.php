<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Cashfree\Cashfree;
use Cashfree\Model\CreateOrderRequest;
use Cashfree\Model\CustomerDetails;

class PaymentController extends Controller
{
    /**
     * Create a new order and return payment link.
     */
    public function createOrder(Request $request)
    {
        // Validate request
        $validatedData = $request->validate([
            'orderAmount'   => 'required|numeric',
            'customerPhone' => 'required|string',
            'customerName'  => 'required|string',
            'customerEmail' => 'required|string',
        ]);

        // Retrieve app ID and secret key from config
        $clientId     = env('CASHFREE_APP_ID');
        $clientSecret = env('CASHFREE_SECRET_KEY');
        $environment  = Cashfree::$SANDBOX;

        // Initialize Cashfree SDK
        Cashfree::$XClientId     = $clientId;
        Cashfree::$XClientSecret = $clientSecret;
        Cashfree::$XEnvironment  = $environment;

        $cashfree = new Cashfree();

        $x_api_version = "2022-09-01";

        // Prepare link
        $link_customer_details_entity = new \Cashfree\Model\LinkCustomerDetailsEntity();
        $link_customer_details_entity->setCustomerName($validatedData['customerName']);
        $link_customer_details_entity->setCustomerPhone($validatedData['customerPhone']);
        $link_customer_details_entity->setCustomerEmail($validatedData['customerEmail']);

        $link_notify_entity = new \Cashfree\Model\LinkNotifyEntity();
        $link_notify_entity->setSendSms(false);
        $link_notify_entity->setSendEmail(true);
        $link_meta_response_entity = new \Cashfree\Model\LinkMetaResponseEntity();
        $link_meta_response_entity->setNotifyUrl("https://ee08e626ecd88c61c85f5c69c0418cb5.m.pipedream.net");
        $link_meta_response_entity->setUpiIntent(false);
        $link_meta_response_entity->setReturnUrl("https://b8af79f41056.eu.ngrok.io");

        $create_link_request = new \Cashfree\Model\CreateLinkRequest();
        $create_link_request->setLinkId(uniqid());
        $create_link_request->setLinkAmount($validatedData['orderAmount']);
        $create_link_request->setLinkCurrency("INR");
        $create_link_request->setLinkPurpose("Payment for PlayStation 11"); // eslai ni dynamic banauna parla
        $create_link_request->setCustomerDetails($link_customer_details_entity);

        $create_link_request->setLinkPartialPayments(true);
        $create_link_request->setLinkMinimumPartialAmount(10);
        $create_link_request->setLinkExpiryTime("2025-08-30T19:14:24+05:30");
        $create_link_request->setLinkNotify($link_notify_entity);
        $create_link_request->setLinkAutoReminders(false);
        $create_link_request->setLinkNotes(["key_1" => "value_1", "key_2" => "value_2"]);
        $create_link_request->setLinkMeta($link_meta_response_entity);

        // la bhayo hai full moj
        try {
            $result = $cashfree->PGCreateLink($x_api_version, $create_link_request, null, null, null);
            return response()->json($result);
        } catch (Exception $e) {
            echo 'Exception when calling PGCreateLink: ', $e->getMessage(), PHP_EOL;
        }

        // try {
        //     $result = $cashfree->PGCreateOrder($x_api_version, $create_orders_request);

        //     return $result;
        //     $paymentLink = $result->payment_link; // Extract payment link from the response

        //     // Return the payment link in the response
        //     return response()->json([
        //         'payment_link' => $paymentLink
        //     ]);
        // } catch (Exception $e) {
        //     // Handle exception
        //     return response()->json([
        //         'error' => 'Failed to create order: ' . $e->getMessage()
        //     ], 500);
        // }
    }

    /**
     * Handle success callback.
     */
    public function handleSuccess()
    {
        // Handle success callback
        return response()->json(['status' => 'success', 'message' => 'Payment Successful']);
    }

    /**
     * Handle notification callback.
     */
    public function handleNotify(Request $request)
    {
        // Handle notification callback
        // Validate and process the payment notification here
        // Log the notification or process as needed

        return response()->json(['status' => 'success']);
    }
}
