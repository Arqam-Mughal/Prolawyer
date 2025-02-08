<?php

use App\Models\Setting;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Payment\PayuPaymentController;
use App\Http\Controllers\Payment\CashfreePaymentController;
use App\Http\Controllers\Admin\CaseModelAllCasesCrudController;

Route::get('/', [WelcomeController::class,'index']);

Route::post('cases/fetch-by-cnr', [CaseController::class, 'fetchbycnr'])->name('cases.fetch_by_cnr');
Route::post('cases/fetch-by-case-no', [CaseModelAllCasesCrudController::class, 'fetch_case_by_case_no'])->name('cases.fetch_by_case_no');
Route::post('cases/get-districts', [CaseModelAllCasesCrudController::class, 'getDistricts'])->name('cases.getDistricts');
Route::post('cases/change-favourite', [CaseModelAllCasesCrudController::class, 'changeFavourite'])->name('cases.changeFavourite');
Route::post('cases/get-case-modal', [CaseModelAllCasesCrudController::class, 'getCaseForModal'])->name('cases.getCaseForModal');
Route::post('cases/change-date-stage', [CaseModelAllCasesCrudController::class, 'changeDateAndStage'])->name('cases.changeDateAndStage');


// welcome page routes

Route::any('check-email', [WelcomeController::class,'checkEmail'])->name('check-email');


Route::post('/generate-payu-order', [PayuPaymentController::class, 'store'])->name('fetch-payu');
Route::any('/payment/payu/success', [PayuPaymentController::class, 'successs'])->name('payu.pay.success');
Route::any('/payment/payu/failure', [PayuPaymentController::class, 'failure'])->name('payu.pay.falure');

// CashfreePayment Gateway
// Route::get('cashfree/payments/create', [CashfreePaymentController::class, 'create'])->name('callback');
Route::post('cashfree/payments/store', [CashfreePaymentController::class, 'store'])->name('cashfree.pay.store');
Route::any('cashfree/payments/success', [CashfreePaymentController::class, 'successs'])->name('success');

Route::post('version-change',function(){
    $setting_exist = Setting::where('name','version-change')->first();
    if($setting_exist){
        $setting_exist->value = $setting_exist->value == 0 ? 1 : 0;
        $setting_exist->save();

        if($setting_exist->value == 0){
        $msg = 'The Old Website Activated Now!';
        $type = 'success';
        }else{
        $msg = 'The New Website Activated Now!';
        $type = 'primary';
        }

    }else{
    $setting = new Setting();
    $setting->name = 'version-change';
    $setting->value = 1;
    $setting->save();

    $msg = 'The New Website Activated Now!';
    $type = 'primary';
    }

    return response()->json([
        'status' => 'success',
        'message' => $msg,
        'type' => $type
    ]);
})->name('version-change');


