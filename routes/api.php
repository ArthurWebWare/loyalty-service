<?php

use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\LoyaltyPointsController;
use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->get('auth/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('user',function (Request $request){ return $request->user(); });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        // account management
        Route::group(['prefix' => 'accounts'], function (){
            Route::get('/', [AccountController::class, 'index']);
            Route::post('create', [AccountController::class, 'create']);
            Route::get('view/{type}/{id}', [AccountController::class, 'view']);
            Route::patch('activate/{type}/{id}', [AccountController::class, 'activate']);
            Route::patch('deactivate/{type}/{id}', [AccountController::class, 'deactivate']);
            Route::get('balance/{type}/{id}', [AccountController::class, 'balance']);
        });

        // loyalty points management
        Route::group(['preifx' => 'loyaltyPoints'], function (){

            Route::post('deposit', [LoyaltyPointsController::class, 'deposit']);
            Route::post('withdraw', [LoyaltyPointsController::class, 'withdraw']);
            Route::post('cancel', [LoyaltyPointsController::class, 'cancel']);
            
        });
    });
});