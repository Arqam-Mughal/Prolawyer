<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadCasesRequest;
use App\Imports\CasesImport;

use App\Models\ApiDistrict;
use App\Models\ApiKey;
use App\Models\ApiState;
use App\Models\CaseAct;
use App\Models\CaseImage;
use App\Models\CaseModel;
use App\Models\CourtList;
use App\Models\District;
use App\Models\HearingDate;
use App\Models\Lawyer;
use App\Models\Organization;
use App\Models\Petitioner;
use App\Models\PetitionerAdvocate;
use App\Models\RefTag;
use App\Models\Respondent;
use App\Models\RespondentAdvocate;
use App\Models\Stage;
use App\Models\User;
use App\Services\CaseService;
use App\Services\EncryptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class CaseController extends Controller
{
    protected $caseService;
    protected $encryptionService;

    public function __construct(CaseService $caseService, EncryptionService $encryptionService)
    {
        $this->caseService       = $caseService;
        $this->encryptionService = $encryptionService;
    }

    public function getActiveCases(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType      = $this->mapCourtType($courtTypeParam);
        $search = $request->get('search', '');

        if (!$courtType) {
            return response()->json(['error' => 'Invalid court type provided.'], 400);
        }

        try {
            $activeCases = $this->caseService->getCases($courtType,$search);
            $apiKey      = ApiKey::first();
            if ($apiKey && $apiKey->case_encryption_key) {
                $key            = $apiKey->case_encryption_key;
                $encryptedCases = $this->caseService->encryptCases($activeCases, $key);
                return response()->json(['active_cases' => $encryptedCases]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch cases. Please try again later.', 'data' => $e->getMessage()], 503);
        }

        return response()->json(['active_cases' => $activeCases]);
    }

    public function getTodayCases(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType      = $this->mapCourtType($courtTypeParam);
        $search = $request->get('search', '');

        $todayCondition = function ($query) {
            $query->whereDate('next_date', Carbon::now()->format('Y-m-d'));
        };

        $todayCases = $this->caseService->getCases($courtType,$search, $todayCondition, 5);
        $apiKey     = ApiKey::first();
        if ($apiKey && $apiKey->case_encryption_key) {
            $key            = $apiKey->case_encryption_key;
            $encryptedCases = $this->caseService->encryptCases($todayCases, $key);
            return response()->json(['today_cases' => $encryptedCases]);
        }

        return response()->json(['today_cases' => $todayCases]);
    }

    public function getTomorrowCases(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType      = $this->mapCourtType($courtTypeParam);
        $search = $request->get('search', '');

        $tomorrowCondition = function ($query) {
            $query->whereDate('next_date', Carbon::tomorrow()->format('Y-m-d'));
        };

        $tomorrowCases = $this->caseService->getCases($courtType,$search, $tomorrowCondition, 5);

        $apiKey        = ApiKey::first();
        if ($apiKey && $apiKey->case_encryption_key) {
            $key            = $apiKey->case_encryption_key;
            $encryptedCases = $this->caseService->encryptCases($tomorrowCases, $key);
            return response()->json(['tomorrow_cases' => $encryptedCases]);
        }

        return response()->json(['tomorrow_cases' => $tomorrowCases]);
    }

    public function getDailyBoardCases($date, $tab, Request $request): JsonResponse
    {
        $courtType = $this->mapCourtType($tab);

        // Create a query builder instance
        $query = $this->caseService->caseQuery($courtType);

        // Retrieve the search term from the request
        $search = $request->get('search', '');

        $dailyBoardCondition = function ($query) use ($date) {
            $query->whereNotNull('history')
                ->where('history', '!=', '')
                ->whereRaw("JSON_VALID(`history`) > 0 AND JSON_SEARCH(`history`, 'all', '".date('Y-m-d', strtotime($date))."', NULL, '$**.date') IS NOT NULL")
                ->orWhereDate('next_date', date('Y-m-d', strtotime($date)));
        };

        // Apply the daily board conditions
        $query->where($dailyBoardCondition);

        // Apply the search condition
        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('case_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('petitioners', function ($q) use ($search) {
                        $q->where('petitioner', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('respondents', function ($q) use ($search) {
                        $q->where('respondent', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        // Log the SQL query
        \Log::info($query->toSql(), $query->getBindings());

        // Execute the query to get the results
        $dailyBoardCases = $query->take(5)->get(); // Adjust to your need (e.g., limit to 5)

        if ($dailyBoardCases->isEmpty()) {
            return response()->json([
                'message' => 'No cases found for the given date and tab.',
                'date' => $date,
                'tab' => $tab,
            ], 404);
        }

        $apiKey = ApiKey::first();
        if ($apiKey && $apiKey->case_encryption_key) {
            $key = $apiKey->case_encryption_key;
            $encryptedCases = $this->caseService->encryptCases($dailyBoardCases, $key);
            return response()->json([
                'dailyboard_cases' => $encryptedCases,
                'date' => $date,
                'tab' => $tab,
            ]);
        }

        return response()->json([
            'dailyboard_cases' => $dailyBoardCases,
            'date' => $date,
            'tab' => $tab,
        ]);
    }

    public function getDateAwaitedCases(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType      = $this->mapCourtType($courtTypeParam);
        $search = $request->get('search', '');

        $dateAwaitedCondition = function ($query) {
            $query->whereDate('next_date', '<', Carbon::now()->format('Y-m-d'))
                ->whereNotNull('next_date');
        };

        $dateAwaitedCases = $this->caseService->getCases($courtType,$search ,$dateAwaitedCondition, 5);
        $apiKey           = ApiKey::first();
        if ($apiKey && $apiKey->case_encryption_key) {
            $key            = $apiKey->case_encryption_key;
            $encryptedCases = $this->caseService->encryptCases($dateAwaitedCases, $key);
            return response()->json(['date_awaited_cases' => $encryptedCases]);
        }

        return response()->json(['date_awaited_cases' => $dateAwaitedCases]);
    }

    public function getArchievedCases(Request $request): JsonResponse
    {
        $status = $request->input('status', 'pending'); // Default to 'pending' if not provided
        $search = $request->input('search', ''); // Retrieve search term

        // Create a base query for archived cases
        $query = CaseModel::where(function ($query) {
            $query->whereNotNull('decided_toggle')
                ->orWhereNotNull('abbondend_toggle');
        });

        // Add condition based on status input
        if ($status === 'decided') {
            $query->where('decided_toggle', 'check');
        } else {
            $query->whereNull('decided_toggle');
        }

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('case_no', 'LIKE', "%{$search}%") // Search by case number
                ->orWhereHas('petitioners', function ($q) use ($search) { // Search petitioners
                    $q->where('petitioner', 'LIKE', "%{$search}%");
                })
                    ->orWhereHas('respondents', function ($q) use ($search) { // Search respondents
                        $q->where('respondent', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('title', 'LIKE', "%{$search}%"); // Search case title if available
            });
        }

        $archiveCases = $query->paginate(5); // Adjust page size as needed

        // Check for API key and encrypt cases if necessary
        $apiKey = ApiKey::first();
        if ($apiKey && $apiKey->case_encryption_key) {
            $key = $apiKey->case_encryption_key;
            $encryptedCases = $this->caseService->encryptCases($archiveCases, $key);
            return response()->json(['archive_cases' => $encryptedCases]);
        }

        return response()->json(['archive_cases' => $archiveCases]);
    }

    public function fetchbycnr(Request $request): JsonResponse
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

        if (!isset($request->cnrid) || $request->cnrid == '') {
            return response()->json(['error' => 'CNR No field is required'], 400);
        }

        $curl = curl_init();

        $settings = ApiKey::first();
        $sub      = ($settings->case_subject != '') ? $settings->case_subject : '';
        $data     = [
            "cino" => $request->cnrid,
            "user" => $this->encryptionService->encryptDataToString(auth()->user()->email, $settings->encryption_key),
            "api"  => 'test'
        ];

        $json = json_encode($data);
        $url  = (env('CASEWISE_URL') != '') ? env('CASEWISE_URL') : "https://test.casewise.in:8443/";
        $pass = (env('CASEWISE_PASSWORD') != '') ? env('CASEWISE_PASSWORD') : "password";

        $options = [
            CURLOPT_URL            => $url.'cnrNumber',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Accept: application/json", "Content-Type: application/json"],
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_SSLCERT        => public_path('mycert.p12'),
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSLCERTPASSWD  => $pass,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            curl_close($curl);
            return response()->json(['error' => 'CURL ERROR - '.curl_error($curl)], 500);
        }

        curl_close($curl);

        // Debugging the response

        // Decrypt and decode the response
        $response = $this->encryptionService->decryptDataToJson($response, $settings->encryption_key);
        $responseArray = json_decode($response, true);

        if (isset($responseArray['status']) && $responseArray['status'] == 500) {
            $msg = $responseArray['error'] ?? 'server is unavailable';
            return response()->json(['error' => 'API '.$msg], 500);
        }

        if (!empty($responseArray)) {
            $stateVal = $responseArray['state_val'] ?? '';
            if ($stateVal != '') {
                Session::put('stateVal', $stateVal);
            }
            if (isset($responseArray['decided']) && $responseArray['decided'] == 'Y') {
                Session::put('decided', 'Y');
            }

            $assigned_to = $responseArray['assigned_to'] ?? '';
            $case_no     = $responseArray['case_no'] ?? '';
            $case_year   = $responseArray['case_year'] ?? '';
            $court_id    = $responseArray['court_id'] ?? '';
            $court_name  = $responseArray['court_name'] ?? '';

            // Return the formatted response
            return response()->json([
                'data'        => $responseArray,
                'assigned_to' => $assigned_to,
                'case_no'     => $case_no,
                'case_year'   => $case_year,
                'court_id'    => $court_id,
                'court_name'  => $court_name,
                'error'       => 0,
                'message'     => 'Data is fetched Successfully.'
            ]);
        }

        return response()->json(['response' => 'No data found'], 404);
    }

    public function fetchByCase(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

        if (!empty($request)) {
            $settings = ApiKey::first();

            $sub = ($settings->case_subject != '') ? $settings->case_subject : '';
            $curl = curl_init();
            $data = [
                "courtId"          => $request->courtId,
                "state_val"        => $request->state_val,
                "district_val"     => $request->district_val,
                "courtComplexEstb" => "0",
                "bench_val"        => $request->bench_val,
                "ct"               => $request->ct,
                "cn"               => $request->cn,
                "cy"               => $request->cy,
                "recordsReturned"  => "1",
                "format"           => "0",
                "api"              => 'test',
                "user"             => $this->encryptionService->encryptDataToString(auth()->user()->email, $settings->encryption_key),
            ];

            $json = json_encode($data);
            $url  = (env('CASEWISE_URL') != '') ? env('CASEWISE_URL') : "https://test.casewise.in:8443/";
            $pass = (env('CASEWISE_PASSWORD') != '') ? env('CASEWISE_PASSWORD') : "password";

            // All options in an array
            $options = [
                CURLOPT_URL            => $url . 'caseNumber',
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

            if (curl_errno($curl)) {
                return response()->json([
                    'error'   => 1,
                    'message' => 'CURL ERROR - ' . curl_error($curl)
                ]);
            }

            // Close to remove $curl from memory
            curl_close($curl);

            // No decryption, use response directly
            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] == 500) {
                $msg = isset($data['error']) ? $data['error'] : 'server is unavailable';
                return response()->json(['error' => 1, 'message' => 'API ' . $msg]);
            }

            $data = isset($data) ? $data : [];

            $petitioners = '';
            $padvocates  = '';
            $respondents = '';
            $radvocates  = '';
            $history     = '';
            $orders      = '';

            if (isset($data) && isset($data['history'])) {
                $history = json_encode($data['history']);
            }

            if (isset($data) && isset($data['orders'])) {
                $orders = json_encode($data['orders']);
            }

            $orders  = str_replace('"', "'", $orders);
            $history = str_replace('"', "'", $history);

            if (isset($data) && isset($data['petitioners'])) {
                foreach ($data['petitioners'] as $key => $petitioner) {
                    $petitioners .= ($key + 1) . ') ' . $petitioner . ' ';
                }
            }

            if (isset($data) && isset($data['padvocates'])) {
                foreach ($data['padvocates'] as $key => $padvocate) {
                    $padvocates .= ($key + 1) . ') ' . $padvocate . ' ';
                }
            }

            if (isset($data) && isset($data['respondents'])) {
                foreach ($data['respondents'] as $key => $respondent) {
                    $respondents .= ($key + 1) . ') ' . $respondent . ' ';
                }
            }

            if (isset($data) && isset($data['radvocates'])) {
                foreach ($data['radvocates'] as $key => $radvocate) {
                    $radvocates .= ($key + 1) . ') ' . $radvocate . ' ';
                }
            }

            if (isset($data) && isset($data['state_val']) && $data['state_val'] != '') {
                $stateVal = $data['state_val'];
                $state = ApiState::where('val', $stateVal)->first();
                $data['state_val'] = $state->id;
            }

            $response = array();
            $response['data']                  = $data;
            $response['data']['history']       = $history;
            $response['data']['orders']        = $orders;
            $response['data']['petitioners']   = $petitioners;
            $response['data']['padvocates']    = $padvocates;
            $response['data']['respondents']   = $respondents;
            $response['data']['radvocates']    = $radvocates;
            $response['data']['petitioners_h'] = implode(',', $data['petitioners']);
            $response['data']['padvocates_h']  = implode(',', $data['padvocates']);
            $response['data']['respondents_h'] = implode(',', $data['respondents']);
            $response['data']['radvocates_h']  = implode(',', $data['radvocates']);
            $response['error']                 = 0;
            $response['message']               = "Data is fetched Successfully.";

            return response()->json($response);
        } else {
            return response()->json(['error' => 1, 'message' => 'Could not found data.']);
        }
    }

    public function updateByCase(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes

        if (!empty($request)) {
            $settings = ApiKey::first();
            $sub = ($settings->case_subject != '') ? $settings->case_subject : '';
            $curl = curl_init();
            $data = [
                "courtId"          => $request->court_id,
                "state_val"        => $request->state_id,
                "district_val"     => $request->district_id,
                "courtComplexEstb" => "0",
                "bench_val"        => $request->bench_id,
                "ct"               => $request->case_type_id,
                "cn"               => $request->case_no,
                "cy"               => $request->case_year,
                "recordsReturned"  => "1",
                "format"           => "0",
                "api"              => $sub,
                "user"             => $this->encryptionService->encryptDataToString(auth()->user()->email, $settings->encryption_key),
            ];

            $json = json_encode($data);
            $url = (env('CASEWISE_URL') != '') ? env('CASEWISE_URL') : "https://test.casewise.in:8443/";
            $pass = (env('CASEWISE_PASSWORD') != '') ? env('CASEWISE_PASSWORD') : "password";

            // All options in an array
            $options = [
                CURLOPT_URL            => $url . 'updateCase', // Assuming 'updateCase' is the endpoint for updating
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

            // Error handling
            if (curl_errno($curl)) {
                return response()->json([
                    'error'   => 1,
                    'message' => 'CURL ERROR - ' . curl_error($curl)
                ]);
            }

            // Close to remove $curl from memory
            curl_close($curl);

            // Decrypt response
            $response = $this->encryptionService->decryptDataToJson($response, $settings->encryption_key);
            $response = json_decode($response, true);

            if (isset($response['status']) && $response['status'] == 500) {
                $msg = isset($response['error']) ? $response['error'] : 'server is unavailable';
                return response()->json(['error' => 1, 'message' => 'API ' . $msg]);
            }

            // Process response as needed for update
            $data = isset($response) ? $response : [];
            $petitioners = '';
            $padvocates  = '';
            $respondents = '';
            $radvocates  = '';
            $history     = '';
            $orders      = '';

            if (isset($data['history'])) {
                $history = json_encode($data['history']);
            }

            if (isset($data['orders'])) {
                $orders = json_encode($data['orders']);
            }

            $orders  = str_replace('"', "'", $orders);
            $history = str_replace('"', "'", $history);

            if (isset($data['petitioners'])) {
                foreach ($data['petitioners'] as $key => $petitioner) {
                    $petitioners .= ($key + 1) . ') ' . $petitioner . ' ';
                }
            }

            if (isset($data['padvocates'])) {
                foreach ($data['padvocates'] as $key => $padvocate) {
                    $padvocates .= ($key + 1) . ') ' . $padvocate . ' ';
                }
            }

            if (isset($data['respondents'])) {
                foreach ($data['respondents'] as $key => $respondent) {
                    $respondents .= ($key + 1) . ') ' . $respondent . ' ';
                }
            }

            if (isset($data['radvocates'])) {
                foreach ($data['radvocates'] as $key => $radvocate) {
                    $radvocates .= ($key + 1) . ') ' . $radvocate . ' ';
                }
            }

            if (isset($data['state_val']) && $data['state_val'] != '') {
                $stateVal = $data['state_val'];
                $state = ApiState::where('val', $stateVal)->first();
                $data['state_val'] = $state ? $state->id : null;
            }

            $responseData = [
                'data'                  => $data,
                'history'               => $history,
                'orders'                => $orders,
                'petitioners'           => $petitioners,
                'padvocates'            => $padvocates,
                'respondents'           => $respondents,
                'radvocates'            => $radvocates,
                'petitioners_h'         => isset($data['petitioners']) ? implode(',', $data['petitioners']) : '',
                'padvocates_h'          => isset($data['padvocates']) ? implode(',', $data['padvocates']) : '',
                'respondents_h'         => isset($data['respondents']) ? implode(',', $data['respondents']) : '',
                'radvocates_h'          => isset($data['radvocates']) ? implode(',', $data['radvocates']) : '',
                'error'                 => 0,
                'message'               => "Data updated successfully."
            ];

            $responseData = base64_encode(json_encode($responseData));

            return response()->json($responseData);
        } else {
            return response()->json(['error' => 1, 'message' => 'No data found.']);
        }
    }

    private function mapCourtType($shorthand)
    {
        $courtTypeMap = [
            'district' => 'District Courts and Tribunals',
            'high'     => 'High Court'
        ];

        return $courtTypeMap[$shorthand] ?? 'District Courts and Tribunals';
    }

    public function updateByCnr(Request $request): JsonResponse
    {
        ini_set('max_execution_time', 300);

        if (isset($request->id) && $request->id > 0) {
            $case = CaseModel::find($request->id);

            if (!$case) {
                return response()->json(['error' => 1, 'message' => 'Could not find case.'], 404);
            }

            $settings      = ApiKey::first();
            $sub           = $settings->case_subject != '' ? $settings->case_subject : '';
            $url           = env('CASEWISE_URL') != '' ? env('CASEWISE_URL') : "https://test.casewise.in:8443/";
            $pass          = env('CASEWISE_PASSWORD') != '' ? env('CASEWISE_PASSWORD') : "password";
            $encryptionKey = $settings->case_encryption_key ?? '';

            $curl = curl_init();
            $data = [
                "cino" => $case->cnr_no,
                "api"  => $sub,
                "user" => $encryptionKey
                    ? $this->encryptionService->encryptDataToString(auth()->user()->email, $encryptionKey)
                    : auth()->user()->email
            ];

            $json    = json_encode($data);
            $options = [
                CURLOPT_URL            => $url.'cnrNumber',
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ["Accept: application/json", "Content-Type: application/json"],
                CURLOPT_POSTFIELDS     => $json,
                CURLOPT_SSLCERT        => public_path('mycert.p12'),
                CURLOPT_SSLCERTTYPE    => 'P12',
                CURLOPT_SSLCERTPASSWD  => $pass,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ];

            curl_setopt_array($curl, $options);
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                curl_close($curl);
                return response()->json([
                    'error'   => 1,
                    'message' => 'CURL ERROR - '.curl_error($curl)
                ], 500);
            }

            curl_close($curl);

            $response = $encryptionKey
                ? $this->encryptionService->decryptDataToJson($response, $encryptionKey)
                : json_decode($response, true);

            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] == 500) {
                $msg = $data['error'] ?? 'server is unavailable';
                return response()->json(['error' => 'API '.$msg], 500);
            }

            $next_date     = $data['listingDate'] ?? '';
            $previous_date = $data['previous_date'] ?? '';
            $state         = $data['state_val'] ?? '';
            $district      = $data['district_val'] ?? '';
            $case_stage    = $data['listingStage'] ?? '';
            $court         = $data['listingJudges'] ?? '';
            $petitioners   = $data['petitioners'] ?? '';
            $padvocates    = $data['padvocates'] ?? '';
            $judge_name    = $data['judgeName'] ?? '';
            $case_no       = $data['cn'] ?? '';
            $history       = $data['history'] ?? [];
            $respondents   = $data['respondents'] ?? '';
            $radvocates    = $data['radvocates'] ?? '';
            $court_room    = $data['courtRoomNo'] ?? '';
            $year          = $data['cy'] ?? '';
            $interlocutory = $data['orders'] ?? [];
            $case_type     = $data['caseTypeStr'] ?? '';
            $sno           = $data['causeLists'] ?? [];

            $updateData = [];
            if (!empty($history)) {
                $updateData['history'] = json_encode($history);
            }
            if (!empty($interlocutory)) {
                $updateData['orders'] = json_encode($interlocutory);
            }
            if ($next_date != '') {
                $updateData['next_date'] = date('Y-m-d', strtotime($next_date));
            }
            if ($previous_date != '') {
                $updateData['previous_date'] = date('Y-m-d', strtotime($previous_date));
            }
            if ($case_stage != '') {
                $updateData['case_stage'] = $case_stage;
            }
            if ($court_room != '') {
                $updateData['court_room_no'] = $court_room;
            }
            if ($judge_name != '') {
                $updateData['judge_name'] = $judge_name;
            }
            if ($case_no != '') {
                $updateData['case_no'] = $case_no;
            }
            if ($state != '') {
                $stateModel             = ApiState::where('val', $state)->first();
                $updateData['state_id'] = $stateModel->id ?? 1;
            }
            if ($district != '') {
                $districtModel             = ApiDistrict::where('val', $district)->first();
                $updateData['district_id'] = $districtModel->id ?? 1;
            }
            if (!empty($sno) && isset($sno[0]['Sno'])) {
                $updateData['sr_no_in_court'] = $sno[0]['Sno'];
            }
            if ($court != '') {
                $updateData['court_bench'] = $court;
            }
            if ($year != '') {
                $updateData['case_year'] = $year;
            }
            if ($case_type != '') {
                $updateData['case_category'] = $case_type;
            }

            CaseModel::whereId($request->id)->update($updateData);

            if ($petitioners && count($petitioners) > 0) {
                Petitioner::where('case_id', $case->id)->delete();
                foreach ($petitioners as $p) {
                    if ($p != '') {
                        Petitioner::create(['case_id' => $case->id, 'petitioner' => $p]);
                    }
                }
            }

            if ($padvocates && count($padvocates) > 0) {
                PetitionerAdvocate::where('case_id', $case->id)->delete();
                foreach ($padvocates as $p) {
                    if ($p != '') {
                        PetitionerAdvocate::create(['case_id' => $case->id, 'petitioner_advocate' => $p]);
                    }
                }
            }

            if ($respondents && count($respondents) > 0) {
                Respondent::where('case_id', $case->id)->delete();
                foreach ($respondents as $r) {
                    if ($r != '') {
                        Respondent::create(['case_id' => $case->id, 'respondent' => $r]);
                    }
                }
            }

            if ($radvocates && count($radvocates) > 0) {
                RespondentAdvocate::where('case_id', $case->id)->delete();
                foreach ($radvocates as $r) {
                    if ($r != '') {
                        RespondentAdvocate::create(['case_id' => $case->id, 'respondent_advocate' => $r]);
                    }
                }
            }

            return response()->json(['error' => 0, 'message' => 'Data has been updated.']);
        }

        return response()->json(['error' => 1, 'message' => 'Could not find case.'], 404);
    }

    public function storeCase(Request $request): JsonResponse
    {
        if (!$request->json()) {
            abort(404);
        }

        // Adjusted validation rules
        $validate_rules = [
            'case_no'             => 'required|unique:cases,case_no|string',
            'petitioner'          => 'required|array',
            'petitioner.*.petitioner_name'   => 'required|string',
            'petitioner.*.petitioner_advocate' => 'sometimes|nullable|string',
            'respondent'          => 'sometimes|array',
            'respondent.*.respondent_name'   => 'required|string',
            'respondent.*.respondent_advocate' => 'sometimes|nullable|string',
        ];

        // Initialize case model
        $model = new \App\Models\CaseModel();
        $model->lawyer_id = auth()->id();

        // Setting status based on judgement (without judgement_date_yes)
        $model->status = 'Open';

        // Fill case model with other fields
        $model->Brief_no      = $request->brief_no ?? '---';
        $model->previous_date = $request->previous_date;
        $model->case_stage    = $request->stage ?? '---';
        $model->sr_no_in_court = $request->sr_no_in_court ?? '---';
        $model->brief_for     = $request->brief_for ?? '---';
        $model->police_station = $request->police_station ?? '---';
        $model->organization_id = $request->organization_id;
        $model->tags = is_array($request->tags) ? implode(',', $request->tags) : $request->tags;
        $model->cnr_no       = $request->cnr_no;
        $model->remarks      = $request->remarks ?? '---';
        $model->notes_description = $request->description ?? '<p>---</p>';
        $model->court_room_no = $request->court_room_no ?? '---';
        $model->judge_name    = $request->judge_name ?? '---';
        $model->state_id      = $request->state_id ?? 1;
        $model->next_date     = $request->next_date;
        $model->district_id   = $request->district_id ?? 1;
        $model->tabs          = $request->tabs;
        $model->court_bench   = $request->court_bench ?? '---';
        $model->case_year     = $request->case_year ?? '---';
        $model->case_category  = $request->case_category ?? '---';
        $model->case_no       = $request->case_no ?? '---';
        $model->client_id     = $request->client_id ?? 0;
        $model->decided_toggle = $request->decided_toggle;
        $model->abbondend_toggle = $request->abbondend_toggle;

        // Save the case model
        $model->save();

        User::whereId(auth()->id())->update([
            'used_cases' => (auth()->user()->used_cases + 1)
        ]);

        // Handle petitioners
        if ($request->petitioner && count($request->petitioner) > 0) {
            foreach ($request->petitioner as $p) {
                if (!empty($p['name'])) {
                    try {
                        // Store petitioner
                        $petitioner = Petitioner::create([
                            'case_id' => $model->id,
                            'petitioner' => $p['name']
                        ]);

                        // Store petitioner advocate
                        if (!empty($p['advocate'])) {
                            PetitionerAdvocate::create([
                                'case_id' => $model->id,
                                'petitioner_advocate' => $p['advocate'],
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to insert petitioner: ' . $e->getMessage());
                    }
                }
            }
        }

        // Handle respondents
        if ($request->respondent && count($request->respondent) > 0) {
            foreach ($request->respondent as $r) {
                if (!empty($r['name'])) {
                    try {
                        // Store respondent
                        $respondent = Respondent::create([
                            'case_id' => $model->id,
                            'respondent' => $r['name']
                        ]);

                        // Store respondent advocate
                        if (!empty($r['advocate'])) {
                            RespondentAdvocate::create([
                                'case_id' => $model->id,
                                'respondent_advocate' => $r['advocate'],
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to insert respondent: ' . $e->getMessage());
                    }
                }
            }
        }

        // Handle file uploads
        if ($request->file) {
            foreach ($request->file as $file) {
                // Create the directory if it doesn't exist
                $path = './assets/images/case/' . date("Y") . '/' . date("m") . '/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                // Handle base64-encoded file content
                if (isset($file['content']) && isset($file['filename'])) {
                    $filename = pathinfo($file['filename'], PATHINFO_FILENAME);
                    $extension = pathinfo($file['filename'], PATHINFO_EXTENSION);
                    $uniqueFilename = time() . Str::uuid() . '-' . $filename . '.' . $extension;

                    // Decode and save the file
                    $fileContent = base64_decode($file['content']);
                    file_put_contents($path . $uniqueFilename, $fileContent);

                    // Save file info to the database
                    CaseImage::create([
                        'case_id' => $model->id,
                        'path' => $path,
                        'image' => $uniqueFilename
                    ]);
                } else {
                    return response()->json(['message' => 'Invalid file structure.', 'success' => false]);
                }
            }
        }

        // Handle case status based on toggle
        if ((isset($request->decided_toggle) && $request->decided_toggle == 'check') ||
            (isset($request->abbondend_toggle) && $request->abbondend_toggle == 'check')) {
            return response()->json([
                'message' => 'Case added to archive successfully.',
                'case_id' => $model->id,
                'success' => true
            ]);
        }

        return response()->json([
            'message' => 'Case added successfully.',
            'case_id' => $model->id,
            'success' => true
        ]);
    }

    public function updateCase(Request $request, $id): JsonResponse
    {
        $case = CaseModel::find($id);
        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        $validate_rules = [
            'case_no' => 'sometimes|nullable|string',
        ];

        $request->validate($validate_rules);

        // Removed unused date variables and logic
        $hearing_date = null;

        if ($request->hearing_date) {
            $hearing_date = date_format(date_create($request->hearing_date), 'Y-m-d H:i:s');
        }

        // Case status handling based on judgement toggle
        if ($request->decided_toggle) {
            $case->judgement_status = 'Judgement';
            $case->status           = 'Judgement';
        } else {
            $case->status = 'Open';
        }

        // Update the case model with the relevant fields
        $case->Brief_no          = $request->brief_no ?? '---';
        $case->previous_date     = $request->previous_date;
        $case->case_stage        = $request->stage ?? '---';
        $case->sr_no_in_court    = $request->sr_no_in_court ?? '---';
        $case->brief_for         = $request->brief_for ?? '---';
        $case->police_station    = $request->police_station ?? '---';
        $case->organization_id   = $request->organization_id;
        $case->tags              = is_array($request->tags) ? implode(',', $request->tags) : $request->tags;
        $case->cnr_no            = $request->cnr_no;
        $case->remarks           = $request->remarks ?? '---';
        $case->notes_description = $request->description ?? '<p>---</p>';
        $case->court_room_no     = $request->court_room_no ?? '---';
        $case->judge_name        = $request->judge_name ?? '---';
        $case->state_id          = $request->state_id ?? 1;
        $case->next_date         = $request->next_date;
        $case->district_id       = $request->district_id ?? 1;
        $case->tabs              = $request->tabs;
        $case->court_bench       = $request->court_bench ?? '---';
        $case->case_year         = $request->case_year ?? '---';
        $case->case_category     = $request->case_category ?? '---';
        $case->case_no           = $request->case_no ?? '---';
        $case->client_id         = $request->client_id;
        $case->decided_toggle    = $request->decided_toggle;
        $case->abbondend_toggle  = $request->abbondend_toggle;

        // Check and store new court bench if it doesn't exist
        if (isset($request->court_bench) && $request->court_bench != '') {
            if (!CourtList::where(['long_form' => $request->court_bench])->exists()) {
                CourtList::create(['long_form' => $request->court_bench]);
            }
        }

        // Update case dates
        $case->hearing_date = $hearing_date;

        // Save the updated case model
        $case->save();

        User::whereId(auth()->id())->update([
            'used_cases' => (auth()->user()->used_cases + 1)
        ]);

        // Update petitioners
        Petitioner::where('case_id', $case->id)->delete();
        if ($request->petitioner && count($request->petitioner) > 0) {
            foreach ($request->petitioner as $p) {
                if (!empty($p['name'])) {
                    $petitioner = Petitioner::updateOrCreate(
                        ['case_id' => $case->id, 'petitioner' => $p['name']],
                        ['petitioner' => $p['name']]
                    );

                    if (!empty($p['advocate'])) {
                        PetitionerAdvocate::updateOrCreate(
                            ['case_id' => $case->id, 'petitioner_advocate' => $p['advocate']],
                            ['petitioner_advocate' => $p['advocate']]
                        );
                    }
                }
            }
        }

        // Update respondents
        Respondent::where('case_id', $case->id)->delete();
        if ($request->respondent && count($request->respondent) > 0) {
            foreach ($request->respondent as $r) {
                if (!empty($r['name'])) {
                    $respondent = Respondent::updateOrCreate(
                        ['case_id' => $case->id, 'respondent' => $r['name']],
                        ['respondent' => $r['name']]
                    );

                    if (!empty($r['advocate'])) {
                        RespondentAdvocate::updateOrCreate(
                            ['case_id' => $case->id, 'respondent_advocate' => $r['advocate']],
                            ['respondent_advocate' => $r['advocate']]
                        );
                    }
                }
            }
        }

        // Update hearing dates
        HearingDate::where('cases_id', $case->id)->delete();
        if ($request->hearing_date) {
            HearingDate::create([
                'cases_id'    => $case->id,
                'stage_id'    => $request->stage_id,
                'date'        => $hearing_date,
                'description' => $request->description,
            ]);
        }

        // Update case images
        CaseImage::where('case_id', $case->id)->delete();
        if ($request->file) {
            foreach ($request->file as $file) {
                $path = './assets/images/case/' . date("Y") . '/' . date("m") . '/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                if (isset($file['content']) && isset($file['filename'])) {
                    $filename = pathinfo($file['filename'], PATHINFO_FILENAME);
                    $extension = pathinfo($file['filename'], PATHINFO_EXTENSION);
                    $uniqueFilename = time() . Str::uuid() . '-' . $filename . '.' . $extension;

                    $fileContent = base64_decode($file['content']);
                    file_put_contents($path . $uniqueFilename, $fileContent);

                    CaseImage::create([
                        'case_id' => $case->id,
                        'path'    => $path,
                        'image'   => $uniqueFilename,
                    ]);
                } else {
                    return response()->json(['message' => 'Invalid file structure.', 'success' => false]);
                }
            }
        }

        return response()->json([
            'message' => 'Case updated successfully.',
            'case_id' => $case->id,
            'success' => true
        ]);
    }

    public function deleteCase($id): JsonResponse
    {
        $case = CaseModel::find($id);
        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        $case->delete();
        return response()->json(['message' => 'Case deleted successfully.']);
    }

    public function bulkUploadCases(BulkUploadCasesRequest $request): JsonResponse
    {
        $file = $request->file('file');
        Excel::import(new CasesImport, $file);

        return response()->json(['message' => 'Cases uploaded successfully.', 'success' => true]);
    }

    public function allCases(Request $request): JsonResponse
    {

        $courtTypeParam = $request->get('type', 'district');
        $courtType = $this->mapCourtType($courtTypeParam);
        $searchQuery = $request->get('search', null);

        $search = $request->get('search', '');

        try {
            $activeCases = $this->caseService->getallCases($courtType, null, 10, $searchQuery);
            $apiKey = ApiKey::first();
            if ($apiKey && $apiKey->case_encryption_key) {
                $key = $apiKey->case_encryption_key;
                $encryptedCases = $this->caseService->encryptCases($activeCases, $key);
                return response()->json(['all_cases' => $encryptedCases]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch cases. Please try again later.',
                'data' => $e->getMessage()
            ], 503);
        }

        return response()->json(['all_cases' => $activeCases]);
    }

//    public function getDashboardData(Request $request): JsonResponse
//    {
//
//    }
    public function getDashboardData(Request $request): JsonResponse
    {
        try {
            // Active cases counts
            $districtActiveCases = $this->caseService->countCases('District Courts and Tribunals');
            $highCourtActiveCases = $this->caseService->countCases('High Court');

            // Today's cases counts
            $todayCondition = function ($query) {
                $query->whereDate('next_date', Carbon::now()->format('Y-m-d'));
            };
            $districtTodayCases = $this->caseService->countCases('District Courts and Tribunals', $todayCondition);
            $highCourtTodayCases = $this->caseService->countCases('High Court', $todayCondition);

            // Tomorrow's cases counts
            $tomorrowCondition = function ($query) {
                $query->whereDate('next_date', Carbon::tomorrow()->format('Y-m-d'));
            };
            $districtTomorrowCases = $this->caseService->countCases('District Courts and Tribunals', $tomorrowCondition);
            $highCourtTomorrowCases = $this->caseService->countCases('High Court', $tomorrowCondition);

            // Date Awaited cases counts
            $dateAwaitedCondition = function ($query) {
                $query->whereDate('next_date', '<', Carbon::now()->format('Y-m-d'))
                    ->whereNotNull('next_date');
            };
            $districtDateAwaitedCases = $this->caseService->countCases('District Courts and Tribunals', $dateAwaitedCondition);
            $highCourtDateAwaitedCases = $this->caseService->countCases('High Court', $dateAwaitedCondition);

            // Daily Board cases counts
            $dailyBoardCondition = function ($query) {
                $query->whereNotNull('history')
                    ->where('history', '!=', '')
                    ->whereRaw("JSON_VALID(`history`) > 0 AND JSON_SEARCH(`history`, 'all', '".date('d-m-Y', strtotime(Carbon::now()->format('Y-m-d')))."', NULL, '$**.date') IS NOT NULL")
                    ->orWhereDate('next_date', Carbon::now()->format('Y-m-d'))
                    ->orWhereDate('previous_date', Carbon::now()->format('Y-m-d'));
            };
            $districtDailyBoardCases = $this->caseService->countCases('District Courts and Tribunals', $dailyBoardCondition);
            $highCourtDailyBoardCases = $this->caseService->countCases('High Court', $dailyBoardCondition);

            // Archives (static value as per your example, modify this logic as needed)
            $districtArchivesCases = 24;
            $highCourtArchivesCases = 13;

            // Prepare the response
            $dashboardData = [
                [
                    'id'              => uniqid(),
                    'title'           => 'Active Cases',
                    'districtCases'   => $districtActiveCases,
                    'highCourtCases'  => $highCourtActiveCases,
                ],
                [
                    'id'              => uniqid(),
                    'title'           => "Today's Cases",
                    'districtCases'   => $districtTodayCases,
                    'highCourtCases'  => $highCourtTodayCases,
                ],
                [
                    'id'              => uniqid(),
                    'title'           => "Tomorrow's Cases",
                    'districtCases'   => $districtTomorrowCases,
                    'highCourtCases'  => $highCourtTomorrowCases,
                ],
                [
                    'id'              => uniqid('', true),
                    'title'           => 'Date Awaited Cases',
                    'districtCases'   => $districtDateAwaitedCases,
                    'highCourtCases'  => $highCourtDateAwaitedCases,
                ],
                [
                    'id'              => uniqid(),
                    'title'           => 'Daily Board',
                    'districtCases'   => $districtDailyBoardCases,
                    'highCourtCases'  => $highCourtDailyBoardCases,
                ],
                [
                    'id'              => uniqid(),
                    'title'           => 'Archives',
                    'districtCases'   => $districtArchivesCases,
                    'highCourtCases'  => $highCourtArchivesCases,
                ],
            ];

            return response()->json($dashboardData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch dashboard data. Please try again later.', 'data' => $e->getMessage()], 503);
        }
    }

    public function updateAssignedTo(Request $request, $case_id)
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the case by case_id
        $case = CaseModel::find($case_id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        // Update the assigned_to field
        $case->assigned_to = $request->assigned_to;
        $case->save();

        return response()->json(['message' => 'Case assigned to user successfully.']);
    }

    public function assignUserToCase(Request $request, $case_id)
    {
        // Validate the input for 'assigned_to' to ensure the user exists
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id', // 'assigned_to' must exist in users table
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find the case by the provided case_id
        $case = CaseModel::find($case_id);

        // If case not found, return an error response
        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        // Update the assigned_to field with the user ID from the request
        $case->assigned_to = $request->assigned_to;
        $case->save(); // Save the changes to the database

        // Return a success response
        return response()->json([
            'message' => 'User successfully assigned to case.',
            'assigned_to' => $case->assigned_to
        ], 200);
    }

    public function getAssignedLawyers($userId): JsonResponse
    {
        // Find the logged-in user
        $loggedInUser = User::find($userId);

        // Check if the user exists
        if (!$loggedInUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Determine the senior lawyer ID (if exists) or use the logged-in user's ID
        $seniorLawyerId = $loggedInUser->senior_lawyer_id ?: $userId;

        // Retrieve all users with the same senior lawyer ID
        $assignedLawyers = User::select('id', 'name', 'email') // Adjust fields as needed
        ->where('senior_lawyer_id', $seniorLawyerId)
            ->orWhere('id', $seniorLawyerId)
            ->get();

        // Check if there are any lawyers assigned
        if ($assignedLawyers->isEmpty()) {
            return response()->json(['message' => 'No assigned lawyers found'], 404);
        }

        // Return the list of assigned lawyers
        return response()->json(['assigned_lawyers' => $assignedLawyers]);
    }

    public function getCasesByLabel(Request $request, $label_id)
    {
        // Validate the label_id
        $validator = Validator::make(['label_id' => $label_id], [
            'label_id' => 'required|exists:case_labels,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Retrieve cases associated with the label
        $cases = DB::table('cases')
            ->join('case_labels_case', 'cases.id', '=', 'case_labels_case.case_id')
            ->where('case_labels_case.case_label_id', $label_id)
            ->select('cases.*')
            ->get();

        if ($cases->isEmpty()) {
            return response()->json(['message' => 'No cases found for the given label.'], 404);
        }

        return response()->json($cases);
    }

    public function addLabelToCase(Request $request, $case_id)
    {
        // Validate the request data
        $validator = Validator::make(array_merge($request->all(), ['case_id' => $case_id]), [
            'label_id' => 'required|exists:case_labels,id',
            'case_id' => 'required|exists:cases,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Insert the label to the case
        DB::table('case_labels_case')->insert([
            'case_id' => $case_id,
            'case_label_id' => $request->label_id,
        ]);

        return response()->json(['message' => 'Label added to case successfully.']);
    }

    public function storeLabel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Insert the label into the case_labels table
        $labelId = DB::table('case_labels')->insertGetId([
            'label' => $request->label,
            'created_at'=>Carbon::now(),

        ]);

        return response()->json(['message' => 'Label added successfully.', 'label_id' => $labelId]);
    }

    public function getAllLabels()
    {
        $labels = DB::table('case_labels')->get();
        return response()->json($labels);
    }

    public function getLabel($label_id)
    {
        $label = DB::table('case_labels')->find($label_id);

        if (!$label) {
            return response()->json(['message' => 'Label not found.'], 404);
        }

        return response()->json($label);
    }

    //get notes description

    public function getNotesDescription($case_id)
    {
        $case = CaseModel::find($case_id);

        if (!$case) {
            return response()->json(['message' => 'Case not found.'], 404);
        }

        return response()->json(['notes_description' => $case->notes_description]);
    }

    public function deleteLabel($label_id): JsonResponse
    {
        $label = DB::table('case_labels')->find($label_id);

        if (!$label) {
            return response()->json(['message' => 'Label not found.'], 404);
        }

        DB::table('case_labels')->delete($label_id);

        return response()->json(['message' => 'Label deleted successfully.']);
    }

    public function storeNotesDescription(Request $request, $case_id)
    {
        // Validate the request data
        $validator = Validator::make(array_merge($request->all(), ['case_id' => $case_id]), [
            'notes_description' => 'required|string',
            'case_id' => 'required|exists:cases,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the notes_description in the cases table
        $case = CaseModel::find($case_id);
        $case->notes_description = $request->notes_description;
        $case->save();

        return response()->json(['message' => 'Notes description updated successfully.']);
    }

    public function getCaseTabs()
    {
        // Retrieve unique names from the tabs column in the cases table
        $uniqueTabs = DB::table('cases')
            ->select('tabs')
            ->distinct()
            ->pluck('tabs')
            ->filter(function ($value) {
                return !is_null($value);
            })
            ->values();

        return response()->json($uniqueTabs);
    }

    public function allCasesCalender(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType = $this->mapCourtType($courtTypeParam);

        // Get date range from request
        $startDate = $request->get('start_date'); // e.g., '2024-01-01'
        $endDate = $request->get('end_date'); // e.g., '2024-01-31'

        // Validate date format
        if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 400);
        }

        try {
            // Fetch and transform active cases
            $activeCases = $this->caseService->getAllCasesByDateRange($courtType, $startDate, $endDate);

            // Transform the active cases to include the title
            $activeCases->transform(function ($case) {
                $case->title = $case->petitioners->implode('petitioner', ', ') . ' VS ' . $case->respondents->implode('respondent', ', ');
                return $case;
            });

            $apiKey = ApiKey::first();

            if ($apiKey && $apiKey->case_encryption_key) {
                $key = $apiKey->case_encryption_key;
                $encryptedCases = $this->caseService->encryptCases($activeCases, $key);
                return response()->json(['total_cases' => $encryptedCases]);
            }

            return response()->json(['total_cases' => $activeCases]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch cases. Please try again later.', 'data' => $e->getMessage()], 503);
        }
    }

    public function countCases(Request $request): JsonResponse
    {
        $courtTypeParam = $request->get('type', 'district');
        $courtType = $this->mapCourtType($courtTypeParam);

        // Get date range from request
        $startDate = $request->get('start_date'); // e.g., '2024-01-01'
        $endDate = $request->get('end_date'); // e.g., '2024-01-31'

        // Validate date format
        if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 400);
        }

        try {
            // Fetch all cases in the date range
            $cases = $this->caseService->getCases($courtType, [
                ['cases.created_at', '>=', $startDate],
                ['cases.created_at', '<=', $endDate],
            ], PHP_INT_MAX); // Fetch all cases without pagination

            // Process cases to group by date
            $dailyCounts = [];
            foreach ($cases->items() as $case) {
                $caseDate = (new \DateTime($case->created_at))->format('Y-m-d');
                if (!isset($dailyCounts[$caseDate])) {
                    $dailyCounts[$caseDate] = 0;
                }
                $dailyCounts[$caseDate]++;
            }

            // Format the result as an array of dates and counts, excluding zero counts
            $result = [];
            foreach ($dailyCounts as $date => $count) {
                if ($count > 0) {
                    $result[] = [
                        'date' => $date,
                        'cases_count' => $count,
                    ];
                }
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to count cases. Please try again later.', 'data' => $e->getMessage()], 503);
        }
    }

    // Helper function to validate date format
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

}
