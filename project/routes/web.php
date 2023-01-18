<?php
//admin
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RestaurantCategoryController;
use App\Http\Controllers\Admin\CuisineController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuSubcategoryController;
use App\Http\Controllers\Admin\FoodSliderController;
use App\Http\Controllers\Admin\RestaurantItemController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ResetpasswordController;
use App\Http\Controllers\Admin\RestaurantCouponController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\BusinessTypeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OtherController;
use App\Http\Controllers\Admin\CronjobController;
use App\Http\Controllers\Admin\CompanyInfoController;
use App\Http\Controllers\Admin\TermsController;
use App\Http\Controllers\Admin\RewardSlotController;
use App\Http\Controllers\Admin\CutletController;
use App\Http\Controllers\Admin\CutletHomeController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Artisan;

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

/*Route::get('/admin/template', function () { 
		return view('welcome');
	});*/

//cronjob

Route::get('/excel', [TestController::class, 'index']);

Route::group(['prefix' => '/cronjob'], function () {
	Route::get('/changeDeliveryBoyStatus', [CronjobController::class, 'changeDeliveryBoyStatus']);
	Route::get('/assignDeliveryBoy', [CronjobController::class, 'assignDeliveryBoy']);
	Route::get('/updateRestaurantServiceablity', [CronjobController::class, 'updateRestaurantServiceablity']);
	Route::get('/cutletCronjob', [CronjobController::class, 'cutletCronjob']);
	Route::get('/', [CronjobController::class, 'cutletCronjob']);
	Route::get('/updatePendingCutletStatus', [CronjobController::class, 'updatePendingCutletStatus']);
	Route::get('/connectServer', [CronjobController::class, 'connectServer']);
});



//admin route
Route::group(['middleware' => 'admin'], function () {
	Route::get('/welcome', [HomeController::class, 'welcome']);
	Route::get('/dashboard', [HomeController::class, 'dashboard']);
	Route::get('/profile/{id}', [ProfileController::class, 'profile'])->name('profile');
	Route::get('/profileshow/{id}', [ProfileController::class, 'show']);
	Route::post('profile-update/{id}', [ProfileController::class, 'updateprofile'])->name('updateprofile');


	// activity 

	Route::get('getActivityList', [ActivityController::class, 'getActivityList'])->name('admin.getActivityList');
	Route::group(['prefix' => '/activity'], function () {
		Route::get('list', [ActivityController::class, 'index'])->name('activity');
	});

	// Rating

	Route::get('getRatingList', [RatingController::class, 'getRatingList'])->name('admin.getRatingList');
	Route::group(['prefix' => '/rating'], function () {
		Route::get('list', [RatingController::class, 'index'])->name('rating');
		Route::get('/changestatus', [RatingController::class, 'changestatus']);
	});
	//designation
	Route::get('getDesignationList', [DesignationController::class, 'getDesignationList'])->name('admin.getDesignationList');
	Route::group(['prefix' => '/designation'], function () {
		Route::get('list', [DesignationController::class, 'index'])->name('designation');
		Route::post('store', [DesignationController::class, 'store'])->name('designation.store');
		Route::get('edit', [DesignationController::class, 'edit'])->name('designation.edit');
		Route::get('delete', [DesignationController::class, 'delete'])->name('designation.delete');
	});

	//businesstype
	Route::get('getBusinessTypeList', [BusinessTypeController::class, 'getBusinessTypeList'])->name('admin.getBusinessTypeList');
	Route::group(['prefix' => '/businesstype'], function () {
		Route::get('list', [BusinessTypeController::class, 'index'])->name('businesstype');
		Route::post('store', [BusinessTypeController::class, 'store'])->name('businesstype.store');
		Route::get('edit', [BusinessTypeController::class, 'edit'])->name('businesstype.edit');
		Route::get('delete', [BusinessTypeController::class, 'delete'])->name('businesstype.delete');
	});
	//city
	Route::get('getCityList', [CityController::class, 'getCityList'])->name('admin.getCityList');
	Route::group(['prefix' => '/city'], function () {
		Route::get('list', [CityController::class, 'index'])->name('city');
		Route::post('store', [CityController::class, 'store'])->name('city.store');
		Route::get('edit', [CityController::class, 'edit'])->name('city.edit');
		Route::get('delete', [CityController::class, 'delete'])->name('city.delete');
	});

	//Address
	Route::get('getAddressList', [AddressController::class, 'getAddressList'])->name('admin.getAddressList');
	Route::group(['prefix' => '/address'], function () {
		Route::get('/list', [AddressController::class, 'index'])->name('address');
		Route::get('/add', [AddressController::class, 'add'])->name('addressAdd');
		Route::get('/edit/{code}', [AddressController::class, 'edit']);
		Route::get('/delete', [AddressController::class, 'delete']);
		Route::post('/store', [AddressController::class, 'store']);
		Route::post('/update', [AddressController::class, 'update']);
		Route::get('/view/{code}', [AddressController::class, 'view']);
	});


	//Users
	Route::get('getuserslist', [UserController::class, 'getUserList'])->name('admin.getUserList');
	Route::group(['prefix' => '/users'], function () {
		Route::get('/list', [UserController::class, 'index'])->name('users');
		Route::get('/add', [UserController::class, 'add'])->name('usersAdd');
		Route::post('/store', [UserController::class, 'store']);
		Route::get('/delete', [UserController::class, 'delete']);
		Route::get('/edit/{code}', [UserController::class, 'edit']);
		Route::post('/update', [UserController::class, 'update']);
		Route::get('/view/{code}', [UserController::class, 'view']);

		//access rights url
		Route::get('/userAccessEditList/{code}', [UserController::class, 'userAccessEditList']);
		Route::get('/getUserAccessList', [UserController::class, 'getUserAccessList']);
		Route::get('/getUserAccessEditList', [UserController::class, 'getUserAccessEditList']);
		Route::post('/getAllPrivileges', [UserController::class, 'getAllPrivileges']);
		Route::post('/saveRights', [UserController::class, 'saveRights']);
	});

	//settings
	Route::get('getSettingList', [SettingController::class, 'getSettingList'])->name('admin.getSettingList');
	Route::group(['prefix' => '/setting'], function () {
		Route::get('/list', [SettingController::class, 'index']);
		Route::get('/add', [SettingController::class, 'add'])->name('settingAdd');
		Route::post('/store', [SettingController::class, 'store']);
		Route::get('/delete', [SettingController::class, 'delete']);
		Route::get('/edit/{code}', [SettingController::class, 'edit']);
		Route::post('/update', [SettingController::class, 'update']);
		Route::get('/view/{code}', [SettingController::class, 'view']);
		Route::get('/getMaintenanceMode', [SettingController::class, 'getMaintenanceMode']);
		Route::get('/updateMaintenanceOff', [SettingController::class, 'updateMaintenanceOff']);
		Route::post('/updateMaintenanceOn', [SettingController::class, 'updateMaintenanceOn']);
	});

	//delivery charges slots
	Route::get('getSlotsList', [DeliveryChargeController::class, 'getSlotsList'])->name('admin.getSlotsList');
	Route::group(['prefix' => '/deliveryCharges'], function () {
		Route::get('/list', [DeliveryChargeController::class, 'index']);
		Route::get('/add', [DeliveryChargeController::class, 'add']);
		Route::get('/edit/{code}', [DeliveryChargeController::class, 'edit']);
		Route::get('/delete', [DeliveryChargeController::class, 'delete']);
		Route::post('/store', [DeliveryChargeController::class, 'store']);
		Route::post('/update', [DeliveryChargeController::class, 'update']);
		Route::get('/view/{code}', [DeliveryChargeController::class, 'view']);
		Route::post('/checkOverlappingSlot', [DeliveryChargeController::class, 'checkOverlappingSlot']);
	});

	//Restaurant configuration
	//Restaurant
	Route::get('getRestaurantList', [RestaurantController::class, 'getRestaurantList'])->name('admin.getRestaurantList');
	Route::get('changeServiceable', [RestaurantController::class, 'changeServiceable'])->name('admin.changeServiceable');
	Route::get('getAreaDetails', [RestaurantController::class, 'getAreaDetails'])->name('admin.getAreaDetails');
	Route::get('checkDuplicateemail', [RestaurantController::class, 'checkDuplicateemail'])->name('admin.checkDuplicateemail');
	Route::get('checkDuplicatemobile', [RestaurantController::class, 'checkDuplicatemobile'])->name('admin.checkDuplicatemobile');
	Route::get('checkDuplicateemailOnUpdate', [RestaurantController::class, 'checkDuplicateemailOnUpdate'])->name('admin. checkDuplicateemailOnUpdate');
	Route::get('checkDuplicatemobileOnUpdate', [RestaurantController::class, 'checkDuplicatemobileOnUpdate'])->name('admin.checkDuplicatemobileOnUpdate');
	Route::group(['prefix' => '/partner'], function () {
		Route::get('/list', [RestaurantController::class, 'index'])->name('partner');
		Route::get('/add', [RestaurantController::class, 'add'])->name('restaurantAdd');
		Route::post('/store', [RestaurantController::class, 'store']);
		Route::get('/delete', [RestaurantController::class, 'delete']);
		Route::get('/edit/{code}', [RestaurantController::class, 'edit']);
		Route::post('/update', [RestaurantController::class, 'update']);
		Route::get('/view/{code}', [RestaurantController::class, 'view']);
		Route::get('deleteImage', [RestaurantController::class, 'deleteImage']);
		Route::get('/getRestaurantHours/{code}', [RestaurantController::class, 'getRestaurantHours']);
		Route::get('/updateRestaurantHour', [RestaurantController::class, 'updateRestaurantHour']);
		Route::get('/saveHours', [RestaurantController::class, 'saveHours']);
		Route::get('/deleteHourLine', [RestaurantController::class, 'deleteHourLine']);
	});


	Route::get('getRestaurantCategoryList', [RestaurantCategoryController::class, 'getRestaurantCategoryList'])->name('admin.getRestaurantCategoryList');
	Route::group(['prefix' => '/restaurant-category'], function () {
		Route::get('list', [RestaurantCategoryController::class, 'index'])->name('restaurantcategory');
		Route::post('store', [RestaurantCategoryController::class, 'store'])->name('restaurantcategory.store');
		Route::get('edit', [RestaurantCategoryController::class, 'edit'])->name('restaurantcategory.edit');
		Route::get('delete', [RestaurantCategoryController::class, 'delete'])->name('restaurantcategory.delete');
	});

	//Cuisine List
	Route::get('getCuisineList', [CuisineController::class, 'getCuisineList'])->name('admin.getCuisineList');
	Route::group(['prefix' => '/cuisine'], function () {
		Route::get('list', [CuisineController::class, 'index'])->name('cuisine');
		Route::post('store', [CuisineController::class, 'store'])->name('cuisine.store');
		Route::get('edit', [CuisineController::class, 'edit'])->name('cuisine.edit');
		Route::get('delete', [CuisineController::class, 'delete'])->name('cuisine.delete');
	});

	//Menu Category
	Route::get('getCategoryList', [MenuCategoryController::class, 'getCategoryList'])->name('admin.getCategoryList');
	Route::group(['prefix' => '/menuCategory'], function () {
		Route::get('list', [MenuCategoryController::class, 'index'])->name('category');
		Route::post('store', [MenuCategoryController::class, 'store'])->name('category.store');
		Route::get('edit', [MenuCategoryController::class, 'edit'])->name('category.edit');
		Route::get('delete', [MenuCategoryController::class, 'delete'])->name('category.delete');
	});

	//Menu Category
	Route::get('getSubcategoryList', [MenuSubcategoryController::class, 'getSubcategoryList'])->name('admin.getSubcategoryList');
	Route::group(['prefix' => '/menuSubcategory'], function () {
		Route::get('list', [MenuSubcategoryController::class, 'index'])->name('subcategory');
		Route::post('store', [MenuSubcategoryController::class, 'store'])->name('subcategory.store');
		Route::get('edit', [MenuSubcategoryController::class, 'edit'])->name('subcategory.edit');
		Route::get('delete', [MenuSubcategoryController::class, 'delete'])->name('subcategory.delete');
	});

	//Food Slider
	Route::get('getSliderList', [FoodSliderController::class, 'getSliderList'])->name('admin.getSliderList');
	Route::group(['prefix' => '/foodSlider'], function () {
		Route::get('list', [FoodSliderController::class, 'index'])->name('foodSlider');
		Route::post('store', [FoodSliderController::class, 'store'])->name('foodSlider.store');
		Route::get('edit', [FoodSliderController::class, 'edit'])->name('foodSlider.edit');
		Route::get('delete', [FoodSliderController::class, 'delete'])->name('foodSlider.delete');
	});

	//Terms Conditions
	Route::get('getTermsList', [TermsController::class, 'getTermsList'])->name('admin.getTermsList');
	Route::group(['prefix' => '/termsAndCondition'], function () {
		Route::get('list', [TermsController::class, 'index'])->name('terms');
		Route::post('store', [TermsController::class, 'store'])->name('terms.store');
		Route::get('edit', [TermsController::class, 'edit'])->name('terms.edit');
		Route::get('delete', [TermsController::class, 'delete'])->name('terms.delete');
	});

	//Reward Slot List
	Route::get('getRewardSlotList', [RewardSlotController::class, 'getRewardSlotList'])->name('admin.getRewardSlotList');
	Route::group(['prefix' => '/rewardSlots'], function () {
		Route::get('list', [RewardSlotController::class, 'index'])->name('rewardSlots');
		Route::post('store', [RewardSlotController::class, 'store'])->name('rewardSlots.store');
		Route::get('edit', [RewardSlotController::class, 'edit'])->name('rewardSlots.edit');
		Route::get('delete', [RewardSlotController::class, 'delete'])->name('rewardSlots.delete');
		Route::get('/checkOverLappingSlots', [RewardSlotController::class, 'checkOverLappingSlots']);
	});

	//Home Slider
	Route::get('getHomeSliderList', [HomeSliderController::class, 'getHomeSliderList'])->name('admin.getHomeSliderList');
	Route::get('getRestDetails', [HomeSliderController::class, 'getRestDetails'])->name('admin.getRestDetails');
	Route::group(['prefix' => '/homeSlider'], function () {
		Route::get('list', [HomeSliderController::class, 'index'])->name('homeSlider');
		Route::post('store', [HomeSliderController::class, 'store'])->name('homeSlider.store');
		Route::get('edit', [HomeSliderController::class, 'edit'])->name('homeSlider.edit');
		Route::get('delete', [HomeSliderController::class, 'delete'])->name('homeSlider.delete');
	});

	//Restaurant Item
	Route::get('getItemList', [RestaurantItemController::class, 'getItemList'])->name('admin.getItemList');
	Route::get('getSubcategoryDetails', [RestaurantItemController::class, 'getSubcategoryDetails'])->name('admin.getSubcategoryDetails');
	Route::group(['prefix' => '/restaurantItem'], function () {
		Route::get('/list', [RestaurantItemController::class, 'index'])->name('restaurantItem');
		Route::get('/add', [RestaurantItemController::class, 'add'])->name('restaurantItemAdd');
		Route::get('/edit/{code}', [RestaurantItemController::class, 'edit']);
		Route::get('/delete', [RestaurantItemController::class, 'delete']);
		Route::post('/store', [RestaurantItemController::class, 'store']);
		Route::post('/update', [RestaurantItemController::class, 'update']);
		Route::get('/view/{code}', [RestaurantItemController::class, 'view']);
		Route::get('/customizeaddon/{code}', [RestaurantItemController::class, 'customizeaddon']);
		Route::get('/deleteAddonCategory', [RestaurantItemController::class, 'deleteAddonCategory']);
		Route::get('/deleteAddonLine', [RestaurantItemController::class, 'deleteAddonLine']);
		Route::get('/addAddonLine', [RestaurantItemController::class, 'addAddonLine']);
		Route::get('/getAddonCategoryData', [RestaurantItemController::class, 'getAddonCategoryData']);
		Route::get('/addAddonCategory', [RestaurantItemController::class, 'addAddonCategory']);
		Route::get('/updateAddonCategory', [RestaurantItemController::class, 'updateAddonCategory']);
		Route::get('/generateExcelTemplate', [RestaurantItemController::class, 'generateExcelTemplate']);
		Route::get('/uploadExcel', [RestaurantItemController::class, 'uploadExcel']);
		Route::post('/uploadData', [RestaurantItemController::class, 'uploadData']);
		Route::post('/validateExcelFile', [RestaurantItemController::class, 'validateExcelFile']);
	});

	//Restaurant offer coupon
	Route::get('getCouponList', [RestaurantCouponController::class, 'getCouponList'])->name('admin.getCouponList');
	Route::group(['prefix' => '/restaurantCoupon'], function () {
		Route::get('/list', [RestaurantCouponController::class, 'index'])->name('restaurantOffer');
		Route::get('/edit/{code}', [RestaurantCouponController::class, 'edit'])->name('restaurantOffer.edit');
		Route::get('/view/{code}', [RestaurantCouponController::class, 'view'])->name('restaurantOffer.view');
		Route::post('/update', [RestaurantCouponController::class, 'update']);
		Route::get('/delete', [RestaurantCouponController::class, 'delete']);
	});

	//Customer List
	Route::get('getCustomerList', [CustomerController::class, 'getCustomerList'])->name('admin.getCustomerList');
	Route::group(['prefix' => '/customer'], function () {
		Route::get('/list', [CustomerController::class, 'index'])->name('customer');
		Route::get('/edit/{code}', [CustomerController::class, 'edit'])->name('customer.edit');
		Route::get('/view/{code}', [CustomerController::class, 'view'])->name('customer.view');
		Route::post('/update', [CustomerController::class, 'update']);
		Route::get('/delete', [CustomerController::class, 'delete']);
	});
	//Reset password
	Route::get('getDeliveryBoyList', [ResetpasswordController::class, 'getDeliveryBoyList'])->name('resetpassword.getDeliveryBoyList');
	Route::group(['prefix' => '/resetPassword'], function () {
		Route::get('/deliveryBoyList', [ResetpasswordController::class, 'index'])->name('resetpassword.list');
		Route::get('/resetDeliveryPassword', [ResetpasswordController::class, 'resetDeliveryPassword'])->name('resetpassword.reset');
	});

	//orders
	Route::group(['prefix' => '/order'], function () {
		Route::get('/pendingList', [OrderController::class, 'pendingList']);
		Route::get('/placedList', [OrderController::class, 'index']);
		Route::get('/getOrders', [OrderController::class, 'getOrders']);
		Route::get('/getPendingOrders', [OrderController::class, 'getPendingOrders']);
		Route::get('/rejectList', [OrderController::class, 'rejectedList']);
		Route::get('/getRejectedOrders', [OrderController::class, 'getRejectedList']);
		Route::post('/confirm', [OrderController::class, 'update']);
		Route::get('/view/{code}', [OrderController::class, 'view']);
		Route::get('/invoice/{code}', [OrderController::class, 'invoice']);
		Route::get('/tracking/{code}', [OrderController::class, 'tracking']);

		//view operations
		Route::get('/getOrderDetails', [OrderController::class, 'getOrderDetails']);
		Route::get('/getOrderStatusList', [OrderController::class, 'getOrderStatusList']);
		Route::get('/getPendingDeliveryBoys', [OrderController::class, 'getPendingDeliveryBoys']);
		Route::post('/transferOrder', [OrderController::class, 'transferOrder']);
		Route::get('/checkDeliveryBoyOrders', [OrderController::class, 'checkDeliveryBoyOrders']);
		Route::post('/expiredByAdmin', [OrderController::class, 'expiredByAdmin']);
		Route::get('/getOrderStatusList', [OrderController::class, 'getOrderStatusList']);

		//cancel order
		Route::get('/cancelOrderList', [OrderController::class, 'cancelOrderList']);
		Route::get('/getcancelorderlist', [OrderController::class, 'getcancelorderlist']);
	});

	//notification
	Route::group(['prefix' => '/notification'], function () {
		Route::get('create', [NotificationController::class, 'createNotification']);
		Route::get('getCustomersListByCity', [NotificationController::class, 'getCustomersListByCity']);
		Route::post('process', [NotificationController::class, 'processNotification']);
		Route::post('sendCommonNotification', [NotificationController::class, 'sendCommonNotification']);
	});

	//dashboard
	//notification
	Route::group(['prefix' => '/dashboard'], function () {
		Route::get('/getOrderCounts', [HomeController::class, 'getOrderCounts']);
		Route::get('/getRestaurant', [HomeController::class, 'getRestaurant']);
		Route::get('/getCustomer', [HomeController::class, 'getCustomer']);
		Route::get('/getDeliveryBoys', [HomeController::class, 'getDeliveryBoys']);
		Route::get('/getOrders', [HomeController::class, 'getOrders']);
		Route::get('/getOrdersGraphData', [HomeController::class, 'getOrdersGraphData']);
		Route::get('/getBarData', [HomeController::class, 'getBarData']);
	});

	//other
	Route::group(['prefix' => '/other'], function () {
		Route::get('/deliveryBoyCommissionList', [OtherController::class, 'deliveryBoyCommissionList']);
		Route::get('/getDeliveryBoyCommissionList', [OtherController::class, 'getDeliveryBoyCommissionList']);
		Route::get('/viewCurrentHistory', [OtherController::class, 'viewCurrentHistory']);
		Route::post('/save', [OtherController::class, 'save']);
		Route::get('/restaurantCommissionList', [OtherController::class, 'restaurantCommissionList']);
		Route::get('/viewResCurrentHistory', [OtherController::class, 'viewResCurrentHistory']);
		Route::post('/saveRes', [OtherController::class, 'saveRes']);
		Route::get('/getRestaurantCommissionList', [OtherController::class, 'getRestaurantCommissionList']);
		Route::get('/orderReport', [OtherController::class, 'orderReport']);
		Route::get('/getOrderReportList', [OtherController::class, 'getOrderReportList']);
		Route::get('/viewOrder/{code}', [OtherController::class, 'viewOrder']);
	});

	//company info
	Route::group(['prefix' => '/companyinfo'], function () {
		Route::get('/list', [CompanyInfoController::class, 'index']);
		Route::get('/edit', [CompanyInfoController::class, 'edit']);
		Route::post('/store', [CompanyInfoController::class, 'store']);
		Route::get('/getAddressFromPin', [CompanyInfoController::class, 'getAddressFromPin']);
	});

	//CutletList
	Route::get('getCutletList', [CutletController::class, 'getCutletList'])->name('admin.getCutletList');
	Route::group(['prefix' => '/cutlet'], function () {
		Route::get('/list', [CutletController::class, 'index'])->name('cutlet');
	});

	//Chat Controller
	Route::group(['prefix' => '/chat'], function () {
		Route::get('/', [ChatController::class, 'index']);
		Route::post('/sendMessage', [ChatController::class, 'sendMessage']);
		Route::get('/trackOrder/{type}/{code}', [ChatController::class, 'trackOrder']);
	});
});

//admin login
Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/update', [LoginController::class, 'updatePassword']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/reset-password', [LoginController::class, 'reset']);
Route::post('/forgot-password', [LoginController::class, 'resetPassword']);
Route::get('/recoverPassword/{token}', [LoginController::class, 'verifyTokenLink']);
Route::post('/recover-password', [LoginController::class, 'updateMemberPassword']);

//static pages

Route::get('/', [CutletHomeController::class, 'index']);
Route::get('/about', [CutletHomeController::class, 'about']);
Route::get('/privacy', [CutletHomeController::class, 'privacy']);
Route::get('/terms', [CutletHomeController::class, 'terms']);
Route::post('/sendmail', [CutletHomeController::class, 'sendmail']);

Route::get('/clear', function () {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	return "Cleared!";
});

//mail sending check
Route::get('/sendbasicemail', [AddressController::class, 'basic_email']);
