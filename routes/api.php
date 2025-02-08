<?php

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\PayUController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\CaseDetailsController;
use App\Http\Controllers\API\WorklistController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdvanceSearchController;
use App\Http\Controllers\AccountProfileController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WorklistCategoryController;

// Public Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget-password', [ForgetPasswordController::class, 'forget_password']);
Route::post('reset-password', [ForgetPasswordController::class, 'reset_password']);
Route::post('change-password', [ForgetPasswordController::class, 'change_password']);
Route::post('request-otp', [AuthController::class, 'requestOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);

Route::post('/payu/callback', [PayUController::class, 'handleCallback']);

Route::get('roles/with-permissions', [RoleController::class, 'rolesWithPermissions']);
Route::get('/get/payment-gateway', [PaymentGatewayController::class, 'getActiveGateway']);

Route::post('/notifications', [NotificationController::class, 'createNotification']);
Route::get('/notifications', [NotificationController::class, 'fetchNotifications']);
Route::post('/notifications/{id}/dismiss', [NotificationController::class, 'dismissNotification']);

Route::post('/validate-token', [AuthController::class, 'IsvalidateToken']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/advance-search', [AdvanceSearchController::class, 'apiAdvanceSearch']);


    Route::apiResource('users', UserController::class)->missing(function () {
        return response()->json([
            'success' => false,
            'message' => 'User not found.',
        ], 200);
    });
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

    Route::get('/dashboard', [CaseController::class, 'getDashboardData']);

    Route::post('/set/payment-gateway', [PaymentGatewayController::class, 'setPaymentGateway']);
    Route::post('/process-payment', [PaymentGatewayController::class, 'processPayment']);

    //worklist
    Route::get('/worklist', [WorklistCategoryController::class, 'index']);
    Route::get('/worklist/{id}', [WorklistCategoryController::class, 'show']);
    Route::post('/worklist', [WorklistCategoryController::class, 'store']);
    Route::put('/worklist/{id}', [WorklistCategoryController::class, 'update']);
    Route::delete('/worklist/{id}', [WorklistCategoryController::class, 'destroy']);
    Route::post('worklists/{id}/mark-completed',
        [WorklistCategoryController::class, 'markAsWorkCompleted'])->name('worklists.mark-completed');
    Route::post('worklists/{id}/mark-inprogress',
        [WorklistCategoryController::class, 'markAsWorkInprogress'])->name('worklists.mark-incomplete');


    //deleteaccount , delete user , update profile
    Route::delete('/delete-account', [AccountProfileController::class, 'deleteAccount']);
    Route::delete('/delete-user/{id}', [AccountProfileController::class, 'deleteUser']);
    Route::post('/update-account', [AuthController::class, 'updateProfile']);

    //my account
    Route::get('/my-account', [AccountProfileController::class, 'myAccount']);

    // Case Routes
    Route::get('/courts', [DashboardController::class, 'getCourts']);
    //get haring courts
    Route::get('/hearing-courts', [DashboardController::class, 'getHearingCourts']);
    Route::get('/states', [DashboardController::class, 'getStates']);
    Route::get('/districts/state/{stateValue}', [DashboardController::class, 'getDistrictsByState']);

    Route::get('/benches/district/{districtValue}', [DashboardController::class, 'getBenchesByDistrict']);
    Route::get('/types/bench/{benchValue}', [DashboardController::class, 'getTypes']);


    Route::prefix('cases')->group(function () {
        Route::get('/active-cases', [CaseController::class, 'getActiveCases']);
        Route::get('/today-cases', [CaseController::class, 'getTodayCases']);
        Route::get('/tomorrow-cases', [CaseController::class, 'getTomorrowCases']);
        Route::get('/daily-board-cases/{date}/{tab}', [CaseController::class, 'getDailyBoardCases']);
        Route::get('/date-awaited-cases', [CaseController::class, 'getDateAwaitedCases']);
        Route::get('/archive-cases', [CaseController::class, 'getArchievedCases']);

        Route::get('/{caseId}/details', [CaseDetailsController::class, 'getCaseDetails']);

        Route::post('/organization', [CaseDetailsController::class, 'addOrganization']);
        Route::get('/organizations', [CaseDetailsController::class, 'getAllOrganizations']);
        Route::get('/organization/{organizationId}', [CaseDetailsController::class, 'getOrganization']);
        Route::put('/organization/{organizationId}', [CaseDetailsController::class, 'updateOrganization']);
        Route::delete('/organization/{organizationId}', [CaseDetailsController::class, 'deleteOrganization']);

        Route::get('/clients/{clientId}', [CaseDetailsController::class, 'getClient']);
        Route::get('/clients', [CaseDetailsController::class, 'getAllClients']);
        Route::post('/clients', [CaseDetailsController::class, 'addClient']);
        Route::put('/clients/{clientId}', [CaseDetailsController::class, 'updateClient']);
        Route::delete('/clients/{clientId}', [CaseDetailsController::class, 'deleteClient']);

        Route::get('/label/{label_id}', [CaseController::class, 'getCasesByLabel']);
        Route::post('/{case_id}/label', [CaseController::class, 'addLabelToCase']);
        Route::post('/labels', [CaseController::class, 'storeLabel']);
        Route::get('/labels', [CaseController::class, 'getAllLabels']);
        Route::get('/labels/{label_id}', [CaseController::class, 'getLabel']);
        Route::delete('/labels/{label_id}', [CaseController::class, 'deleteLabel']);

        Route::get('/users/{userId}/assigned-user', [CaseController::class, 'getAssignedLawyers']);
        Route::post('/{caseId}/assigned-to', [CaseController::class, 'assignUserToCase']);
        Route::put('/{caseId}/assigned-to', [CaseController::class, 'updateAssignedTo']);

        Route::get('/{case_id}/notes', [CaseController::class, 'getNotesDescription']);
        Route::post('/{case_id}/notes', [CaseController::class, 'storeNotesDescription']);
        Route::post('/{case_id}/notes', [CaseController::class, 'storeNotesDesccalription']);

        Route::get('/tabs', [CaseController::class, 'getCaseTabs']);

        Route::get('/tag/{tagId}', [CaseDetailsController::class, 'getTag']);
        Route::get('/tags', [CaseDetailsController::class, 'getAllTags']);
        Route::post('/tags', [CaseDetailsController::class, 'addTag']);
        Route::put('/tags/{tagId}', [CaseDetailsController::class, 'updateTag']);
        Route::delete('/tags/{tagId}', [CaseDetailsController::class, 'deleteTag']);

        // add document in case
        Route::post('/{caseId}/document', [CaseDetailsController::class, 'addDocument']);
        //delete document
        Route::delete('/document/{documentId}', [CaseDetailsController::class, 'deleteDocument']);


        Route::get('/{caseId}/connected-matters', [CaseDetailsController::class, 'getConnectedMatters']);
        //get all connected matters
        Route::get('/connected-matters', [CaseDetailsController::class, 'getAllConnectedMatters']);
        Route::post('/connected-matter', [CaseDetailsController::class, 'addConnectedMatter']);
        Route::delete('/connected-matters/{connectedMatterId}',
            [CaseDetailsController::class, 'deleteConnectedMatter']);
        Route::post('/connected-matter/{id}/update', [CaseDetailsController::class, 'updateConnectedMatter']);
        Route::get('/bare-act/{id}', [CaseDetailsController::class, 'getBareAct']);
        Route::get('/bare-acts', [CaseDetailsController::class, 'getBareActsDetails']);

        Route::post('/fetch-by-cnr', [CaseController::class, 'fetchbycnr']);
        Route::post('/update-by-cnr', [CaseController::class, 'updatebycnr']);
        //fetchbycase
        Route::post('/fetch-by-case', [CaseController::class, 'fetchByCase']);
        Route::post('/update-by-case', [CaseController::class, 'updateByCase']);
        Route::post('/store', [CaseController::class, 'store']);
        Route::post('/storeCase', [CaseController::class, 'storeCase']);
        Route::post('/updateCase/{id}', [CaseController::class, 'updateCase']);
        Route::post('/deleteCase/{id}', [CaseController::class, 'deleteCase']);
        Route::post('/bulkUpload', [CaseController::class, 'bulkUploadCases']);
        Route::get('/all-cases', [CaseController::class, 'allCases']);

        Route::post('/{caseId}/mark-favorite', [CaseDetailsController::class, 'markAsFavorite']);
        Route::post('/{caseId}/mark-completed', [CaseDetailsController::class, 'markAsCompleted']);
        Route::post('/{caseId}/mark-in-progress', [CaseDetailsController::class, 'markAsInProgress']);
        Route::post('/{caseId}/unmark-favorite', [CaseDetailsController::class, 'unmarkAsFavorite']);

        Route::get('/all', [CaseController::class, 'allCasesCalender']);
        Route::get('/count', [CaseController::class, 'countCases']);


    });

    //upgrade or downgrade
    Route::post('/change-plan', [RoleController::class, 'upgradeOrDowngradePlan']);

    //choose plan
    Route::post('/choose-plan', [RoleController::class, 'selectPlan']);


    Route::get('/plans', [RoleController::class, 'getPlans']);

    // Role Routes
    Route::prefix('roles')->group(function () {
        Route::apiResource('', RoleController::class)->missing(function () {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 200);
        });
        Route::get('{role}/permissions', [RoleController::class, 'permissionsIndex'])->name('roles.permissions.index');
        Route::post('{role}/permissions',
            [RoleController::class, 'permissionsUpdate'])->name('roles.permissions.update');
    });

    // Permission Routes
    Route::apiResource('permissions', PermissionController::class)->missing(function () {
        return response()->json([
            'success' => false,
            'message' => 'Permission not found.',
        ], 200);
    });

    // Worklist Routes
    Route::apiResource('worklists', WorklistController::class)->missing(function () {
        return response()->json([
            'success' => false,
            'message' => 'Worklist not found.',
        ], 200);
    });


    Route::post('/create-order', [PaymentController::class, 'createOrder']);
    Route::get('/order/success', [PaymentController::class, 'handleSuccess']);
    Route::post('/order/notify', [PaymentController::class, 'handleNotify']);

    Route::post('/payu/payment-link', [PayUController::class, 'createPaymentLink']);


    Route::get('ocr', [OcrController::class, 'indexOcr']);
    Route::post('ocr', [OcrController::class, 'storeOcr']);

    Route::get('events', [EventsController::class, 'index']);
    Route::apiResource('transactions', TransactionController::class)->missing(function () {
        return response()->json([
            'success' => false,
            'message' => 'Transaction not found.',
        ], 200);
    });
});

Route::get('version-switch',function(){
    $setting_exist = Setting::where('name','version-change')->first();
    if($setting_exist){
        return response()->json([
            'data' => $setting_exist->value == 1 ? 'new' : 'old',
            'code' => $setting_exist->value == 1 ? 1 : 0
        ]);
    }
});

