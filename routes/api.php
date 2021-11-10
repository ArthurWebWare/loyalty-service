<?php

use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\TransactionsController;
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
       
        Route::group(['prefix' => 'accounts'], function (){
             // account management
            Route::get('/', [AccountController::class, 'index']);
            Route::get('{type}/{id}', [AccountController::class, 'view']);

            Route::post('/', [AccountController::class, 'create']);
            
            Route::patch('activate/{type}/{id}', [AccountController::class, 'activate']);
            Route::patch('deactivate/{type}/{id}', [AccountController::class, 'deactivate']);
            Route::get('balance/{type}/{id}', [AccountController::class, 'balance']);
        });

        Route::group(['prefix' => 'transactions'], function (){
            // loyalty points management
            Route::post('{type}/{id}', [TransactionsController::class, 'deposit']);
            Route::post('{type}/{id}/withdraw', [TransactionsController::class, 'withdraw']);
            Route::post('cancel', [TransactionsController::class, 'cancel']);
            
        });
    });
});