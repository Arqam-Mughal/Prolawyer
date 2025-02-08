<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use http\Client\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Used to return success response
     * @return \Illuminate\Http\JsonResponse
     */

    public function ok($items = null) {
        return response()->json($items)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Used to return success response
     * @return \Illuminate\Http\JsonResponse
     */

    public function success($items = null, $status = 200) {
        $data = ['status' => 'success'];

        if ($items instanceof Arrayable) {
            $items = $items->toArray();
        }

        if ($items) {
            foreach ($items as $key => $item) {
                $data[$key] = $item;
            }
        }
        return response()->json($data, $status)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Used to return error response
     * @return \Illuminate\Http\JsonResponse
     */

    public function error($items = null, $status = 500) {
        $data = array();

        if ($items) {
            foreach ($items as $key => $item) {
                $data['errors'][$key][] = $item;
            }
        }

        return response()->json($data, $status)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function demoCheck()
    {
        if(\Illuminate\Support\Facades\Config::get('app.app_sync')){

            return true;
        }

        return false;

    }

    public function getStripeDetails()
    {
        $data = ApiKey::first();

        return [
            'STRIPE_KEY'    => isset($data->stripe_publishable_key) ? $data->stripe_publishable_key : '',
            'STRIPE_SECRET' => isset($data->stripe_secret_key) ? $data->stripe_secret_key : ''
        ];
    }

    public function getCCAvenueDetails()
    {
        $data = ApiKey::first();

        return [
            'CCAVENUE'             => isset($data->ccavenue) ? $data->ccavenue : '',
            'CCAVENUE_KEY'         => isset($data->ccavenue_key) ? $data->ccavenue_key : '',
            'CCAVENUE_ACCESS_CODE' => isset($data->ccavenue_access_code) ? $data->ccavenue_access_code : '',
            'CCAVENUE_MERCHANT_ID' => isset($data->ccavenue_merchant_id) ? $data->ccavenue_merchant_id : ''
        ];
    }

    public function redirectIfPlanExpired($user)
    {
        if (isset($user) && !empty($user)) {
            $role = Role::whereId($user->role_id)->first();
            $plan_expiry = $user->plan_expiry;
            $today       = date("Y-m-d H:i:s");

            if (strtotime($today) > strtotime($plan_expiry) && $role->type != 'system_user') {
                return 'expired';
            }
        }
    }

    public function redirectIfAuthorized($user)
    {
        if (isset($user) && !empty($user)) {
            $currentRoute = \Request::route()->getName();
            $role         = Role::whereId($user->role_id)->first();
            $except       = ['change_password','password.change'];
            $related      = ['completed-task' => 'task.index', 'my-task' => 'task.index'];
            $byIds        = ['case.index' => 818, 'case.create' => 337, 'court_fee_cal' => 845];

            if (isset($related[$currentRoute])) {
                $currentRoute = $related[$currentRoute];
            }

            if ($role->type == 'system_user' || in_array($currentRoute, $except)) {
                return 'allowed';
            }

            if (!isset($byIds[$currentRoute])) {
                $permission = Permission::whereRoute($currentRoute)->first();
            } else {
                $permission = Permission::whereId($byIds[$currentRoute])->first();
            }

            if ($permission) {
                $userPermission = \DB::table('role_permission')
                    ->select(\DB::raw('GROUP_CONCAT(permission_id) AS permissions'))
                    ->where('role_id', $user->role_id)
                    ->first();

                if (in_array($permission->id, explode(',', $userPermission->permissions))) {
                    return 'allowed';
                } else {
                    return 'denied';
                }
            } else {
                return 'allowed';
            }
        }
    }
}
