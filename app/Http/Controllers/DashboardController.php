<?php
namespace App\Http\Controllers;

use App\Models\ApiBench;
use App\Models\ApiCaseType;
use App\Models\ApiCourt;
use App\Models\ApiDistrict;
use App\Models\ApiState;
use App\Models\CaseModel;
use App\Models\Court;
use App\Models\CourtList;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        $districtCourtType = 'district';
        $highCourtType = 'high';

        // Get current date
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Fetch active cases
        $activeCases = $this->getCaseCount('Open');

        // Fetch today's cases
        $todaysCases = $this->getCaseCount('Open', ['hearing_date' => $today]);

        // Fetch tomorrow's cases
        $tomorrowsCases = $this->getCaseCount('Open', ['hearing_date' => $tomorrow]);

        // Fetch date awaited cases (no hearing date set)
        $dateAwaitedCases = $this->getCaseCount('Open', ['hearing_date' => null]);

        // Fetch daily board cases (cases to be heard today or tomorrow)
        $dailyBoardCases = $this->getCaseCount('Open', [
            ['hearing_date', '>=', $today],
            ['hearing_date', '<=', $tomorrow]
        ]);

        // Fetch archived cases
        $archivedCases = $this->getCaseCount('Closed');

        // Create the dashboard response structure
        $dashboardData = [
            [
                'id' => uniqid(),
                'title' => 'Active Cases',
                'districtCases' => $activeCases['district'] ?? 0,
                'highCourtCases' => $activeCases['high'] ?? 0,
            ],
            [
                'id' => uniqid(),
                'title' => "Today's Cases",
                'districtCases' => $todaysCases['district'] ?? 0,
                'highCourtCases' => $todaysCases['high'] ?? 0,
            ],
            [
                'id' => uniqid(),
                'title' => "Tomorrow's Cases",
                'districtCases' => $tomorrowsCases['district'] ?? 0,
                'highCourtCases' => $tomorrowsCases['high'] ?? 0,
            ],
            [
                'id' => uniqid(),
                'title' => 'Date Awaited Cases',
                'districtCases' => $dateAwaitedCases['district'] ?? 0,
                'highCourtCases' => $dateAwaitedCases['high'] ?? 0,
            ],
            [
                'id' => uniqid(),
                'title' => 'Daily Board',
                'districtCases' => $dailyBoardCases['district'] ?? 0,
                'highCourtCases' => $dailyBoardCases['high'] ?? 0,
            ],
            [
                'id' => uniqid(),
                'title' => 'Archives',
                'districtCases' => $archivedCases['district'] ?? 0,
                'highCourtCases' => $archivedCases['high'] ?? 0,
            ]
        ];

        return response()->json($dashboardData);
    }

    /**
     * Get case counts based on case status and additional conditions.
     */
    protected function getCaseCount($status, $conditions = [])
    {
        // Create a query to count cases based on court type
        $query = CaseModel::select(
            DB::raw("COUNT(CASE WHEN tabs = 'district' THEN 1 END) as district"),
            DB::raw("COUNT(CASE WHEN tabs = 'high' THEN 1 END) as high")
        )
            ->where('status', $status)
            ->whereNull('decided_toggle')
            ->whereNull('abbondend_toggle');

        // Apply additional conditions if provided
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                // For complex conditions like range comparisons (>=, <=)
                $query->where($value[0], $value[1], $value[2]);
            } else {
                // For simple conditions like date or specific values
                $query->where($key, $value);
            }
        }

        // Return the result as an associative array
        return $query->first()->toArray();
    }

    public function getCourts(Request $request)
    {
        $perPage = $request->get('per_page');

        if ($perPage) {
            // Pagination enabled, paginate by 'per_page' value
            $courts = ApiCourt::paginate($perPage);
        } else {
            // No pagination, fetch all records
            $courts = ApiCourt::all();
        }

        return response()->json($courts);
    }

    public function getStates(Request $request)
    {
        $perPage = $request->get('per_page');

        if ($perPage) {
            // Pagination enabled, paginate by 'per_page' value
            $states = ApiState::paginate($perPage);
        } else {
            // No pagination, fetch all records
            $states = ApiState::all();
        }

        return response()->json($states);
    }

    public function getDistrictsByState(Request $request, $stateValue)
    {
        $perPage = $request->get('per_page');

        if ($perPage) {
            // Pagination enabled, paginate by 'per_page' value
            $districts = ApiDistrict::where('state_value', $stateValue)->paginate($perPage);
        } else {
            // No pagination, fetch all records
            $districts = ApiDistrict::where('state_value', $stateValue)->get();
        }

        if ($districts->isNotEmpty()) {
            return response()->json($districts);
        }
        return response()->json(['message' => 'No districts found for the specified state'], 404);
    }

    public function getBenchesByDistrict(Request $request, $districtValue)
    {
        $perPage = $request->get('per_page');

        if ($perPage) {
            // Pagination enabled, paginate by 'per_page' value
            $benches = ApiBench::where('district_val', $districtValue)->paginate($perPage);
        } else {
            // No pagination, fetch all records
            $benches = ApiBench::where('district_val', $districtValue)->get();
        }

        if ($benches->isNotEmpty()) {
            return response()->json($benches);
        }
        return response()->json(['message' => 'No benches found for the specified district'], 404);
    }

    public function getTypes(Request $request, $benchValue)
    {
        $perPage = $request->get('per_page');

        if ($perPage) {
            // Pagination enabled, paginate by 'per_page' value
            $types = ApiCaseType::where('bench_val', $benchValue)->paginate($perPage);
        } else {
            // No pagination, fetch all records
            $types = ApiCaseType::where('bench_val', $benchValue)->get();
        }

        if ($types->isNotEmpty()) {
            return response()->json($types);
        }
        return response()->json(['message' => 'No types found for the specified benches'], 404);
    }


    public function getHearingCourts()
    {
        $hearingCourts = DB::table('hearing_courts')
            ->where('is_active', true)
            ->get();

        return response()->json($hearingCourts);
    }



}
