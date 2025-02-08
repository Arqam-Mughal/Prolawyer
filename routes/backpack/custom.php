<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('case-model-active', 'CaseModelActiveCrudController');
    Route::crud('case-model-today', 'CaseModelTodayCrudController');
    Route::crud('case-model-tomorrow', 'CaseModelTomorrowCrudController');
    Route::crud('case-model-dailyboard', 'CaseModelDailyBoardCrudController');
    Route::crud('case-model-date-awaited', 'CaseModelDateAwaitedCrudController');
    Route::crud('case-model-all-cases', 'CaseModelAllCasesCrudController');
    Route::crud('case-model-running-cases', 'CaseModelRunningCrudController');
    Route::crud('case-model-case-category', 'CaseModelCaseCategoryCrudController');
    Route::crud('case-model-judgement-case', 'CaseModelJudgementCrudController');
    Route::crud('case-model-closed', 'CaseModelClosedCrudController');
    Route::crud('case-model-filtered', 'CaseModelFilterCrudController');
    Route::crud('case-model-cause-list', 'CaseModelCauseCrudController');

    Route::crud('case-advance-search', 'CaseModelAdvanceSearchCrudController');
    Route::crud('worklist', 'WorklistCrudController');
    Route::get('worklist/{worklist}/mark-as-completed', 'WorklistCrudController@markAsCompleted')->name('worklist.mark_as_completed');
    Route::get('worklist/{worklist}/mark-as-incomplete', 'WorklistCrudController@markAsIncomplete')->name('worklist.mark_as_incomplete');
    Route::crud('worklist-category', 'WorklistCategoryCrudController');
    Route::crud('transaction', 'TransactionCrudController');
    Route::crud('organization', 'OrganizationCrudController');
    Route::crud('client', 'ClientCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('case-label', 'CaseLabelCrudController');

    Route::get('advance-search-petitioner', 'AdvanceSearchController@advanceSearchPetitioner')->name('advance-search-petitioner');
    Route::get('advance-search-respondent', 'AdvanceSearchController@advanceSearchRespondent')->name('advance-search-respondent');
    Route::get('advance-search-petitioner-advocate', 'AdvanceSearchController@advanceSearchPetitionerAdvocate')->name('advance-search-petitioner-advocate');
    Route::get('advance-search-respondent-advocate', 'AdvanceSearchController@advanceSearchRespondentAdvocate')->name('advance-search-respondent-advocate');
    Route::get('advance-search-cnr-no', 'AdvanceSearchController@advanceSearchCnrNo')->name('advance-search-cnr-no');
    Route::get('advance-search-case-type', 'AdvanceSearchController@advanceSearchCaseType')->name('advance-search-case-type');
    Route::get('advance-search-case-no', 'AdvanceSearchController@advanceSearchCaseNo')->name('advance-search-case-no');
    Route::get('advance-search-brief-no', 'AdvanceSearchController@advanceSearchBriefNo')->name('advance-search-brief-no');
    Route::get('advance-search-court-bench', 'AdvanceSearchController@advanceSearchCourtBench')->name('advance-search-court-bench');
    Route::get('advance-search-judge', 'AdvanceSearchController@advanceSearchJudge')->name('advance-search-judge');
    Route::get('advance-search-case-stage', 'AdvanceSearchController@advanceSearchCaseStage')->name('advance-search-case-stage');
    Route::get('advance-search-organization', 'AdvanceSearchController@advanceSearchOrganization')->name('advance-search-organization');
    Route::get('advance-search-reference', 'AdvanceSearchController@advanceSearchReference')->name('advance-search-reference');
    Route::get('advance-search-client', 'AdvanceSearchController@advanceSearchClient')->name('advance-search-client');
    Route::get('advance-search-caselabel', 'AdvanceSearchController@advanceSearchCaseLabel')->name('advance-search-case-label');
    Route::get('advance-search-state', 'AdvanceSearchController@advanceSearchState')->name('advance-search-state');
    Route::get('advance-search-district', 'AdvanceSearchController@advanceSearchDistrict')->name('advance-search-district');


    Route::crud('archive', 'ArchieveCrudController');
    Route::crud('bare-acts', 'BareActsCrudController');
    Route::get('daily-board', 'DailyBoardController@index');
    Route::post('daily-board', 'DailyBoardController@fetch')->name('dailyboard.fetch');
    Route::get('calculator-court-fee', 'CalculatorController@court_fee_calculator')->name('calculator.court_fee');
    Route::get('calculator-interest', 'CalculatorController@interest_calculator')->name('calculator.interest');
    Route::get('calculator-limitation', 'CalculatorController@limitation_calculator')->name('calculator.limitation');
    Route::crud('connected-matters', 'ConnectedMattersCrudController');
    Route::crud('ref-tag', 'RefTagCrudController');
}); // this should be the absolute last line of this file

Route::get('/get-senior-lawyer-roles/{id}', function ($id) {
    $seniorLawyer = \App\Models\User::findOrFail($id);

    $roles = $seniorLawyer->roles()->with('permissions')->get();

    $roleIds = $roles->pluck('id')->toArray();

    $permissionIds = $seniorLawyer->getAllPermissions()->where('name','!=','Add sub lawyer')->toArray();

    $m1 = array_filter($permissionIds, function ($permission) {
        return $permission['type'] == 1;
    });

    $m2 = array_filter($permissionIds, function ($permission) {
        return $permission['type'] == 2;
    });

    $m3 = array_filter($permissionIds, function ($permission) {
        return $permission['type'] == 3;
    });

    return response()->json([
        'permissions' => [
            'm1' => array_values($m1),
            'm2' => array_values($m2),
            'm3' => array_values($m3),
        ]
    ]);
});

