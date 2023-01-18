<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CustomerController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

 Route::group(['prefix' => '/customer'], function () {
		Route::get('/cityList', [CustomerController::class, 'getCityList']);
		Route::post('/sendRegisterOTP', [CustomerController::class, 'sendRegisterOTP']);
	    Route::post('/verifyOTP', [CustomerController::class, 'verifyRegisterOTP']);
		Route::get('/registration', [CustomerController::class, 'registration']);
	});
