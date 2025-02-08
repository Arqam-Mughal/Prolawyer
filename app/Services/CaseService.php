<?php

namespace App\Services;

use App\Models\CaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CaseService
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function getCases($courtType, $search = '', $dateCondition = null, $pageSize = 10)
    {
        $cacheKey = 'cases_' . auth()->id() . '_' . $courtType . '_' . md5(json_encode($dateCondition));

        $cases = Cache::remember($cacheKey, 600, function () use ($courtType, $dateCondition, $pageSize) {
            $casesQuery = CaseModel::select('cases.*')
                ->with(['petitioners', 'respondents'])
                ->where('cases.status', 'Open')
                ->whereNull('decided_toggle')
                ->whereNull('abbondend_toggle')
                ->where('cases.tabs', $courtType)
                ->groupBy('cases.id')
                ->orderBy('cases.created_at', 'desc');

            if ($dateCondition) {
                $casesQuery->where($dateCondition);
            }

            if (auth()->check() && auth()->user()->role->type != 'system_user') {
                $casesQuery->where('cases.lawyer_id', auth()->id());
            }

            return $casesQuery->paginate($pageSize);
        });

        if (!empty($search)) {
            $filteredCollection = $cases->getCollection()->filter(function ($case) use ($search) {
                $searchableTitle = $case->title;
                $matchesCaseNo = strpos($case->case_no, $search) !== false;
                $matchesCaseId = strpos($case->id, $search) !== false;
                $matchesTitle = str_contains($searchableTitle, $search);

                return $matchesCaseNo || $matchesCaseId || $matchesTitle;
            });

            $cases->setCollection($filteredCollection);
        }

        $cases->getCollection()->transform(function ($case) {
            $case->connected_matters = $this->getConnectedMatters($case->case_no);
            $case->short_form = $this->getShortForm($case->court_bench);
            unset($case->history, $case->orders);
            return $case;
        });

        return $cases;
    }


    public function caseQuery($courtType)
    {
        return CaseModel::where('tabs', $courtType); // Reference the new model name
    }

    protected function getConnectedMatters($caseNos)
    {
        return CaseModel::select([
            'cases.id',
            'cases.case_no',
            'cases.case_category',
            'cases.case_year',
            DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title"),
            'respondents.respondent',
            'petitioners.petitioner'
        ])
            ->join('petitioners', 'cases.id', '=', 'petitioners.case_id')
            ->join('respondents', 'cases.id', '=', 'respondents.case_id')
            ->where('cases.case_no', $caseNos)  // Fetch in bulk
            ->get();
    }


    protected function getPetitioners($caseId)
    {
        // Fetch petitioners from the database
        return DB::table('petitioners')
            ->where('case_id', $caseId)
            ->first()
            ->petitioner;
    }

    protected function getRespondents($caseId)
    {
        // Fetch respondents from the database
        return DB::table('respondents')
            ->where('case_id', $caseId)
            ->first()
            ->respondent;
    }




    protected function getShortForm($court_bench)
    {
        // Fetch shortform from the database
        return DB::table('court_lists')
            ->where('long_form', $court_bench)
            ->first()
            ->short_form ?? '---';
    }

    public function encryptCases($cases, $key)
    {
        $encryptedCases = $cases->getCollection()->transform(function ($case) use ($key) {
            return $this->encryptionService->encryptDataToString(json_encode($case), $key);
        });

        $cases->setCollection($encryptedCases);

        return $cases;
    }



//    public function allCases($pageSize = 10)
//    {
//        $cacheKey = 'all_cases_'.auth()->id(); // Unique key based on user ID
//
//        // Attempt to retrieve cached data
//        $cases = Cache::remember($cacheKey, 300, function () use ($pageSize) {
//            // Create a base query for fetching all case data
//            $casesQuery = CaseModel::select(
//                'cases.*',
//                'court_lists.short_form',
//                DB::raw("CONCAT(petitioners.petitioner, ' vs ', respondents.respondent) AS case_title")
//            )
//                ->leftJoin('court_lists', 'cases.court_bench', '=', 'court_lists.long_form')
//                ->leftJoin('petitioners', 'cases.id', '=', 'petitioners.case_id')
//                ->leftJoin('respondents', 'cases.id', '=', 'respondents.case_id')
//                ->groupBy('cases.id')
//                ->orderBy('cases.created_at', 'desc');
//
//            // Restrict to logged-in user's cases if not a system user
//            if (auth()->check() && auth()->user()->role->type != 'system_user') {
//                $casesQuery->where('cases.lawyer_id', auth()->id());
//            }
//
//            // Fetch cases directly from the database
//            $cases = $casesQuery->paginate($pageSize);
//
//            // Transform the cases to include additional data
//            $cases->getCollection()->transform(function ($case) {
//                $case->connected_matters = $this->getConnectedMatters($case->case_no);
//                $case->petitioners       = $this->getPetitioners($case->id);
//                $case->respondents       = $this->getRespondents($case->id);
//
//                // Remove history and orders to optimize performance
//                unset($case->history, $case->orders, $case->title);
//
//                return $case;
//            });
//
//            return $cases;
//        });
//
//        return $cases;
//    }





    public function countCases($courtType, $dateCondition = null)
    {
        $cacheKey = 'count_cases_' . auth()->id() . '_' . $courtType . '_' . md5(json_encode($dateCondition,
                JSON_THROW_ON_ERROR)); // Unique key

        // Attempt to retrieve cached data
        $count = Cache::remember($cacheKey, 300, function () use ($courtType, $dateCondition) {
            // Create a base query for counting case data
            $casesQuery = CaseModel::where('cases.status', 'Open')
                ->whereNull('decided_toggle')
                ->whereNull('abbondend_toggle')
                ->where('cases.tabs', $courtType);

            // Apply date condition if provided
            if ($dateCondition) {
                $casesQuery->where($dateCondition);
            }

            // Restrict to logged-in user's cases if not a system user
            if (auth()->check()) {
                $user = auth()->user()->load('role'); // Ensure the role is loaded
                if ($user->role && $user->role->type !== 'system_user') {
                    $casesQuery->where('cases.lawyer_id', $user->id);
                }
            }

            // Return the count of cases
            return $casesQuery->count();
        });

        return $count;
    }


    public function getallCases($courtType, $dateCondition = null, $pageSize = 10, $searchQuery = null)
    {
        $cacheKey = 'cases_'.auth()->id().'_'.$courtType.'_'.md5(json_encode([$dateCondition, $searchQuery]));
        Cache::forget($cacheKey); // Clear the cache for debugging purposes

        $cases = Cache::remember($cacheKey, 300, function () use ($courtType, $dateCondition, $pageSize, $searchQuery) {
            $casesQuery = CaseModel::select('cases.*')
                ->with(['petitioners', 'respondents'])
                ->groupBy('cases.id')
                ->orderBy('cases.created_at', 'desc');

            if ($dateCondition) {
                $casesQuery->where($dateCondition);
            }

            if (auth()->check() && auth()->user()->role->type != 'system_user') {
                $casesQuery->where('cases.lawyer_id', auth()->id());
            }

            if ($searchQuery) {
                $casesQuery->where(function ($query) use ($searchQuery) {
                    $query->where('cases.case_no', 'like', "%{$searchQuery}%")
                        ->orWhere('cases.title', 'like', "%{$searchQuery}%")
                        ->orWhereHas('petitioners', function ($q) use ($searchQuery) {
                            $q->where('petitioner', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('respondents', function ($q) use ($searchQuery) {
                            $q->where('respondent', 'like', "%{$searchQuery}%");
                        });
                });
            }

            return $casesQuery->paginate($pageSize);
        });

        $cases->getCollection()->transform(function ($case) {
            $case->connected_matters = $this->getConnectedMatters($case->case_no);
            $case->title             = $case->petitioners->implode('petitioner',
                    ', ').' VS '.$case->respondents->implode('respondent', ', ');
            $case->short_form        = $this->getShortForm($case->court_bench);
            unset($case->history, $case->orders);
            return $case;
        });

        // Replace the original collection with the filtered collection
        $cases->setCollection($filteredCollection);



        return $cases;
    }





    public function getAllCasesByDateRange($courtType, $startDate, $endDate)
    {
        return CaseModel::with(['petitioners', 'respondents'])
            ->where('tabs', $courtType)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }




}
