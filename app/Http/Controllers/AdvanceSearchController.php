<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;

class AdvanceSearchController extends Controller
{

    public function apiAdvanceSearch(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        \Log::info(auth()->user());

        $filters      = $request->except(['_token', '_method']);
        \Log::info($filters);

        $active_cases = CaseModel::select('cases.*', 'court_lists.short_form', 'petitioners.id as petitioners_id',
            'petitioner_advocates.id as petitioner_advocates_id', 'respondents.id as respondents_id',
            'respondent_advocates.id as respondent_advocates_id')
            ->leftJoin('court_lists', 'cases.court_bench', '=', 'court_lists.long_form')
            ->leftJoin('petitioners', 'cases.id', '=', 'petitioners.case_id')
            ->leftJoin('petitioner_advocates', 'cases.id', '=', 'petitioner_advocates.case_id')
            ->leftJoin('respondents', 'cases.id', '=', 'respondents.case_id')
            ->leftJoin('respondent_advocates', 'cases.id', '=', 'respondent_advocates.case_id')
            ->where('cases.tabs', 'District Courts and Tribunals');

        if (auth()->user()->role->type !== 'system_user') {
            $active_cases = $active_cases->where('cases.lawyer_id', auth()->id());
        }

        if (isset($filters['brief_no']) && $filters['brief_no'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.brief_no', 'like', '%'.$filters['brief_no'].'%');
        }

        if (isset($filters['case_no']) && $filters['case_no'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.case_no', 'like', '%'.$filters['case_no'].'%');
        }

        if (isset($filters['case_category']) && $filters['case_category'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.case_category', $filters['case_category']);
        }

        if (isset($filters['case_year']) && $filters['case_year'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.case_year', '=', $filters['case_year']);
        }

        if (isset($filters['case_stages']) && $filters['case_stages'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.case_stage', 'like', '%'.$filters['case_stages'].'%');
        }

        if (isset($filters['judge_names']) && $filters['judge_names'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.judge_name', 'like', '%'.$filters['judge_names'].'%');
        }

        if (isset($filters['court']) && $filters['court'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.court_bench', $filters['court']);
        }

        if (isset($filters['brief_for']) && $filters['brief_for'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.brief_for', $filters['brief_for']);
        }

        if (isset($filters['state']) && $filters['state'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.state_id', $filters['state']);
        }

        if (isset($filters['district']) && $filters['district'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.district_id', $filters['district']);
        }

        if (isset($filters['police_station']) && $filters['police_station'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.police_station', $filters['police_station']);
        }

        if (isset($filters['tag']) && $filters['tag'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.tags', $filters['tag']);
        }

        if (isset($filters['orgs']) && $filters['orgs'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('cases.organization_id', $filters['orgs']);
        }

        if (isset($filters['from_prev_date']) && isset($filters['to_prev_date']) && $filters['to_prev_date'] != '' && $filters['from_prev_date'] != '') {
            $from_date    = date('Y-m-d', strtotime($filters['from_prev_date']));
            $to_date      = date('Y-m-d', strtotime($filters['to_prev_date'].' + 1 day'));
            $active_cases = $active_cases->whereBetween('cases.previous_date', [$from_date, $to_date]);
        } else {
            if (isset($filters['from_prev_date']) && $filters['from_prev_date'] != '') {
                $from_date    = date('Y-m-d', strtotime($filters['from_prev_date']));
                $active_cases = $active_cases->whereDate('cases.previous_date', '>=', $from_date);
            } else {
                if (isset($filters['to_prev_date']) && $filters['to_prev_date'] != '') {
                    $to_date      = date('Y-m-d', strtotime($filters['to_prev_date'].' + 1 day'));
                    $active_cases = $active_cases->whereDate('cases.previous_date', '<=', $to_date);
                }
            }
        }

        if (isset($filters['from_next_date']) && isset($filters['to_next_date']) && $filters['to_next_date'] != '' && $filters['from_next_date'] != '') {
            $from_date    = date('Y-m-d', strtotime($filters['from_next_date']));
            $to_date      = date('Y-m-d', strtotime($filters['to_next_date'].' + 1 day'));
            $active_cases = $active_cases->whereBetween('cases.next_date', [$from_date, $to_date]);
        } else {
            if (isset($filters['from_next_date']) && $filters['from_next_date'] != '') {
                $from_date    = date('Y-m-d', strtotime($filters['from_next_date']));
                $active_cases = $active_cases->whereDate('cases.next_date', '>=', $from_date);
            } else {
                if (isset($filters['to_next_date']) && $filters['to_next_date'] != '') {
                    $to_date      = date('Y-m-d', strtotime($filters['to_next_date'].' + 1 day'));
                    $active_cases = $active_cases->whereDate('cases.next_date', '<=', $to_date);
                }
            }
        }

        if (isset($filters['petitioner']) && $filters['petitioner'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('petitioners.id', $filters['petitioner']);
        }

        if (isset($filters['respondent']) && $filters['respondent'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
            $active_cases = $active_cases->where('respondents.id', $filters['respondent']);

            if (isset($filters['petitioner_advocate']) && $filters['petitioner_advocate'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
                $active_cases = $active_cases->where('petitioner_advocates.id', $filters['petitioner_advocate']);
            }

            if (isset($filters['respondent_advocate']) && $filters['respondent_advocate'] != '' && isset($filters['tab']) && $filters['tab'] == 'District Courts and Tribunals') {
                $active_cases = $active_cases->where('respondent_advocates.id', $filters['respondent_advocate']);
            }

            $active_cases = $active_cases->orderBy('cases.created_at', 'desc')->get();
            if ($active_cases->isEmpty()) {
                return response()->json(['message' => 'No cases found'], 200);
            }
            return response()->json($active_cases);
        }
    }
}


