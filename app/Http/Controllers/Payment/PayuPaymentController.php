<?php

namespace App\Http\Controllers\Payment;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class PayuPaymentController extends Controller
{

    public function store(Request $request)
{
    // Replace the items coming from Cashfree with PayU equivalent
    $raw = str_replace('amp;', '', $request->items);
    parse_str($raw, $data);

    Cache::put('user_register_data', $data);

    $payu_merchant_id = '3GFWvb';
    $payu_salt = 'LexCKtuurraR7yFkdIC45kGzMRdEyLK7';
    $payu_key = '3GFWvb';

    // $payu_merchant_id = 'e5YhzV';
    // $payu_salt = 'VOA4MFu7E2V6Tf5zuokESFWXS5Bbr2VP';
    // $payu_key = 'e5YhzV';

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
    $customToken = 'payu_custom_token'.bin2hex(random_bytes(32));  // Generate a random token
    Cache::put($customToken, $data);

    $order_data = [
        'txnid' => 'txn_' . rand(1111111111, 9999999999),
        'amount' => $price,
        'firstname' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'productinfo' => 'User Registration Fee',
        //  'surl' => env('APP_URL').'/payment/payu/success',
        // 'furl' => env('APP_URL').'/payment/payu/failure',
        'surl' => env('APP_URL').'/payment/payu/success?tokenCustom='.$customToken,
        'furl' => env('APP_URL').'/payment/payu/failure?tokenCustom='.$customToken,
        'service_provider' => 'payu_paisa',
        'key' => $payu_key,
        'merchant_id' => $payu_merchant_id,
    ];

    $hash = $this->generatePayUHash($order_data, $payu_salt);

    $order_data['hash'] = $hash;

    $request_payload = [
        'key' => $order_data['key'],
        'txnid' => $order_data['txnid'],
        'amount' => $order_data['amount'],
        'firstname' => $order_data['firstname'],
        'email' => $order_data['email'],
        'phone' => $order_data['phone'],
        'productinfo' => $order_data['productinfo'],
        'surl' => $order_data['surl'],
        'furl' => $order_data['furl'],
        'hash' => $order_data['hash'],
    ];

    return response()->json(['status'=>'success','order_data' => $request_payload]);
}

public function successs(Request $request)
{
    $response = $request->all();

        if ($response['status'] == 'success') {
            if (Cache::has($request->tokenCustom)) {
                $data = Cache::get($request->tokenCustom);
                $role = explode('-', $data['role_id']);
                $role_id = $role[0];
                $price = 0;
                $expiry = '';
                $plan = Role::find($data['plan_id']);
                $validity = '';

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
                $transaction->payment_method = 2;
                $transaction->order_id = $response['txnid'];
                $transaction->trans_date = now();
                $transaction->charged_amount = $response['amount'];
                $transaction->description = 'User registration Fee ₹ ' . $response['amount'] . '.00';
                $transaction->plan_id = $role_id;
                $transaction->plan_validity = $validity;
                $transaction->save();

                Cache::forget($request->tokenCustom);
                Session::flash('success', 'User Registered Successfully!');

                return redirect()->route('backpack.auth.login');
            }
        } else {
            return response()->json(['error' => 'Payment not completed successfully'], 400);
        }

}

// Failure handler after PayU payment
public function failure(Request $request)
{
    Session::flash('error','Payment failed or was cancelled by the user');
    return redirect('/');
}

public function generatePayUHash($data, $salt)
{
        $input = $data['key'] . '|' .  $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '|||||||||||' . $salt;
        return hash('sha512', $input);
}


}
