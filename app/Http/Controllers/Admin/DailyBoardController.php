<?php

namespace App\Http\Controllers\Admin;

use App\Models\ApiKey;
use App\Models\ApiCourt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DailyBoardController extends Controller
{
    public function index() {
        $courts = ApiCourt::all();
        return view(backpack_view('daily_board'), compact('courts'));
    }

    public function fetch(Request $request)
    {
        $courts = ApiCourt::all();

        if (isset($request->court) && $request->court != '') {
            
            $key = ApiKey::first();
            $subject = DB::table('api_keys')->select('display_board_subject')->first();
            if ($key->encryption_key != '' && $subject->display_board_subject != '') {
                $email = encryptDataToString(auth()->user()->email, $key->encryption_key);
                
                $curl = curl_init();
                $data = [
                    "courtId" => $request->court,
                    "subject" => $subject->display_board_subject,
                    "user"    => $email
                ];

                $json = json_encode($data);
                $url  = (env('CASEWISE_URL') != '') ? env('CASEWISE_URL') : "https://test.casewise.in:8443/";
                $pass  = (env('CASEWISE_PASSWORD') != '') ? env('CASEWISE_PASSWORD') : "password";

                $options = [
                    CURLOPT_URL            => $url . 'displayBoard',
                    CURLOPT_POST           => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        "Accept: application/json", "Content-Type: application/json"
                    ],
                    CURLOPT_POSTFIELDS     => $json,
                    
                    CURLOPT_SSLCERT        => public_path('mycert.p12'),
                    CURLOPT_SSLCERTTYPE    => 'P12',
                    CURLOPT_SSLCERTPASSWD  => $pass,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    
                ];
                // Set the options array
                curl_setopt_array($curl, $options);

                // Execute the request
                $response = curl_exec($curl);

                // If an error occurs
                if (curl_errno($curl)) {
                    return redirect()->back()->with('error', 'CURL ERROR - '. curl_error($curl));
                }

                $data = decryptDataToJson($response, $key->encryption_key);

                if (empty($data)) {
                    return redirect()->back()->with('error', "API Error: Response is empty!");
                }

                $data = json_decode($data);

                if (isset($data->status) && $data->status == 500) {
                    $error = "No data found";
                    return view(backpack_view('daily_board'), compact('error', 'courts'));
                }
                
                return view(backpack_view('daily_board'), compact('data', 'courts'));
            } else {
                $error = "Invalid Encryption Key";
                return view(backpack_view('daily_board'), compact('error', 'courts'));
            }
        } else {
            $error = "No Court Selected";
            return view(backpack_view('daily_board'), compact('error', 'courts'));
        }
    } 
}
