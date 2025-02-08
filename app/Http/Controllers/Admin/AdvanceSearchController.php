<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdvanceSearchController extends Controller
{
    public function advanceSearchCnrNo(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('cnr_no', 'LIKE', '%' . $search_term . '%')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::paginate(10);
        }

        return $results;
    }

    public function advanceSearchCaseType(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('case_category', 'LIKE', '%' . $search_term . '%')
                ->groupBy('case_category')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('case_category')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchCaseNo(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('case_no', 'LIKE', '%' . $search_term . '%')
                ->groupBy('case_no')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('case_no')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchBriefNo(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('Brief_no', 'LIKE', '%' . $search_term . '%')
                ->groupBy('Brief_no')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('Brief_no')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchCourtBench(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('court_bench', 'LIKE', '%' . $search_term . '%')
                ->groupBy('court_bench')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('court_bench')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchJudge(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('judge_name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('judge_name')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('judge_name')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchCaseStage(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseModel::where('case_stage', 'LIKE', '%' . $search_term . '%')
                ->groupBy('case_stage')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseModel::groupBy('case_stage')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchPetitioner(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\Petitioner::where('petitioner', 'LIKE', '%' . $search_term . '%')
                ->groupBy('petitioner')
                ->paginate(10);
        } else {
            $results = \App\Models\Petitioner::groupBy('petitioner')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchRespondent(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\Respondent::where('respondent', 'LIKE', '%' . $search_term . '%')
                ->groupBy('respondent')
                ->paginate(10);
        } else {
            $results = \App\Models\Respondent::groupBy('respondent')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchPetitionerAdvocate(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\PetitionerAdvocate::where('petitioner_advocate', 'LIKE', '%' . $search_term . '%')
                ->groupBy('petitioner_advocate')
                ->paginate(10);
        } else {
            $results = \App\Models\PetitionerAdvocate::groupBy('petitioner_advocate')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchRespondentAdvocate(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\RespondentAdvocate::where('respondent_advocate', 'LIKE', '%' . $search_term . '%')
                ->groupBy('respondent_advocate')
                ->paginate(10);
        } else {
            $results = \App\Models\RespondentAdvocate::groupBy('respondent_advocate')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchOrganization(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\Organization::where('organization_name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('organization_name')
                ->paginate(10);
        } else {
            $results = \App\Models\Organization::groupBy('organization_name')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchReference(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\RefTag::where('name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('name')
                ->paginate(10);
        } else {
            $results = \App\Models\RefTag::groupBy('name')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchClient(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\Client::where('name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('name')
                ->paginate(10);
        } else {
            $results = \App\Models\Client::groupBy('name')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchCaseLabel(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\CaseLabel::where('label', 'LIKE', '%' . $search_term . '%')
                ->groupBy('label')
                ->paginate(10);
        } else {
            $results = \App\Models\CaseLabel::groupBy('label')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchState(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\ApiState::where('name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('name')
                ->paginate(10);
        } else {
            $results = \App\Models\ApiState::groupBy('name')
                ->paginate(10);
        }

        return $results;
    }

    public function advanceSearchDistrict(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term) {
            $results = \App\Models\ApiDistrict::where('name', 'LIKE', '%' . $search_term . '%')
                ->groupBy('name')
                ->paginate(10);
        } else {
            $results = \App\Models\ApiDistrict::groupBy('name')
                ->paginate(10);
        }

        return $results;
    }
}
