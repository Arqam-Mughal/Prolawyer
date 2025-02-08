<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace'  => 'App\Http\Controllers\Admin', // the new namespace
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware()],     // 2 middleware web, admin
], function () {
    // the adapted controllers
    Route::crud('user', 'UserCrudController');
    // Route::crud('permission', 'PermissionCrudController');
    Route::crud('role', 'RoleCrudController');
});


Route::group([
    'namespace'  => '\Backpack\PermissionManager\app\Http\Controllers', // the original namespace
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware()],
], function () {
    // to original controllers
    // Route::crud('user', 'UserCrudController');
    // Route::crud('permission', 'PermissionCrudController');
    // Route::crud('role', 'RoleCrudController');
});
