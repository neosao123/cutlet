<?php
//Restaurant

use App\Http\Controllers\Restaurant\LoginController as RestaurantLoginController;
use App\Http\Controllers\Restaurant\HomeController as RestaurantHomeController;
use App\Http\Controllers\Restaurant\RestaurantHoursController;
use App\Http\Controllers\Restaurant\RestaurantItemsController;
use App\Http\Controllers\Restaurant\ProfileController;
use App\Http\Controllers\Restaurant\RestaurantCouponController;
use App\Http\Controllers\Restaurant\RestaurantPendingOrderController;
use App\Http\Controllers\Restaurant\RestaurantConfirmOrderController;
use App\Http\Controllers\Restaurant\RestaurantOrderReportController;
use App\Http\Controllers\Restaurant\RestaurantCommissionController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

//restaurant
Route::group(['middleware' => 'restaurant'], function () {
		Route::get('/dashboard', [RestaurantHomeController::class, 'dashboard']);
		Route::get('/recentorders', [RestaurantHomeController::class, 'getDashboardOrders']);
		Route::get('/getDashboardOrders', [RestaurantHomeController::class, 'recentOrderDashboard']);
		Route::get('/getRestaurantStatus', [RestaurantHomeController::class, 'getRestaurantStatus']);
		Route::get('/updateRestaurantStatus', [RestaurantHomeController::class, 'updateRestaurantStatus']);
		Route::get('/updateOrderStatus', [RestaurantHomeController::class, 'updateOrderStatus']);
		Route::get('/updatePreparingTime', [RestaurantHomeController::class, 'updatePreparingTime']);
		Route::get('/profile/{id}', [ProfileController::class, 'view']);
		Route::get('/profileshow/{id}', [ProfileController::class, 'viewProfile']);
		Route::post('/profile/update',[ProfileController::class, 'update']);
		Route::post('/configUpdate',[ProfileController::class, 'configUpdate']);
		Route::get('/checkDuplicatemobileOnUpdate',[ProfileController::class, 'checkDuplicatemobileOnUpdate']);
	    //restaurant hours
		Route::get('/getRestaurantHours/{code}', [RestaurantHoursController::class, 'getRestaurantHours']);
		Route::get('/updateRestaurantHour', [RestaurantHoursController::class, 'updateRestaurantHour']);
	    Route::get('/saveHours', [RestaurantHoursController::class, 'saveHours']);
		Route::get('/deleteHourLine', [RestaurantHoursController::class, 'deleteHourLine']);

		 //Restaurant Item
    Route::get('getRestaurantItemList', [RestaurantItemsController::class, 'getRestaurantItemList'])->name('admin.getRestaurantItemList');
     Route::get('getSubcategoryDetails', [RestaurantItemsController::class, 'getSubcategoryDetails'])->name('admin.getSubcategoryDetails');	  
    Route::group(['prefix' => '/restaurantItems'], function () {
		Route::get('/list', [RestaurantItemsController::class, 'index'])->name('restaurantItem');
		Route::get('/add', [RestaurantItemsController::class, 'add'])->name('restaurantItemAdd');
		Route::get('/edit/{code}', [RestaurantItemsController::class, 'edit']);
		Route::get('/delete', [RestaurantItemsController::class, 'delete']);
		Route::post('/store', [RestaurantItemsController::class, 'store']);
		Route::post('/update', [RestaurantItemsController::class, 'update']);
		Route::get('/view/{code}', [RestaurantItemsController::class, 'view']);
		Route::get('/customizeaddon/{code}', [RestaurantItemsController::class, 'customizeaddon']);
		Route::get('/deleteAddonCategory', [RestaurantItemsController::class, 'deleteAddonCategory']);
		Route::get('/deleteAddonLine', [RestaurantItemsController::class, 'deleteAddonLine']);
		Route::get('/addAddonLine', [RestaurantItemsController::class, 'addAddonLine']);
		Route::get('/getAddonCategoryData', [RestaurantItemsController::class, 'getAddonCategoryData']);
		Route::get('/addAddonCategory', [RestaurantItemsController::class, 'addAddonCategory']);
		Route::get('/updateAddonCategory', [RestaurantItemsController::class, 'updateAddonCategory']);
		
		Route::get('/generateExcelTemplate', [RestaurantItemsController::class, 'generateExcelTemplate']);
		Route::get('/uploadExcel', [RestaurantItemsController::class, 'uploadExcel']);
		Route::post('/uploadData', [RestaurantItemsController::class, 'uploadData']);
		Route::post('/validateExcelFile', [RestaurantItemsController::class, 'validateExcelFile']);
	});

     // Restaurant Offer
	 Route::get('getRestaurantCouponList', [RestaurantCouponController::class, 'getCouponList'])->name('admin.getRestaurantCouponList');
	 Route::group(['prefix' => '/restaurantoffer'], function () {
        Route::get('/list', [RestaurantCouponController::class, 'index'])->name('restaurantOfferList');
        Route::get('/add', [RestaurantCouponController::class, 'add']);
        Route::get('/edit/{code}', [RestaurantCouponController::class, 'edit']);
        Route::post('/store', [RestaurantCouponController::class, 'store']);
		Route::post('/update', [RestaurantCouponController::class, 'update']);
		Route::get('/view/{code}', [RestaurantCouponController::class, 'view']);
     });
	 
	  // Restaurant Commission 
	 Route::get('getRestaurantCommission', [RestaurantCommissionController::class, 'getCommissionList'])->name('admin.getRestaurantCommissionList');
	 Route::group(['prefix' => '/restaurantcommission'], function () {
        Route::get('/list', [RestaurantCommissionController::class, 'index'])->name('restaurantCommissionList');
     });
	 
	 //Pending Orders
	 Route::get('getRestaurantPendingOrder', [RestaurantPendingOrderController::class, 'getRestaurantPendingOrder'])->name('admin.getRestaurantPendingOrder');
	 Route::group(['prefix' => '/restaurantPendingOrder'], function () {
		  Route::get('/list', [RestaurantPendingOrderController::class, 'index'])->name('restaurantPendingOrderList');
		  Route::get('/view/{code}', [RestaurantPendingOrderController::class, 'view']);
		  Route::get('/getOrderDetails',[RestaurantPendingOrderController::class, 'getOrderDetails']);
		  Route::get('/reject',[RestaurantPendingOrderController::class, 'reject']);
		  Route::post('/confirm',[RestaurantPendingOrderController::class, 'confirm']);
	 });
	 
	 //Confirm Orders
	 Route::get('getRestaurantConfirmOrder', [RestaurantConfirmOrderController::class, 'getRestaurantConfirmOrder'])->name('admin.getRestaurantConfirmOrder');
	 Route::group(['prefix' => '/restaurantConfirmOrder'], function () {
		  Route::get('/list', [RestaurantConfirmOrderController::class, 'index'])->name('restaurantConfirmOrderList');
		  Route::get('/view/{code}', [RestaurantConfirmOrderController::class, 'view']);
		  Route::get('/invoice/{code}', [RestaurantConfirmOrderController::class, 'invoice']);
		  Route::get('/getOrderDetails',[RestaurantConfirmOrderController::class, 'getOrderDetails']);
	 });
	 
	 //Confirm Orders
	 Route::get('getRestaurantOrderReport', [RestaurantOrderReportController::class, 'getRestaurantOrderReportList'])->name('admin.getRestaurantOrderReportList');
	 Route::group(['prefix' => '/orderreport'], function () {
		  Route::get('/list', [RestaurantOrderReportController::class, 'index'])->name('restaurantOrderReportList');
		  Route::get('/view/{code}', [RestaurantOrderReportController::class, 'view']);
		  Route::get('/invoice/{code}', [RestaurantOrderReportController::class, 'invoice']);
		  Route::get('/getOrderDetails',[RestaurantOrderReportController::class, 'getOrderDetails']);
	 });
	 
	 
	 //dashboard count
	 Route::group(['prefix' => '/dashboard'], function () {
		 Route::get('/getOrderCounts', [RestaurantHomeController::class, 'getOrderCounts']);
		 Route::get('/getOrders', [RestaurantHomeController::class, 'getOrders']);
		 Route::get('/getOrdersDoughnutChartData',[RestaurantHomeController::class, 'getOrdersDoughnutChartData']);
	 });
});

//restaurant login
Route::get('/', [RestaurantLoginController::class, 'index']);
Route::post('/login-restaurant', [RestaurantLoginController::class, 'login']);  
Route::get('/logout', [RestaurantLoginController::class, 'logout']);
Route::get('/update', [RestaurantLoginController::class, 'updatePassword']);
Route::get('/reset-password', [RestaurantLoginController::class, 'reset']);
Route::post('/forgot-password', [RestaurantLoginController::class, 'resetPassword']);
Route::get('/recoverPassword/{token}', [RestaurantLoginController::class, 'verifyTokenLink']);
Route::post('/recover-password', [RestaurantLoginController::class, 'updateMemberPassword']); 
Route::get('/clear', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cleared!";
});

