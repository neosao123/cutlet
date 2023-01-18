<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeliveryBoyController;
use App\Http\Controllers\Api\RestaurantController;

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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::get('/refreshRandom', [CustomerController::class, 'restaurantRefreshCode']);
Route::get('/rating/{id}', [CustomerController::class, 'getRatings']);

Route::middleware(['auth:sanctum'])->group(function(){

    //restaurant
    Route::group(['prefix' => '/restaurant'], function () { 
		Route::post('/restPasswordUpdate', [RestaurantController::class, 'restaurantPasswordUpdate']);
		Route::post('/getprofileinfoRestaurant', [RestaurantController::class, 'getprofileinfoRestaurant']);
		Route::get('getMainMenuList',[RestaurantController::class,'getMainMenuList']);
		Route::post('getMenuItemList', [RestaurantController::class, 'getMenuItemList']);
		Route::post('/getOffersByVendor',[RestaurantController::class,'getOffersByVendor']);
		Route::post('/getOffersByOfferID',[RestaurantController::class,'getOffersByOfferID']);
		Route::post('/onlineOfflineStatusChange',[RestaurantController::class,'onlineOfflineStatusChange']);
		Route::post('/getOnlineOfflineStatus',[RestaurantController::class,'getOnlineOfflineStatus']);
		Route::post('/getVendorOfferList',[RestaurantController::class,'getVendorOfferList']);
		Route::post('/addVendorCouponOffer',[RestaurantController::class,'addVendorCouponOffer']);
		Route::post('/updateVendorCouponOffer',[RestaurantController::class,'updateVendorCouponOffer']);
		Route::post('/deleteVendorCouponOffer',[RestaurantController::class,'deleteVendorCouponOffer']);
		Route::post('/confirmOrderStatusUpdate',[RestaurantController::class,'confirmOrderStatusUpdate']);
		Route::post('/rejectOrderByVendor',[RestaurantController::class,'rejectOrderByVendor']);
		Route::post('/getAllOrderListByRestaurant',[RestaurantController::class,'getAllOrderListByRestaurant']);
		Route::post('/getOrderListRestaurantByStatus',[RestaurantController::class,'getOrderListRestaurantByStatus']);
	    Route::post('/getOrderDetails',[RestaurantController::class,'getOrderDetails']);
		Route::post('/getDashboardCounts',[RestaurantController::class,'getDashboardCounts']);
		Route::post('/updateMenuItemStatus',[RestaurantController::class,'updateMenuItemStatus']);
		Route::post('/updateFirebaseId', [RestaurantController::class, 'updateFirebaseId']);
		Route::post('/getOrderBillingList', [RestaurantController::class, 'getOrderBillingList']);
	    Route::post('/testNotification', [RestaurantController::class, 'testNotification']);
	}); 
	
	// customer api
   Route::group(['prefix' => '/customer'], function () {
		Route::post('/getCustomAddressList', [CustomerController::class, 'getCustomAddressList']);
		Route::post('/gethomeSliderImages', [CustomerController::class, 'gethomeSliderImages']);
		Route::post('/getUserProfile', [CustomerController::class, 'getUserProfile']);
		Route::post('/updateProfile', [CustomerController::class, 'updateProfile']);
		Route::post('/updateProfileAddress', [CustomerController::class, 'updateProfileAddress']);
		Route::post('/updateFirebaseId', [CustomerController::class, 'updateFirebaseId']);
		Route::get('/getMaintenanceDetails', [CustomerController::class, 'getMaintenanceDetails']);
		Route::post('/getCityByLatLong', [CustomerController::class, 'getCityByLatitudeLongitude']);
		Route::post('/addClientAddress', [CustomerController::class, 'addClientAddress']);
		Route::post('/getAddressesByclienCode', [CustomerController::class, 'getAddressesByclienCode']);
		Route::post('/setDefaultAddress', [CustomerController::class, 'setDefaultAddress']);
		Route::post('/checkUserExists', [CustomerController::class, 'checkUserExists']);
		Route::post('/deleteClientAddress', [CustomerController::class, 'deleteClientAddress']);
		Route::post('/updateClientAddress', [CustomerController::class, 'updateClientAddress']);
		Route::post('/saveDeliveryBoyRating', [CustomerController::class, 'saveDeliveryBoyRating']);
		Route::post('/saveRestaurantRating', [CustomerController::class, 'saveRestaurantRating']);
		Route::post('/favouriteRestaurantUpdate', [CustomerController::class, 'favouriteRestaurantUpdate']);
		Route::post('/getFavRestaurantList', [CustomerController::class, 'getFavRestaurantList']);
		Route::get('/getSupportContact', [CustomerController::class, 'getSupportContact']);
		Route::post('/checkWishList', [CustomerController::class, 'checkWishList']);
		Route::post('/addToWishlist', [CustomerController::class, 'addToWishlist']);
		Route::post('/getWishList', [CustomerController::class, 'getWishList']);
		Route::post('/getClientOrderList', [CustomerController::class, 'getClientOrderList']);
		Route::post('/cancelOrder', [CustomerController::class, 'cancelOrder']);
		Route::get('/cartTermsAndCondition', [CustomerController::class, 'cartTermsAndCondition']);
		Route::post('/getWalletAmount', [CustomerController::class, 'getWalletAmount']);
		Route::post('/getCutletHistoryList', [CustomerController::class, 'getCutletHistoryList']);
		Route::post('/getPendingCutletList', [CustomerController::class, 'getPendingCutletList']);
		Route::post('/trackOrderById', [CustomerController::class, 'trackOrderById']);
		Route::post('/getOrderHistoryListByTab', [CustomerController::class, 'getOrderHistoryListByTab']);
		Route::post('/getOrderDetailsByOrderCode', [CustomerController::class, 'getOrderDetailsByOrderCode']);
		Route::post('/checkServiceableAreaByLatLong', [CustomerController::class, 'checkServiceableAreaByLatitudeLongitude']);
		
		//restaurant 
		Route::get('/getEntityCategoryList', [CustomerController::class, 'getEntityCategoryList']);
		Route::get('/getFoodSliderImages', [CustomerController::class, 'getFoodSliderImages']);
		Route::get('/getMenuCategoryList', [CustomerController::class, 'getMenuCategoryList']);
		Route::get('/getMenuSubCategoryList', [CustomerController::class, 'getMenuSubCategoryList']);
		Route::post('/getCuisinesList', [CustomerController::class, 'getCuisinesList']);
		Route::post('/getRestaurantList', [CustomerController::class, 'getRestaurantList']);
		Route::post('/getMenuItemList', [CustomerController::class, 'getMenuItemList']);
		Route::post('/getRestaurantByCode', [CustomerController::class, 'getRestaurantByCode']);
		Route::post('/getCouponList', [CustomerController::class, 'getCouponList']);
		Route::post('/getCoupanDetails', [CustomerController::class, 'getCoupanDetails']);
		Route::post('/getCartAmountDetails', [CustomerController::class, 'getCartAmountDetails']);
		//Route::post('/getSearchListByTab', [CustomerController::class, 'getSearchListByTab']);
		Route::post('/getDishesByKeyword', [CustomerController::class, 'getDishesByKeyword']);
		Route::post('/placeOrder', [CustomerController::class, 'placeOrder']);
	
		
	});
	
	// delivery boy api
	 Route::group(['prefix' => '/deliveryBoy'], function () {
		Route::post('/resetpassword', [DeliveryBoyController::class, 'resetpassword']);
		Route::post('/deliveryProfileUpdate', [DeliveryBoyController::class, 'deliveryProfileUpdate']);
		Route::post('/deliveryLoginStatusChange', [DeliveryBoyController::class, 'deliveryLoginStatusChange']);
		Route::post('/profilePicUpload', [DeliveryBoyController::class, 'profilePicUpload']);
		Route::post('/updatePassword', [DeliveryBoyController::class, 'updatePassword']);
		Route::post('/updateOrderStatus', [DeliveryBoyController::class, 'updateOrderStatus']);
		Route::post('/getProfileDetails', [DeliveryBoyController::class, 'getProfileDetails']);
		Route::post('/updateLatLong', [DeliveryBoyController::class, 'updateLatLong']);
		Route::get('/testNotification', [DeliveryBoyController::class, 'testNotification']);
		Route::post('/getOrdersByStatus', [DeliveryBoyController::class, 'getOrdersByStatus']);
		Route::post('/updateFirebaseId', [DeliveryBoyController::class, 'updateFirebaseId']);
		Route::post('/getCommissionRecords', [DeliveryBoyController::class, 'getCommissionRecords']);
		Route::post('/getPenultyDetails', [DeliveryBoyController::class, 'getPenultyDetails']);
		Route::post('/isUserActive', [DeliveryBoyController::class, 'isUserActive']);
	});
});

	Route::group(['prefix' => '/restaurant'], function () {
		 Route::post('/login', [RestaurantController::class, 'restaurantLogin']);
	});

	// customer api
    Route::group(['prefix' => '/customer'], function () {
		Route::post('/sendRegisterOTP', [CustomerController::class, 'sendRegisterOTP']);
	    Route::post('/verifyRegisterOTP', [CustomerController::class, 'verifyRegisterOTP']);
		Route::post('/registration', [CustomerController::class, 'registration']);
		Route::get('/cityList', [CustomerController::class, 'getCityList']);
		Route::post('/verifyPayment', [CustomerController::class, 'verifyPayment']);
		Route::post('/checknotification', [CustomerController::class, 'checknotification']);
	});
	
	//delivery boy login
	Route::group(['prefix' => '/deliveryBoy'], function () {
		Route::post('/loginProcess', [DeliveryBoyController::class, 'loginProcess']);
	});
	

	