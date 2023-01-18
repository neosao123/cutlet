<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\RestaurantItemMaster;
use App\Models\Restaurants;
use App\Models\Users;
use App\Models\MenuCategory;
use App\Models\MenuSubCategory;
use App\Models\RestaurantOrder; 
use App\Models\RestaurantOffer;
use App\Models\CustomizedCategory;
use App\Models\Customizedcategorylineentries;
use App\Models\RestaurantOrderLineEntry;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Settings;
use DB;
use App\Classes\Notificationlibv_3;
use App\Models\ClientDevicesDetails;
use App\Classes\FirestoreActions;
header("Access-Control-Allow-Origin: *");
class RestaurantController extends Controller
{
    public function __construct(GlobalModel $model,ApiModel $apimodel)
	{
		$this->model = $model;
		$this->apimodel = $apimodel;
	}
	
	public function getDashboardCounts(Request $r)
	{
		$dataValidate=$r->validate([
			'restaurantCode' => 'required', 
        ]);
        $restaurantCode = $r->restaurantCode;
		
		$resTotalOrders=$resplacedOrders=$respendingOrders=$resdeliverOrders=$resrejectOrders=$rescancelOrders=0;
		$resoffer = $resitems = 0;
		$getTotalOrders=RestaurantOrder::select(DB::raw('COUNT(id) as totalOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['isActive' => 1])
				->whereNull('isExpired')
				->whereIn('restaurantordermaster.orderStatus', ['PLC','RFP','PUP','PRE','RCH','DEL','RJT'])
				->first();
		if(!empty($getTotalOrders)){
			$resTotalOrders=$getTotalOrders->totalOrders;
		}
		
		
		$getRead=RestaurantOrder::select(DB::raw('COUNT(id) as confirmOrders')) 
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['isActive' => 1])
				->whereIn('restaurantordermaster.orderStatus', ['RFP','PUP','PRE'])
				->whereNull('isExpired')
				->first();
		if(!empty($getConfirmOrders)){
			$resconfirmOrders=$getConfirmOrders->confirmOrders;
		}
		
		$getREPOrders=RestaurantOrder::select(DB::raw('COUNT(id) as rfpOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'RFP'])
				->whereNull('isExpired')
				->first();
		if(!empty($getREPOrders)){
			$resRFPOrders=$getREPOrders->rfpOrders;
		}
		
		$getPUPOrders=RestaurantOrder::select(DB::raw('COUNT(id) as pupOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'PUP'])
				->whereNull('isExpired')
				->first();
		if(!empty($getPUPOrders)){
			$resPUPOrders=$getPUPOrders->pupOrders;
		}
		
		$getPREOrders=RestaurantOrder::select(DB::raw('COUNT(id) as preOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'PRE']) 
				->whereNull('isExpired')
				->first();
		if(!empty($getPREOrders)){
			$resPREOrders=$getPREOrders->preOrders;
		}
		
		$getDeliverOrders=RestaurantOrder::select(DB::raw('COUNT(id) as deliverOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'DEL'])
				->whereNull('isExpired')
				->first();
		if(!empty($getDeliverOrders)){
			$resdeliverOrders=$getDeliverOrders->deliverOrders;
		}
		
		$getPlacedOrders=RestaurantOrder::select(DB::raw('COUNT(id) as pendingOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'PLC'])
				->whereNull('isExpired')
				->first();
		if(!empty($getPlacedOrders)){
			$resplaceOrders=$getPlacedOrders->pendingOrders;
		}
		
		$getRejectOrders=RestaurantOrder::select(DB::raw('COUNT(id) as rejectOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' =>'RJT'])
				->whereNull('isExpired')
				->first();
		if(!empty($getRejectOrders)){
			$resrejectOrders=$getRejectOrders->rejectOrders;
		}
		
		$getCancelOrders=RestaurantOrder::select(DB::raw('COUNT(id) as cancelOrders'))
	            ->where(['restaurantCode' => $restaurantCode])
				->where(['orderStatus' => 'CAN'])
				->whereNull('isExpired')
				->first();
				
		if(!empty($getCancelOrders)){
			$rescancelOrders=$getCancelOrders->cancelOrders;
		}
		
		$dataResOffer=RestaurantOffer::select(DB::raw('COUNT(id) as countOffer'))
	            ->where(['restaurantCode' => $restaurantCode])
				->first();
	    if(!empty($dataResOffer)){
			$vendoroffer=$dataResOffer->countOffer;
		}
		
		$dataResOffer=RestaurantOffer::select(DB::raw('COUNT(id) as countOffer'))
	            ->where(['restaurantCode' => $restaurantCode])
				->first();
	    if(!empty($dataResOffer)){
			$resoffer=$dataResOffer->countOffer;
		}
		$dataResItems=RestaurantItemMaster::select(DB::raw('COUNT(id) as countItems'))
	            ->where(['restaurantCode' => $restaurantCode])
				->first();
				
	    if(!empty($dataResItems)){
			$resitems=$dataResItems->countItems;
		}
		
		$custChoiceCat=$custAddonCat=0;
		$CCRecordsChoice = RestaurantItemMaster::select(DB::raw('COUNT(restaurantitemmaster.id) as countCustChoiceCat'))
						->join("customizedcategory", "restaurantitemmaster.code", "=", "customizedcategory.restaurantItemCode")
						->where('customizedcategory.categoryType','choice')
						->where('restaurantitemmaster.restaurantCode',$restaurantCode)
						->first();
		if(!empty($CCRecordsChoice)){
			$custChoiceCat=$CCRecordsChoice->countCustChoiceCat;
		}
						
		$CCRecordsAddon = RestaurantItemMaster::select(DB::raw('COUNT(restaurantitemmaster.id) as countCustAddonCat'))
						->join("customizedcategory", "restaurantitemmaster.code", "=", "customizedcategory.restaurantItemCode")
						->where('customizedcategory.categoryType','addon')
						->where('restaurantitemmaster.restaurantCode',$restaurantCode)
						->first();
		if(!empty($CCRecordsAddon)){
			$custAddonCat=$CCRecordsAddon->countCustAddonCat;
		}
		
		$res['orderCounts'] = array(
			    'totalOrders'=>$resTotalOrders,
				'placedOrders' =>$resplaceOrders,
				'readyforpickupOrders' =>$resRFPOrders,
				'preparingOrders'=>$resPREOrders,
				'pickedupOrders'=>$resPUPOrders,
				'deliveredOrders' =>$resdeliverOrders,
				'cancelledOrders' =>$rescancelOrders,
				'rejectedOrders' =>$resrejectOrders,
				'vendoroffer' =>$resoffer,
				'vendoritems' =>$resitems,
				'custChoiceCat'=>$custChoiceCat,
				'custAddonCat'=>$custAddonCat,
			);
			
		$response = [
					'status' => 200,
					'message' =>'Data Found.',
					'result' =>$res
				];
	     return response()->json($response, 200);
	}
	
	public function restaurantLogin(Request $r) 
    {
		$dataValidate=$r->validate([
            'restaurantContact' => 'required|digits:10',
            'password' => 'required',
        ]);
		DB::enableQueryLog();
		$user = Restaurants::where('ownerContact',$dataValidate['restaurantContact'])->first();
		$query_1 = DB::getQueryLog();
        //print_r($query_1);
		if(! $user ||  ! Hash::check($dataValidate['password'], $user->password)) {
            return response()->json(["status"=>300,"message"=>"Invalid Mobile number or password"], 200);
        }else{
			$resResult = Restaurants::select("restaurant.*","citymaster.cityname","customaddressmaster.state","customaddressmaster.district","customaddressmaster.taluka","customaddressmaster.place","customaddressmaster.pincode")
						->join("citymaster", "citymaster.code", "=", "restaurant.cityCode")
                        ->join("customaddressmaster","customaddressmaster.code","=","restaurant.addressCode")
						->where("restaurant.code",$user->code)
						->first(); 
			if(!empty($resResult)){
				$token = $user->createToken('CutletApp')->plainTextToken;
                $data['code'] =$resResult->code;
				$data['entityName'] =$resResult->entityName;
				$data['firstName'] = $resResult->firstName;
				$data['lastName'] = $resResult->lastName;
				$data['middleName'] = $resResult->middleName;
				$data['address'] = $resResult->address;
				$data['fssaiNumber']= $resResult->fssaiNumber;
				$data['gstNumber']= $resResult->gstNumber;
				$data['latitude']= $resResult->latitude;
				$data['longitude']= $resResult->longitude;			 			
				$data['ownerContact']=$resResult->ownerContact;
				$data['entityContact']=$resResult->entityContact; 
				$data['email']= $resResult->email;
				$data['cityCode']= $resResult->cityCode;
				$data['addressCode']= $resResult->addressCode;
				$data['packagingType']= $resResult->packagingType;
				$data['cityName']= $resResult->cityName;
				$data['place']= $resResult->place; 
				$data['district']= $resResult->district; 
				$data['taluka']= $resResult->taluka; 
				$data['pincode']= $resResult->pincode; 
				$data['state']= $resResult->state; 
				$image ="noimage";
				if(!empty($resResult->entityImage)){
					$pathEntity=url("uploads/restaurant/restaurantimage/".$resResult->entityImage);
					$data['entityImage']=$pathEntity;
				}
				if(!empty($resResult->gstImage)){
					$pathGst=url("uploads/restaurant/gstimage/".$resResult->gstImage);
					$data['gstImage']=$pathGst;
				}
				if(!empty($resResult->fssaiImage)){
					$pathFssai=url("uploads/restaurant/fssaiimage/".$resResult->fssaiImage);
					$data['fssaiImage']=$pathFssai;
				}				
				$response = [
					'status' => 200,
					'message' =>'Restaurant login successfully.',
					'result' =>$data,
					'token'=>$token
				];
				return response()->json($response, 200);
			}
			else{
				$response = [
					'status' => 300,
					'message' =>'Data not found',
					'result' =>[],	
				];
				return response()->json($response, 200); 
			}
           
        }
	}
	public function restaurantLoginOld(Request $r)
    {
        if (isset($r->restaurantContact) && $r->restaurantContact != '' && isset($r->password) && $r->password != '') {
                $restaurantContact=trim($r->restaurantContact);
				$password=trim($r->password);
				$result = Restaurants::where(['ownerContact' => $restaurantContact])->first();
			    if(empty($result)){
					return response()->json(["status" => 300, "message" => "Mobile number is not register."], 200);
				}
				else{
					  if(Auth::guard('restaurant')->attempt(['ownerContact' => $restaurantContact, 'password' => $password])) {
					   $resResult = Restaurants::select("restaurant.*","citymaster.cityname","customaddressmaster.state","customaddressmaster.district","customaddressmaster.taluka","customaddressmaster.place","customaddressmaster.pincode")
						->join("citymaster", "citymaster.code", "=", "restaurant.cityCode")
                        ->join("customaddressmaster","customaddressmaster.code","=","restaurant.addressCode")
						->where("restaurant.code",Auth::guard('restaurant')->user()->code)
						->first();
						if(!empty($resResult)){
							$data['code'] =$resResult->code;
							$data['entityName'] =$resResult->entityName;
							$data['firstName'] = $resResult->firstName;
							$data['lastName'] = $resResult->lastName;
							$data['middleName'] = $resResult->middleName;
							$data['address'] = $resResult->address;
							$data['fssaiNumber']= $resResult->fssaiNumber;
							$data['gstNumber']= $resResult->gstNumber;
							$data['latitude']= $resResult->latitude;
							$data['longitude']= $resResult->longitude;						
							$data['ownerContact']=$resResult->ownerContact;
							$data['entityContact']=$resResult->entityContact; 
							$data['email']= $resResult->email;
							$data['cityCode']= $resResult->cityCode;
							$data['addressCode']= $resResult->addressCode;
							$data['packagingType']= $resResult->packagingType;
							$data['cityName']= $resResult->cityName;
							$data['place']= $resResult->place; 
							$data['district']= $resResult->district; 
							$data['taluka']= $resResult->taluka; 
							$data['pincode']= $resResult->pincode; 
							$data['state']= $resResult->state; 
							$image ="noimage";
							if(!empty($resResult->entityImage)){
								$pathEntity=url("uploads/restaurant/restaurantimage/".$resResult->entityImage);
								$data['entityImage']=$pathEntity;
							}
							if(!empty($resResult->gstImage)){
								$pathGst=url("uploads/restaurant/gstimage/".$resResult->gstImage);
								$data['gstImage']=$pathGst;
							}
							if(!empty($resResult->fssaiImage)){
								$pathFssai=url("uploads/restaurant/fssaiimage/".$resResult->fssaiImage);
								$data['fssaiImage']=$pathFssai;
							}
							return response()->json(["status" => 200, "message" => "Data Found","result"=>$data], 200); 
						}else{
							return response()->json(["status" => 200, "message" => "Data Not Found","result"=>[]], 200); 
						}
					  }else{
					    return response()->json(["status" => 300, "message" => "Invalid Mobile number or Password"], 200); 
					  }
				} 
		}else{
			return response()->json(["status" => 400, "message" => "All * fields are required"], 400);
		}
	}
	
	public function restaurantPasswordUpdate(Request $r){		
	    $dataValidate=$r->validate([
            'restaurantCode' =>'required',
			'oldPassword'=>'required',
			'newPassword'=>'required',
        ]);
		$password=$dataValidate['oldPassword'];
		$newpassword=$dataValidate['newPassword'];
		$restaurantCode=$dataValidate['restaurantCode'];
		$result = Restaurants::where(['code' => $restaurantCode])->first();
		if(empty($result)){
			  $response = [
					'status' => 300,
					'message' =>'Restaurant is not register.',	
				];
				return response()->json($response, 200);
		}else{
			if(Hash::check($password,$result->password)){
				$updatePassword=Hash::make($newpassword);
				$updateResult=Restaurants::where('code',$restaurantCode)
							->update(['password' => $updatePassword]);
				if($updateResult == true){
					 $response = [
						'status' => 200,
						'message' =>'New password is updated successfully..',	
				     ];
					 return response()->json($response, 200);
				}else{
					$response = [
						'status' => 300,
						'message' =>'Failed to update the new password',	
				     ];
					 return response()->json($response, 200);
				}	
			}else{
				$response = [
					'status' => 300,
					'message' =>'Please provide an correct old password.',	
				];
				return response()->json($response, 200); 
			}
    	}
	}
	
	public function getprofileinfoRestaurant(Request $r)
	{
		$dataValidate=$r->validate([
            'restaurantCode' => 'required'
        ]);
		$restaurantCode = trim($dataValidate['restaurantCode']);
		$result = Restaurants::where(['code' => $restaurantCode])->first();
		if(empty($result)){
			$response = [
					'status' => 300,
					'message' =>'Restaurant is not register.',	
			  ];
			return response()->json($response, 200);
			
		}else{
			$resResult = Restaurants::select("restaurant.*","citymaster.cityname","customaddressmaster.state","customaddressmaster.district","customaddressmaster.taluka","customaddressmaster.place","customaddressmaster.pincode")
						->join("citymaster", "citymaster.code", "=", "restaurant.cityCode")
                        ->join("customaddressmaster","customaddressmaster.code","=","restaurant.addressCode")
						->where("restaurant.code",$restaurantCode)
						->first();
			if(!empty($resResult)){
					$data['code'] =$resResult->code;
					$data['entityName'] =$resResult->entityName;  
					$data['firstName'] = $resResult->firstName;
					$data['lastName'] = $resResult->lastName;
					$data['middleName'] = $resResult->middleName;
					$data['address'] = $resResult->address;
					$data['fssaiNumber']= $resResult->fssaiNumber;
					$data['gstNumber']= $resResult->gstNumber;
					$data['latitude']= $resResult->latitude;
					$data['longitude']= $resResult->longitude;						
					$data['ownerContact']=$resResult->ownerContact;
					$data['entityContact']=$resResult->entityContact; 
					$data['email']= $resResult->email;
					$data['cityCode']= $resResult->cityCode;
					$data['addressCode']= $resResult->addressCode;
					$data['packagingType']= $resResult->packagingType;
					$data['cityName']= $resResult->cityName;
					$data['place']= $resResult->place; 
					$data['district']= $resResult->district; 
					$data['taluka']= $resResult->taluka; 
					$data['pincode']= $resResult->pincode; 
					$data['state']= $resResult->state; 
					$image ="noimage";
					if(!empty($resResult->entityImage)){
						$pathEntity=url("uploads/restaurant/restaurantimage/".$resResult->entityImage);
						$data['entityImage']=$pathEntity;
					}
					if(!empty($resResult->gstImage)){
						$pathGst=url("uploads/restaurant/gstimage/".$resResult->gstImage);
						$data['gstImage']=$pathGst;
					}
					if(!empty($resResult->fssaiImage)){
						$pathFssai=url("uploads/restaurant/fssaiimage/".$resResult->fssaiImage);
						$data['fssaiImage']=$pathFssai;
					}
					$response = [
					'status' => 200,
					'message' =>'Data Found.',	
					"result"=>$data
					  ];
					return response()->json($response, 200);
			}
			else{
				 $response = [
					'status' => 300,
					'message' =>'Data Not Found.',	
					"result"=>[]
					  ];
					return response()->json($response, 200);	
			}
		}
	}
	
    public function getMainMenuList(Request $r)
	{
		$result = MenuCategory::where('isDelete','!=',1)
		          ->where('isActive',1)
		          ->orderBy('menucategory.priority', 'asc')
				  ->get();
		if(!empty($result)){
			$response = [
					'status' => 200,
					'message' =>'Data Found.',	
					"result"=>$result
					  ];
			return response()->json($response, 200);
		}
		else{
			$response = [
					'status' => 300,
					'message' =>'Data Not Found',	
					"result"=>[]
					  ];
			return response()->json($response, 200); 
		}
	}
	
	public function getMenuItemList(Request $r)
	{
		$dataValidate=$r->validate([
            'restaurantCode' => 'required'
        ]);
		$resCode = trim($dataValidate['restaurantCode']);
		$result = MenuCategory::where('isDelete','!=',1)
		          ->where('isActive',1)
		          ->orderBy('menucategory.priority', 'asc')
				  ->get();
		    if(!empty($result))
			{
				$data = array();
				foreach($result as $menuItem){
					$mainitemArray = array();
					$addonArray = array();
					$choiceArray = array(); 
					$maincount = 0;
					$catCode = $menuItem->code;
					$catName = $menuItem->menuCategoryName;
					$resResult = RestaurantItemMaster::select("restaurantitemmaster.*","restaurant.entityName","restaurant.isServiceable as restaurantIsServiceable")
						->join("restaurant", "restaurantitemmaster.restaurantCode", "=", "restaurant.code")
						->where('restaurantitemmaster.isActive',1)
						->where('restaurantitemmaster.isDelete','!=',1)
						->where('restaurantitemmaster.menuCategoryCode',$menuItem->code)
						->where('restaurantitemmaster.isAdminApproved',1)
						->where('restaurantitemmaster.restaurantCode',$resCode)
						->get();
					if (!empty($resResult)) {
						foreach($resResult as $resItem){
							$resItemCode = $resItem->code;
							$CCRecordsAddon = CustomizedCategory::select("customizedcategory.*")
							                  ->where('customizedcategory.isEnabled',1)
											  ->where('customizedcategory.restaurantItemCode',$resItemCode)
											  ->where('customizedcategory.categoryType','addon')
											  ->get();
							if (!empty($CCRecordsAddon)) {
								foreach ($CCRecordsAddon as $ccra) {
									$customizedCategoryCode = $ccra->code;
									$categoryTitle = $ccra->categoryTitle;
									$addonCustomizedCategoryArray = array();
									$CCRecordsAddonLine = Customizedcategorylineentries::select("customizedcategorylineentries.*")
							                  ->where('customizedcategorylineentries.isEnabled',1)
											  ->where('customizedcategorylineentries.customizedCategoryCode',$customizedCategoryCode)
											 
											  ->get();
										if (!empty($CCRecordsAddonLine)) {
											$addonCustomizedCategoryArray = array();
											foreach ($CCRecordsAddonLine as $ccraL) {
												$subCategoryTitle = $ccraL->subCategoryTitle;
												$price = $ccraL->price;
												$addonCustomizedCategoryArray[] = array(
													"lineCode" => $ccraL->code,
													"subCategoryTitle" => $subCategoryTitle,
													"price" => $price,
												);
											}
										}
										
									$addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
								}
							}
							
							$CCRecordsChoice = CustomizedCategory::select("customizedcategory.*")
							                  ->where('customizedcategory.isEnabled',1)
											  ->where('customizedcategory.restaurantItemCode',$resItemCode)
											  ->where('customizedcategory.categoryType','choice')
											  ->get();
											  
							if (!empty($CCRecordsChoice)) {
								foreach ($CCRecordsChoice as $ccrc) {
									$customizedCategoryCode = $ccrc->code;
									$categoryTitle = $ccrc->categoryTitle;
									$choiceCustomizedCategoryArray = array();
									$CCRecordsChoiceLine = Customizedcategorylineentries::select("customizedcategorylineentries.*")
							                  ->where('customizedcategorylineentries.isEnabled',1)
											  ->where('customizedcategorylineentries.customizedCategoryCode',$customizedCategoryCode)
											 
											  ->get();
									if (!empty($CCRecordsChoiceLine)) {
										$choiceCustomizedCategoryArray = array();
										foreach ($CCRecordsChoiceLine as $ccrcL) {
											$subCategoryTitle = $ccrcL->subCategoryTitle;
											$price = $ccrcL->price;
											$choiceCustomizedCategoryArray[] = array(
												"lineCode" => $ccrcL->code,
												"subCategoryTitle" => $subCategoryTitle,
												"price" => $price,
											);
										}
									}								
									$choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
								}
							}
							$path = "nophoto";
							if (!empty($resItem->itemPhoto)) {
								$filepath = url('uploads/restaurant/restaurantitemimage/' . $resItem->restaurantCode . '/' . $resItem->itemPhoto);
								//if(file_exists($filepath))  
									$path =  $filepath; 
							}
							$mainitemArray[] = array(
								"restaurantCode" => $resItem->restaurantCode,
								"itemCode" => $resItem->code,
								"itemName" => $resItem->itemName,
								"itemDescription" => $resItem->itemDescription,
								"salePrice" => $resItem->salePrice,
								"itemPhoto" => $path,
								"RestaurantName" => $resItem->entityName,
								"RestaurantIsServiceable"=>$resItem->restaurantIsServiceable,
								"isServiceable" => $resItem->itemActiveStatus,
								"cuisineType" => $resItem->cuisineType,
								"isActive" => $resItem->isActive,
								"maxOrderQty" => $resItem->maxOrderQty,
								"itemPackagingPrice" => $resItem->itemPackagingPrice,
								"addons" => $addonArray,
								"choice" => $choiceArray,
							);
							$maincount++;
							
						}
					}
					
					$subCategoryItemArray = array();
				    $subCateRecords = MenuSubCategory::where('isDelete','!=',1)
							  ->where('menusubcategory.isActive',1)
							  ->where('menusubcategory.menuCategoryCode',$catCode)
							  ->orderBy('menusubcategory.id', 'asc')
							  ->get();
							  
					if (!empty($subCateRecords)) {
						$subcount	= sizeof($subCateRecords);
						foreach ($subCateRecords as $subrow) {
							$subCategoryCode = $subrow->code;
							$subCategoryName = $subrow->menuSubCategoryName;
							$Records = RestaurantItemMaster::select("restaurantitemmaster.*","restaurant.entityName","restaurant.isServiceable as restaurantIsServiceable")
									->join("restaurant", "restaurantitemmaster.restaurantCode", "=", "restaurant.code")
									->where('restaurantitemmaster.isActive',1)
									->where('restaurantitemmaster.isDelete','!=',1)
									->where('restaurantitemmaster.menuSubCategoryCode',$subCategoryCode)
									->where('restaurantitemmaster.isAdminApproved',1)
									->where('restaurantitemmaster.restaurantCode',$resCode)
									->get();
							 if (!empty($Records)) {
								$itemArray = array();
								$count = sizeof($Records);
								foreach ($Records as $r) {
								   $vendorItemCode = $r->code;
								   $CCRecordsAddon = CustomizedCategory::select("customizedcategory.*")
							                  ->where('customizedcategory.isEnabled',1)
											  ->where('customizedcategory.restaurantItemCode',$vendorItemCode)
											  ->where('customizedcategory.categoryType','addon')
											  ->get();
									if (!empty($CCRecordsAddon)) {
										foreach ($CCRecordsAddon as $ccra) {
											$customizedCategoryCode = $ccra->code;
											$categoryTitle = $ccra->categoryTitle;
											$addonCustomizedCategoryArray = array();
											$CCRecordsAddonLine = Customizedcategorylineentries::select("customizedcategorylineentries.*")
													  ->where('customizedcategorylineentries.isEnabled',1)
													  ->where('customizedcategorylineentries.customizedCategoryCode',$customizedCategoryCode)										
													  ->get();
												if (!empty($CCRecordsAddonLine)) {
													$addonCustomizedCategoryArray = array();
													foreach ($CCRecordsAddonLine as $ccraL) {
														$subCategoryTitle = $ccraL->subCategoryTitle;
														$price = $ccraL->price;
														$addonCustomizedCategoryArray[] = array(
															"lineCode" => $ccraL->code,
															"subCategoryTitle" => $subCategoryTitle,
															"price" => $price,
														);
													}
												}
												
											$addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
										}
									}
									
									$CCRecordsChoice = CustomizedCategory::select("customizedcategory.*")
							                  ->where('customizedcategory.isEnabled',1)
											  ->where('customizedcategory.restaurantItemCode',$vendorItemCode)
											  ->where('customizedcategory.categoryType','choice')
											  ->get();
									if (!empty($CCRecordsChoice)) {
										foreach ($CCRecordsChoice as $ccrc) {
											$customizedCategoryCode = $ccrc->code;
											$categoryTitle = $ccrc->categoryTitle;
											$choiceCustomizedCategoryArray = array();
											$CCRecordsChoiceLine = Customizedcategorylineentries::select("customizedcategorylineentries.*")
													  ->where('customizedcategorylineentries.isEnabled',1)
													  ->where('customizedcategorylineentries.customizedCategoryCode',$customizedCategoryCode)
													  ->get();
											$choiceCustomizedCategoryArray = array();
											if (!empty($CCRecordsChoiceLine)) {
												foreach ($CCRecordsChoiceLine as $ccrcL) {
													$subCategoryTitle = $ccrcL->subCategoryTitle;
													$price = $ccrcL->price;
													$choiceCustomizedCategoryArray[] = array(
														"lineCode" => $ccrcL->code,
														"subCategoryTitle" => $subCategoryTitle,
														"price" => $price,
													);
												}
											}
											
										  $choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
										}
									}
									
									$path = "nophoto";
									if (!empty($resItem->itemPhoto)) {
										$filepath = url('uploads/restaurant/restaurantitemimage/' . $resItem->restaurantCode . '/' . $resItem->itemPhoto);
										//if(file_exists($filepath))  
											$path =  $filepath;
									}
									
									$itemArray[] = array(
										"restaurantCode" => $resItem->restaurantCode,
										"itemCode" => $resItem->code,
										"itemName" => $resItem->itemName,
										"itemDescription" => $resItem->itemDescription,
										"salePrice" => $resItem->salePrice,
										"itemPhoto" => $path,
										"RestaurantName" => $resItem->entityName,
								        "RestaurantIsServiceable"=>$resItem->restaurantIsServiceable,
										"cuisineType" => $resItem->cuisineType,
										"isActive" => $resItem->isActive,
										"isServiceable" => $resItem->itemActiveStatus,
										"maxOrderQty" => $resItem->maxOrderQty,
										"itemPackagingPrice" => $resItem->itemPackagingPrice,
										"addons" => $addonArray,
										"choice" => $choiceArray, 
									);
								   $maincount++;
								}
								 $subCategoryItemArray[] = array("subCategoryCode" => $subCategoryCode, "subCategoryName" => $subCategoryName, "count" => $count, "itemList" => $itemArray);
							 }
						}
					}
					if ($maincount > 0) {
						$data[] = array("menuCategoryCode" => $catCode, "count" => $maincount, "menuCategoryName" => $catName, "itemList" => $mainitemArray, "subCategoryList" => $subCategoryItemArray);
					} 
				}
			//	$response['menuItemList'] = ;
				$response = [
					'status' => 200,
					'message' =>'Data Found',	
					"result"=>$data
					  ];
			    return response()->json($response, 200); 
			}
			else {
				$response = [
					'status' => 300,
					'message' =>'No Data Found',	
					"result"=>[]
					  ];
			    return response()->json($response, 200);
			}
			
		    //DB::enableQueryLog();
			//$query_1 = DB::getQueryLog();
			//print_r($query_1);
	
	}
	
	public function updateFirebaseId(Request $r){
		$dataValidate=$r->validate([
			'restaurantCode'=>'required',
			'firebaseId'=>'required'
        ]);
        
        $data = array(
				'firebaseId' => $dataValidate["firebaseId"]
			);
			$updateResult= $this->model->doEdit($data, 'restaurant', trim($dataValidate['restaurantCode']));
		if($updateResult== true){
			 $response = [
				'status' => 200,
				'message' =>'Firebase ID updated successfully',				
			  ];
			return response()->json($response, 200); 	 
		}
		else{
			$response = [
				'status' => 300,
				'message' =>'Failed to update id...', 				
			  ];
			return response()->json($response, 200); 	 
		}
	}
	
	public function updateMenuItemStatus(Request $r){
		$dataValidate=$r->validate([
			'restaurantCode'=>'required',
			'itemCode'=>'required',
			'status'=>'required',
        ]);
        
        $data = array(
				'restaurantCode' => $dataValidate['restaurantCode'],
				'itemActiveStatus' => $dataValidate["status"]
			);
			$updateResult= $this->model->doEdit($data, 'restaurantitemmaster', trim($dataValidate['itemCode']));
		if($updateResult== true){
			 $response = [
				'status' => 200,
				'message' =>'Status updated successfully',				
			  ];
			return response()->json($response, 200); 	 
		}
		else{
			$response = [
				'status' => 300,
				'message' =>'Failed to update status...', 				
			  ];
			return response()->json($response, 200); 	 
		}
	}
	
	public function getOffersByVendor(Request $r){
		
		$dataValidate=$r->validate([
            'restaurantCode' => 'required'
        ]);
		$restaurantCode = trim($dataValidate['restaurantCode']);
		$result = Restaurants::where(['code' => $restaurantCode])->first();
		if(empty($result)){
			$response = [
					'status' => 300,
					'message' =>'Restaurant is not register.',	
			  ];
			return response()->json($response, 200);
			
		}else{
			 $offer = RestaurantOffer::select("restaurantoffer.id","restaurantoffer.code","restaurantoffer.restaurantCode","restaurantoffer.couponCode","restaurantoffer.offerType","restaurantoffer.discount","restaurantoffer.minimumAmount","restaurantoffer.perUserLimit","restaurantoffer.startDate","restaurantoffer.endDate","restaurantoffer.capLimit","restaurantoffer.flatAmount","restaurantoffer.isAdminApproved")
					  ->where('restaurantoffer.isDelete','!=',1)
					  ->where('restaurantoffer.isActive','=',1)
					  ->where('restaurantoffer.restaurantCode',$restaurantCode)
					  ->orderBy('restaurantoffer.id', 'asc')
					  ->get();
			 if(!empty($offer)){
				    $response = [
					'status' => 200,
					'message' =>'Data found',
                    'result' =>$offer					
					  ];
					return response()->json($response, 200);
			  }else{
				    $response = [
					'status' => 300,
					'message' =>'Data Not Found',
                    'result' =>[]					
					  ];
					return response()->json($response, 200);
				 
			}
		}
	}
	
	public function getOffersByOfferID(Request $r){  
	  	$dataValidate=$r->validate([
            'offerCode' => 'required'
        ]);
		$offerCode = $dataValidate['offerCode'];
		$offer = RestaurantOffer::select("restaurantoffer.id","restaurantoffer.code","restaurantoffer.restaurantCode","restaurantoffer.couponCode","restaurantoffer.offerType","restaurantoffer.discount","restaurantoffer.minimumAmount","restaurantoffer.perUserLimit","restaurantoffer.startDate","restaurantoffer.endDate","restaurantoffer.capLimit","restaurantoffer.flatAmount","restaurantoffer.isAdminApproved")
								  ->where('restaurantoffer.code',$offerCode)
								  ->first();
	    if(!empty($offer)){
			  $response = [
				'status' => 200,
				'message' =>'Data found',
				'result' =>$offer					
			  ];
			 return response()->json($response, 200); 
		  }else{
			  $response = [
				'status' => 300,
				'message' =>'Data Not Found',
				'result' =>[],					
			  ];
			 return response()->json($response, 200); 	 
		  } 
	}
	
	public function onlineOfflineStatusChange(Request $r){
		$dataValidate=$r->validate([
            'flag' => 'required',
			'restaurantCode'=>'required'
        ]);
		$restaurantCode=$dataValidate['restaurantCode'];
		$flag=$dataValidate['flag'];
		$getStatus=Restaurants::select("restaurant.isServiceable")
			           ->where('code',$restaurantCode)
					   ->first();
		if(!empty($getStatus)){
			$isServicable=$getStatus->isServiceable;				
			if($isServicable == 1){
				$updateResult=Restaurants::where('code',$restaurantCode)
				     ->update(['manualIsServiceable' => $flag]);
				if($updateResult== true){
					 $response = [
						'status' => 200,
						'message' =>'Service status updated successfully.',				
					  ];
					return response()->json($response, 200); 	 
				}
				else{
					$response = [
						'status' => 300,
						'message' =>'Failed to update the status...', 				
					  ];
					return response()->json($response, 200); 	 
				}	
			}else{ 
				$response = [
						'status' => 300,
						'message' =>'As per Serviceable Restaurant is Closed.You can not change status.', 				
					  ];
				return response()->json($response, 200); 	 
			}
		}
		else{
			$response = [
				'status' => 300,
				'message' =>'Restaurant is not found...', 				
			  ];
			return response()->json($response, 200); 
		}	
	}
		
	public function getOnlineOfflineStatus(Request $r){
		$dataValidate=$r->validate([
			'restaurantCode'=>'required'
        ]);
		$restaurantCode=trim($dataValidate['restaurantCode']);
		$getStatus=Restaurants::select("restaurant.isServiceable")
			           ->where('code',$restaurantCode)
					   ->first();
		if(!empty($getStatus)){
			 $response = [
				'status' => 200,
                'result' =>$getStatus				
			  ];
			  return response()->json($response, 200); 	
			}
			else{
			 $response = [
				'status' => 300,
				'message' =>'Service status is not found.',			
			  ];
			  return response()->json($response, 200);  		 
		}
	}
	
	public function getVendorOfferList(Request $r){
		$dataValidate=$r->validate([
			'restaurantCode'=>'required'
        ]);
		$restaurantCode=trim($dataValidate['restaurantCode']);
			$result = Restaurants::where(['code' => $restaurantCode])->first();
			if(!empty($result)){
				 $date=date('Y-m-d');
				 $offer = RestaurantOffer::select("restaurantoffer.id","restaurantoffer.code","restaurantoffer.couponCode","restaurantoffer.offerType","restaurantoffer.discount","restaurantoffer.minimumAmount","restaurantoffer.perUserlimit","restaurantoffer.startDate as startDate","restaurantoffer.endDate as endDate","restaurantoffer.capLimit","restaurantoffer.termsAndConditions","restaurantoffer.flatAmount as flatAmount","restaurantoffer.isAdminApproved")
						  ->where('restaurantoffer.isDelete','!=',1)
						  ->where('restaurantoffer.isActive','=',1)
						  ->where('restaurantoffer.startDate','<=',$date)
						  ->where('restaurantoffer.endDate','>=',$date)
						  ->where('restaurantoffer.restaurantCode',$restaurantCode)
						  ->orderBy('restaurantoffer.id', 'asc')
						  ->get();   
				 if(!empty($offer)){
					  $response = [
						'status' => 200,
						'message' =>'Data found',
						'result' =>$offer					
					  ];
					 return response()->json($response, 200);	 
				 }
				 else{
					 $response = [
						'status' => 300,
						'message' =>'Data Not Found',
						'result' =>[],					
					  ];
					 return response()->json($response, 200); 	
				 }
			}else{
				$response = [
					'status' => 300,
					'message' =>'Restaurant is not register.',	
			    ];
			   return response()->json($response, 200);
			}	
	  }
	  
  public function addVendorCouponOffer(Request $r){
		 $dataValidate=$r->validate([
			'restaurantCode'=>'required',
			'couponCode'=>'required',
			'offerType'=>'required',
			'minimumAmount'=>'required|numeric|gt:0',
			'perUserLimit'=>'required|numeric',
			'startDate'=>'required',
			'endDate'=>'required',
			'termsAndConditions'=>'nullable',
			'discount'=>'nullable',
			'capLimit'=>'nullable|numeric',
			'flatAmount'=>'nullable|numeric'
         ]);
		$capLimit=$discount=$termsAndConditions='';
        $flatAmount=0;		
		$invalid=0;
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($dataValidate['termsAndConditions']) && $dataValidate['termsAndConditions'] !=''){
		  $termsAndConditions=$dataValidate['termsAndConditions'];
		}
		if(strtolower($dataValidate['offerType'])=='cap'){
			if(isset($dataValidate['discount']) && $dataValidate['discount']!="" && isset($dataValidate['capLimit']) && $dataValidate['capLimit']!=""){
				$discount = $dataValidate['discount'];
				$capLimit = $dataValidate['capLimit'];
			}else{
				$invalid=1;
			}
		}elseif(strtolower($dataValidate['offerType'])=='flat'){
			if(isset($dataValidate['flatAmount']) && $dataValidate['flatAmount']!=""){
				$flatAmount = $dataValidate['flatAmount'];
			}else{
				$invalid=1;
			}
		}else{
			$invalid=1;
			$response = [
						'status' => 300,
						'message' =>'Invalid Offer Type.',				
					  ];
			return response()->json($response, 200); 	
		}
		
		if($invalid==0){
			$data = array(
				'restaurantCode' => $dataValidate['restaurantCode'],
				'couponCode' => $dataValidate['couponCode'],
				'offerType' => strtolower($dataValidate['offerType']),
				'minimumAmount' => trim($dataValidate["minimumAmount"]),
				'capLimit' => trim($capLimit),
				'perUserLimit' => trim($dataValidate["perUserLimit"]),
				'startDate' => trim($dataValidate["startDate"]),
				'endDate' => trim($dataValidate["endDate"]),
				'termsAndConditions' => trim($termsAndConditions),
				'discount' => trim($discount),
				'addID' => $dataValidate['restaurantCode'],
				'addIP' => $ip,
				'isActive' => 1,
				'flatAmount' => trim($flatAmount),
				'isAdminApproved' =>0
			);
			$code = $this->model->addNew($data, 'restaurantoffer', 'ROFF');
			if($code!=false){
				 $response = [
						'status' => 200,
						'message' =>'Offer Coupon added successfully',
						'result' =>$code					
			     ];
				return response()->json($response, 200);
			}else{
				$response = [
						'status' => 300,
						'message' =>'Failed to add offer coupon.',					
			     ];
				return response()->json($response, 200);
			}
			}else{
				$response = [
						'status' => 300,
						'message' =>'Some parameters are missing.',				
			     ];
				return response()->json($response, 200);
			}
	 }
	 
   public function updateVendorCouponOffer(Request $r){
	    $dataValidate=$r->validate([
		    'offerCode'=>'required',
			'restaurantCode'=>'required',
			'couponCode'=>'required',
			'offerType'=>'required',
			'minimumAmount'=>'required|numeric|gt:0',
			'perUserLimit'=>'required|numeric',
			'startDate'=>'required',
			'endDate'=>'required',
			'termsAndConditions'=>'nullable',
			'discount'=>'nullable',
			'capLimit'=>'nullable|numeric',
			'flatAmount'=>'nullable|numeric'
         ]);
		$capLimit=$discount=$termsAndConditions='';
        $flatAmount=0;		
		$invalid=0;
		$ip = $_SERVER['REMOTE_ADDR'];
			if(isset($dataValidate['termsAndConditions']) && $dataValidate['termsAndConditions'] !=''){
		  $termsAndConditions=$dataValidate['termsAndConditions'];
		}
		if(strtolower($dataValidate['offerType'])=='cap'){
			if(isset($dataValidate['discount']) && $dataValidate['discount']!="" && isset($dataValidate['capLimit']) && $dataValidate['capLimit']!=""){
				$discount = $dataValidate['discount'];
				$capLimit = $dataValidate['capLimit'];
			}else{
				$invalid=1;
			} 
		}elseif(strtolower($dataValidate['offerType'])=='flat'){
			if(isset($dataValidate['flatAmount']) && $dataValidate['flatAmount']!=""){
				$flatAmount = $dataValidate['flatAmount'];
			}else{
				$invalid=1;
			}
		}else{
			$invalid=1;
			$response = [
						'status' => 300,
						'message' =>'Invalid Offer Type.',				
					  ];
			return response()->json($response, 200); 	
		}
		if($invalid==0){
			$data = array(
				'restaurantCode' => $dataValidate['restaurantCode'],
				'couponCode' => $dataValidate['couponCode'],
				'offerType' => strtolower($dataValidate['offerType']),
				'minimumAmount' => trim($dataValidate["minimumAmount"]),
				'capLimit' => trim($capLimit),
				'perUserLimit' => trim($dataValidate["perUserLimit"]),
				'startDate' => trim($dataValidate["startDate"]),
				'endDate' => trim($dataValidate["endDate"]),
				'termsAndConditions' => trim($termsAndConditions),
				'discount' => trim($discount),
				'addID' => $dataValidate['restaurantCode'],
				'addIP' => $ip,
				'isActive' => 1,
				'flatAmount' => trim($flatAmount),
				'isAdminApproved' =>0
			);
			$result= $this->model->doEdit($data, 'restaurantoffer', trim($dataValidate['offerCode']));
			if($result != false){
				$response = [
							'status' => 200,
							'message' =>'Offer Coupon updated successfully.'				
				 ];
				return response()->json($response, 200);
			}else{
				$response = [
							'status' => 300,
							'message' =>'Failed to update Offer Coupon.'				
				 ];
				return response()->json($response, 200); 
			}
		}
		else{
				$response = [
						'status' => 300,
						'message' =>'Some parameters are missing.',				
			     ];
				return response()->json($response, 200);
		}
   }
   
   public function deleteVendorCouponOffer(Request $r){
	   $dataValidate=$r->validate([
		    'offerCode'=>'required',
         ]);
	   
		$ip = $_SERVER['REMOTE_ADDR'];
		$today = date('Y-m-d H:i:s');
		$data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' =>'', 'deleteDate' => $today]; 
		$result=$this->model->doEditWithField($data, 'restaurantoffer','code',$dataValidate['offerCode']);			
	    if($result==true){
			$response = [
							'status' => 200,
							'message' =>'Offer Coupon deleted successfully.'				
			];
			return response()->json($response, 200);
		}
		else{
			$response = [
							'status' => 300,
							'message' =>'Failed to delete Offer Coupon.'				
			];
			return response()->json($response, 200); 
		}
	    
   }
   
   public function confirmOrderStatusUpdate(Request $r) 
   {
	   $dataValidate=$r->validate([
		    'restaurantCode'=>'required',
			'orderCode'=>'required',
			'orderStatus'=>'required',
         ]);
	   DB::enableQueryLog();
	   //$query_1 = DB::getQueryLog();
       //print_r($query_1); 
	   $getData=RestaurantOrder::select("restaurantordermaster.id","restaurantordermaster.restaurantCode")
	            ->where('code',$dataValidate['orderCode'])
				->whereNotIn('restaurantordermaster.orderStatus', ['CAN','DEL','RJT','PND'])
				->first();
	   
	   if(!empty($getData)){
		   $orderCode = $dataValidate['orderCode'];
		   $timeStamp = date("Y-m-d h:i:s");
		   $data = array('editID' => $getData->restaurantCode, 'editDate' => $timeStamp);
		   $resCode=$getData->restaurantCode;
		   switch($dataValidate['orderStatus']){
				case 'PRE':	
					$data['orderStatus'] = 'PRE';
					$reason = "Accepted Order and Preparing it";
					$message="Order No-".$orderCode." is set for preparing.";
					$title="Order Accepted";				
					break;
				case 'RFP':					
					$data['orderStatus'] = 'RFP';
					$reason = "Order is prepared and ready for pickup now";
					$message="Order No-".$orderCode." is ready for pickup now";
					$title="Order Picked up";
					break;
				case 'REL':	
					$data['orderStatus'] = 'REL';
					$reason = 'Order is released now';
					$message="Order No-".$orderCode." is released now";
					$title ="Order Released";
					break;
				case 'RCH': 
					$data['orderStatus'] = 'RCH';
					$reason = 'Order is reached';
					$message="Order No-".$orderCode." is reached";
					$title="Order Reached";
					break;
				}  
                $result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);				
		        if($result == true){
					$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=",$dataValidate['orderStatus'])));
					$firestoreAction = new FirestoreActions();
                    $firestoreAction->update_refresh_code($resCode);
					if ($order_status && count($order_status)>0) {
				// 		if($dataValidate['orderStatus']=='PRE'){
				// 			$url='';
				// 			$port='';
				// 			$checkActiveConnectedPort = $this->model->selectQuery(array('activeports.port','activeports.id'),'activeports',array(),array('activeports.status'=>array('=',1),"activeports.isConnected"=>array('=','0'),"activeports.isJunk"=>array('=','0')),array('activeports.id'=>'ASC'),array(),'1');
				// 			if($checkActiveConnectedPort && count($checkActiveConnectedPort)>0){
				// 					$port=$checkActiveConnectedPort[0]->port;
				// 					$url = "https://myvegiz.com";
				// 					$id=$checkActiveConnectedPort[0]->id;
				// 					$updateOrder['trackingPort']=$port;
				// 					$update = $this->model->doEdit($updateOrder,'restaurantordermaster',$orderCode);
				// 					DB::table('activeports')->where('id',$id)->update(['isConnected'=>1]);
				// 			}
				// 	    }
						$order_status_record = $order_status[0];
						$statusTitle = $order_status_record->messageTitle;
						#replace $ template in title 
						$statusDescription = $order_status_record->messageDescription;
						$statusDescription = str_replace("$", $orderCode, $statusDescription);
						$dataBookLine = array(
							"orderCode" => $orderCode,
							"statusPutCode" => $dataValidate['restaurantCode'],
							"statusLine" => $dataValidate['orderStatus'],
							"reason" => $reason,
							"statusTitle" => $statusTitle,
				            "statusDescription" => $statusDescription,
							"statusTime" => $timeStamp,
							"isActive" => 1
						);
						$bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
					}
					$getResOrderResult=RestaurantOrder::select("restaurantordermaster.id","restaurantordermaster.restaurantCode")
							->where('code',$dataValidate['orderCode'])
							->first();
					if(!empty($getResOrderResult)){
						$orderStatus = $getResOrderResult->orderStatus;
						$clientCode = $getResOrderResult->clientCode;
						$deliveryBoyCode = $getResOrderResult->deliveryBoyCode;				
						$vendCode = $getResOrderResult->restaurantCode; 
						
						$clientData = ClientDevicesDetails::select("clientdevicedetails.firebaseId")
								->where('clientdevicedetails.clientCode',$clientCode) 
								->first();
						if(!empty($clientData)){
							$DeviceIdsArr =array();
							$DeviceIdsArr[] = $clientData->firebaseId;
							$message = 'Apologies! The order you placed for No-'.$orderCode.' is rejected. Please try later';
							$title = 'Order Rejected';
							$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode);
						}
						$userData  = Users::select("usermaster.firebase_id")
										->where('usermaster.code',$deliveryBoyCode) 
										->first();
						if(!empty($userData)){
							$DeviceIdsArr =array();
							$DeviceIdsArr[] = $userData->firebase_id;
							$message = 'Order No-'.$orderCode.' is being rejected.';
							$title = 'Order Rejected';
							$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode);
						}		
						
					}
					 $response = [
							'status' => 200,
							'message' =>'Order Status updated successfully.'
						];
			        return response()->json($response, 200);
					
			    }else{
		      $response = [
							'status' => 300,
							'message' =>'Failed to update order status.'
						];
			 return response()->json($response, 200);
		  }
	    }else{
		   $response = [
							'status' => 300,
							'message' =>'Invalid Order Code.'
						];
			return response()->json($response, 200);
	   }
	   
   }

   public function rejectOrderByVendor(Request $r){
	    $dataValidate=$r->validate([
		    'restaurantCode'=>'required',
			'orderCode'=>'required',
         ]);
		$ip = $_SERVER['REMOTE_ADDR'];
		$resCode=$dataValidate['restaurantCode'];
	    DB::enableQueryLog();
		$getData=RestaurantOrder::select("restaurantordermaster.id","restaurantordermaster.restaurantCode")
	            ->where('code',$dataValidate['orderCode'])
				->first();
		if(!empty($getData)){
			$orderCode = $dataValidate['orderCode'];
			$timeStamp = date("Y-m-d h:i:s");
			$data = array('orderStatus' => 'RJT', 'paymentStatus' => 'RJCT', 'editDate' => $timeStamp);
			$result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
			if ($result == true) {
				$firestoreAction = new FirestoreActions();
                $firestoreAction->update_refresh_code($resCode);
				$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=","RJT")));
					if ($order_status && count($order_status)>0) {
						$order_status_record = $order_status[0];
						$statusTitle = $order_status_record->messageTitle;
						#replace $ template in title 
						$statusDescription = $order_status_record->messageDescription;
						$statusDescription = str_replace("$", $orderCode, $statusDescription);
						$dataBookLine = array(
								"orderCode" => $orderCode,
								"statusPutCode" => $dataValidate['restaurantCode'],
								"statusLine" => 'RJT',
								"reason" => 'Order Rejected By Restaurant',
								"statusTime" => $timeStamp,
								"statusTitle" => $statusTitle,
								"statusDescription" => $statusDescription,
								"isActive" => 1
							);
						$bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
					}
				$resResult=RestaurantOrder::select("restaurantordermaster.*")
							->where('code',$dataValidate['orderCode'])
							->first();
				if(!empty($resResult)){
					$orderStatus = $resResult->orderStatus;
					$clientCode = $resResult->clientCode;
					$deliveryBoyCode = $resResult->deliveryBoyCode;				
					$vendCode = $resResult->restaurantCode;   
					$subTotal=$resResult->subTotal;
					$grandTotal=$resResult->grandTotal;	
					if($orderStatus!='PND')  
					{
						$DBFlag=0;
						$restoFlag=0;
						if($orderStatus=='PLC')
						{  
							$DBFlag=1;
						}
						else
						{
							$restoFlag=1;
							$DBFlag=1;
						} 
                       //DB::enableQueryLog();
						if($DBFlag==1){							
							$dataRjt['orderCode'] = null;
							$dataRjt['editID'] = $vendCode;
							$dataRjt['editIP'] = $ip;
							$dataRjt['orderCount'] = 0; 
							$dataRjt['orderType']='food';
							$delbRejectOrder = $this->model->doEditWithCondition($dataRjt, 'deliveryBoyActiveOrder', $deliveryBoyCode,'deliveryBoyCode');
							$query_1 = DB::getQueryLog();
                           // print_r($query_1);
							//exit();
							//$dBoyRelease['deliveryBoyCode']=null;
							//$resultRelase = $this->model->doEdit($dBoyRelease, 'restaurantordermaster', $orderCode);						
					
						}
						if($restoFlag=1){ 
						   $settingResult = Settings::select('settings.*')
											 ->where('settings.code','SET_5')
											 ->where('settings.isActive',1)
											 ->first();
							if(!empty($settingResult)){
								$touchPoint=$settingResult->settingValue;
								$dataUpCnt['commissionAmount'] = $touchPoint;
								$dataUpCnt['deliveryBoyCode'] = $deliveryBoyCode;
								$dataUpCnt['orderCode'] = $orderCode;
								$dataUpCnt['orderType'] = "food";
								$dataUpCnt['isActive'] = 1;
								$delboyCommission = $this->model->addNew($dataUpCnt, 'deliveryboyearncommission', 'DBEC');
							}
							
								//vendor penaulty
								$settingResult = Settings::select('settings.*')
												 ->where('settings.code','SET_7')
												 ->where('settings.isActive',1)
												 ->first();
								if(!empty($settingResult))
								{
									$vendorPenulty = $settingResult->settingValue;
									if($vendorPenulty!=0 && $vendorPenulty!='' && $vendorPenulty!=NULL){
										$commAmount = round(($grandTotal * $vendorPenulty) / 100,2);
										$vcData['comissionPercentage'] = $vendorPenulty;
										$vcData['comissionAmount'] = $commAmount;
										$vcData['subTotal'] = $subTotal;
										$vcData['restaurantAmount'] = $subTotal-$commAmount;
										$vcData['grandTotal'] = $grandTotal;
										$vcData['commissionType'] = 'penalty';
										$vcData['restaurantCode'] =$vendCode;
										$vcData['orderCode'] = $orderCode;
										$vcData['isActive'] = 1;
										$vendorCommission = $this->model->addNew($vcData, 'restaurantordercommission', 'VNDC');
									} 
								} 
						   }						
					}
					$clientData = ClientDevicesDetails::select("clientdevicedetails.firebaseId")
									->where('clientdevicedetails.clientCode',$clientCode) 
									->first();
					if(!empty($clientData)){
						$DeviceIdsArr =array();
						$DeviceIdsArr[] = $clientData->firebaseId;
						$message = 'Apologies! The order you placed for No-'.$orderCode.' is rejected. Please try later';
						$title = 'Order Rejected';
						$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode);
					}
					$userData  = Users::select("usermaster.firebase_id")
									->where('usermaster.code',$deliveryBoyCode) 
									->first();
					if(!empty($userData)){
						$DeviceIdsArr =array();
						$DeviceIdsArr[] = $userData->firebase_id;
						$message = 'Order No-'.$orderCode.' is being rejected.';
						$title = 'Order Rejected';
						$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode);
					}				
				}
				 $response = [
							'status' => 200,
							'message' =>'Order Rejected successfully'
						];
			     return response()->json($response, 200);
				
			}else{
				 $response = [
							'status' => 300,
							'message' =>'Failed to Reject Order.'
						];
			    return response()->json($response, 200);
			}
		}
		else{
		    $response = [
							'status' => 300,
							'message' =>'Invalid Order Code.'
						];
			return response()->json($response, 200);
		}
	    
    }
	
	public function getAllOrderListByRestaurant(Request $r){
		$dataValidate=$r->validate([
		    'restaurantCode'=>'required',
         ]);
		$ip = $_SERVER['REMOTE_ADDR'];
	    DB::enableQueryLog();
		$code=$dataValidate['restaurantCode'];
		$resResult = RestaurantOrder::select("restaurantordermaster.*","clientmaster.name","clientmaster.mobile")
						->join("clientmaster", "clientmaster.code", "=", "restaurantordermaster.clientCode")
						->join("paymentstatusmaster", "paymentstatusmaster.statusSName", "=", "restaurantordermaster.paymentStatus")
						->join("restaurantorderstatusmaster", "restaurantorderstatusmaster.statusSName", "=", "restaurantordermaster.orderStatus")
						->join("usermaster", "usermaster.code", "=", "restaurantordermaster.deliveryBoyCode")
						->where('restaurantordermaster.restaurantCode',$code)
						//->where('restaurantordermaster.paymentStatus','PID')
						->whereRaw('(restaurantordermaster.deliveryBoyCode !="" and restaurantordermaster.deliveryBoyCode is not null)')
						->whereRaw('(restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null)') 
						//->whereIn('restaurantordermaster.orderStatus', ['PLC','PRE','RFP','PUP','RCH'])
						->whereIn('restaurantordermaster.orderStatus', ['PLC','PRE','RFP'])
						->orderBy('restaurantordermaster.id', 'desc')
						->get();  
	    //$query_1 = DB::getQueryLog();
        //print_r($query_1);
		$ordersArray=array();
        if(!empty($resResult)){
			foreach ($resResult as $row) {
				$orderCode = $row->code;
				$dbCode = $row->deliveryBoyCode;
				$orderDate = date('Y-m-d h:i:s',strtotime($row->addDate));
				$orderAcceptDateTime = $row->statusTime;
				$preparingMinutes = $row->preparingMinutes;
				$DBName = "";
				$DBContact = "";
				$dBoy = Users::select("usermaster.*")
						->where("usermaster.code",$dbCode)
						->first();
				if(!empty($dBoy)){
					$DBName=$dBoy->name;
					$DBContact=$dBoy->mobile;
				}
				//empty order array
				$particulars = $order = array();
				$resOrders = RestaurantOrderLineEntry::select("restaurantorderlineentries.*","restaurantitemmaster.itemName","restaurantitemmaster.salePrice","restaurantitemmaster.itemPhoto","restaurantitemmaster.restaurantCode")
							->join("restaurantitemmaster", "restaurantitemmaster.code", "=", "restaurantorderlineentries.restaurantItemCode")
							->where('restaurantorderlineentries.isActive',1)
							->where('restaurantorderlineentries.orderCode',$orderCode) 
							->orderBy('restaurantorderlineentries.id','desc')
							->get();
				$srno = 0;
				if (!empty($resOrders)){
					foreach ($resOrders as $r) {
						$itemPhotoCheck = $r->itemPhoto;
						$itemPhoto = "";
						if(!empty($itemPhotoCheck)){
							 $path=env('IMG_URL').'uploads/restaurant/restaurantitemimage/'.$r->restaurantCode.'/'.$itemPhotoCheck;
							if (file_exists($path)) {
								$itemPhoto=$path; 
							}
						}
						/*$resultArr=$addonArray=$resultAddonArray=[];
						if($r->addonsCode!='' && $r->addonsCode!=NULL){
						   $r->addonsCode = rtrim($r->addonsCode,', ');
						   $savedaddonsCodes = explode(', ',$r->addonsCode);	
						   $categoryArr=[];
						   foreach($savedaddonsCodes as $addon){
								
								$joinType1 = array('customizedcategory' => 'inner');
								$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
								$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
								$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),"","","",$joinType1);
								if($getAddonDetails && count($getAddonDetails)>0){
									$categoryArr = $getAddonDetails[0];
								}
								$resultArr[]=$categoryArr; 
							}
						}*/

						$particulars[] = array("itemName" => $r->itemName, "itemPhoto" => $itemPhoto, "quantity" => $r->quantity, "pricewithQty" => $r->priceWithQuantity,"addOns"=>$r->addons);
						$srno++;	
					} 
				}
				
				//assignOrdeValues
					$order['orderCode'] = $row->code;
					$order['clientName'] = $row->name;
					$order['orderStatus'] = $row->orderStatus;
					$order['coupanCode'] = $row->coupanCode;
					$order['discount'] = $row->discount;
					$order['tax'] = $row->tax;
					$order['totalPackgingCharges'] = $row->totalPackgingCharges;
					$order['shippingCharges'] = $row->shippingCharges;
					
					$amount = $row->grandTotal;
					$grandTotal = round($amount * (18/100));
					$amount = ($amount) - $row->shippingCharges;
					$order['totalAmount'] = $row->grandTotal;
					$order['actualAmount'] = $row->subTotal;
					$order['orderDate'] = $orderDate;
					$order['prepareDateTime'] = $orderAcceptDateTime;
					$order['preparingMinutes'] = $preparingMinutes;
					$order['deliveryBoy'] = $DBName;
					$order['deliveryBoyContact'] = $DBContact; 
					$order['noofItems'] = $srno;
					$order['particulars'] = $particulars;

					$ordersArray[] = $order;
			} 
			$data["ordersData"] = $ordersArray;
			$response = [
				'status' => 200,
				'message' =>'Orders found',
                'result' =>$data				
			  ];
			return response()->json($response, 200); 	
		}
        else{
			 $response = [
							'status' => 300,
							'message' =>'Orders not found.'
						];
			return response()->json($response, 200);
			
		}
	}
	
	public function getOrderListRestaurantByStatus(Request $r){
		$dataValidate=$r->validate([
		    'restaurantCode'=>'required',
			'status'=>'nullable',
         ]);
		$ip = $_SERVER['REMOTE_ADDR'];
		$code=$dataValidate['restaurantCode'];
		$ordersArray=array();
		$result = RestaurantOrder::select("restaurantordermaster.*","clientmaster.name","clientmaster.mobile","restaurantorderstatusmaster.statusName")
					->join("clientmaster", "clientmaster.code", "=", "restaurantordermaster.clientCode")
					->join("paymentstatusmaster", "paymentstatusmaster.statusSName", "=", "restaurantordermaster.paymentStatus")
					->join("restaurantorderstatusmaster", "restaurantorderstatusmaster.statusSName", "=", "restaurantordermaster.orderStatus")
					->join("usermaster", "usermaster.code", "=", "restaurantordermaster.deliveryBoyCode")
					->where('restaurantordermaster.restaurantCode',$code)
					->whereRaw('(restaurantordermaster.deliveryBoyCode !="" and restaurantordermaster.deliveryBoyCode is not null)')
					->whereRaw('(restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null)');
		if (isset($dataValidate["status"]) && $dataValidate["status"] != "") {
			
                $status = explode(",",$dataValidate["status"]);
                $resResult=$result->whereIn('restaurantordermaster.orderStatus', $status)
						->orderBy('restaurantordermaster.id', 'desc')
						->get(); 				
        }
        else
        {
			   //$resResult=$result->whereIn('restaurantordermaster.orderStatus', ['PND','PLC','CAN','DEL','RJT'])
			   $resResult=$result->whereNotIn('restaurantordermaster.orderStatus', ['PND'])
						->orderBy('restaurantordermaster.id', 'desc')
						->get();
        }
	$resultaddOns="";
	    if(!empty($resResult)){
		foreach ($resResult as $row) {
				$orderCode = $row->code;
				$dbCode = $row->deliveryBoyCode;
				$orderDate = date('Y-m-d h:i:s',strtotime($row->addDate));
				$orderAcceptDateTime = $row->statusTime;
				$preparingMinutes = $row->preparingMinutes;
				$DBName = "";
				$DBContact = "";
				$dBoy = Users::select("usermaster.*")
						->where("usermaster.code",$dbCode)
						->first();
				if(!empty($dBoy)){
					$DBName=$dBoy->name;
					$DBContact=$dBoy->mobile;
				}
				//empty order array
				$particulars = $order = array();
				$resOrders = RestaurantOrderLineEntry::select("restaurantorderlineentries.*","restaurantitemmaster.itemName","restaurantitemmaster.salePrice","restaurantitemmaster.itemPhoto","restaurantitemmaster.restaurantCode")
							->join("restaurantitemmaster", "restaurantitemmaster.code", "=", "restaurantorderlineentries.restaurantItemCode")
							->where('restaurantorderlineentries.isActive',1)
							->where('restaurantorderlineentries.orderCode',$orderCode) 
							->orderBy('restaurantorderlineentries.id','desc')
							->get();
				$srno = 0;
				$i=0;
				
				if (!empty($resOrders)){
					foreach ($resOrders as $res) {
						$itemPhotoCheck = $res->itemPhoto;
						$itemPhoto = "";
						if(!empty($itemPhotoCheck)){
							 $path=env('IMG_URL').'uploads/restaurant/restaurantitemimage/'.$r->restaurantCode.'/'.$itemPhotoCheck;
							if (file_exists($path)) {
								$itemPhoto=$path; 
							}
						}
						/*$savedaddonsCodes=[];
						if($res->addonsCode!='' && $res->addonsCode!=NULL){
						   $res->addonsCode = rtrim($res->addonsCode,', ');
						   $savedaddonsCodes = explode(', ',$res->addonsCode);	
						   foreach($savedaddonsCodes as $addon){ 
								$joinType1 = array('customizedcategory' => 'inner');
								$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
								$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
								$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),"","","",$joinType1);
							  
								if($getAddonDetails && count($getAddonDetails)>0){
								    $resultaddOns .= $getAddonDetails[0]->subCategoryTitle.",";
								}						
							}
						}*/

						$particulars[] = array("itemName" => $res->itemName, "itemPhoto" => $itemPhoto, "quantity" => $res->quantity, "pricewithQty" => $res->priceWithQuantity,"addons" => $res->addons); 
						$srno++;	
					} 
				}
				
				//assignOrdeValues
					$order['orderCode'] = $row->code;
					$order['clientName'] = $row->name;
					$order['orderStatus'] = $row->orderStatus;
					$order['orderStatusName'] = $row->statusName;
					$order['coupanCode'] = $row->coupanCode;
					$order['discount'] = $row->discount;
					$order['tax'] = $row->tax;
					$order['totalPackgingCharges'] = $row->totalPackgingCharges;
					$order['shippingCharges'] = $row->shippingCharges;
					
					$amount = $row->grandTotal;
					$grandTotal = round($amount * (18/100));
					$amount = ($amount) - $row->shippingCharges;
					$order['totalAmount'] = $row->grandTotal;
					$order['actualAmount'] = $row->subTotal;
					$order['orderDate'] = $orderDate;
					$order['prepareDateTime'] = $orderAcceptDateTime;
					$order['preparingMinutes'] = $preparingMinutes;
					$order['deliveryBoy'] = $DBName;
					$order['deliveryBoyContact'] = $DBContact; 
					$order['noofItems'] = $srno;
					$order['particulars'] = $particulars;

					$ordersArray[] = $order;
			} 
			$data["ordersData"] = $ordersArray;
			$response = [
				'status' => 200,
				'message' =>'Orders found',
                'result' =>$data				
			  ];
			return response()->json($response, 200); 	
		
		}else{
			 $response = [
							'status' => 300,
							'message' =>'Orders not found.'
						];
			return response()->json($response, 200);
			
		}	 
	}
	
	 public function getOrderDetails(Request $r)
    {
        $dataValidate=$r->validate([
			'restaurantCode' => 'required',  
			'orderCode'=> 'required',
        ]);
		//DB::enableQueryLog();
        $restaurantCode = $r->restaurantCode;
        $orderCode = $r->orderCode;
		$tableName = "restaurantordermaster";
		$orderColumns = array(DB::raw("restaurantordermaster.code as orderCode,clientmaster.name as clientName,usermaster.mobile as dBoyContactNumber,restaurantordermaster.deliveryBoyCode,usermaster.name as dBoyName,usermaster.latitude as dBoyLat,usermaster.longitude as dBoyLong,restaurantordermaster.restaurantCode,restaurantordermaster.totalPackgingCharges,restaurantordermaster.packagingType,restaurantordermaster.addressType,restaurantordermaster.tax,restaurantordermaster.discount,restaurantordermaster.subTotal,restaurantordermaster.shippingCharges as deliveryCharges,restaurantordermaster.usedPoints as useCutlets,restaurantordermaster.paymentmode,restaurantordermaster.address,restaurantordermaster.grandTotal as orderTotalPrice,restaurantordermaster.addDate as orderDate, restaurantorderstatusmaster.statusName as orderStatus, paymentstatusmaster.statusName as paymentStatus,restaurant.entityName,restaurant.address as pickUpAddress,restaurant.latitude as sourceLat,restaurant.longitude as sourceLong,restaurant.cityCode,restaurant.addressCode,citymaster.cityName,customaddressmaster.place,restaurantordermaster.latitude as destiLat,restaurantordermaster.longitude as destiLong,bookorderstatuslineentries.statusTime"));
		$cond = array('restaurantordermaster.code' => array("=",$orderCode),'restaurantordermaster.restaurantCode' => array("=",$restaurantCode),"restaurantordermaster.isActive"=>array("=",1));
		$orderBy = array("restaurantordermaster.id" => 'DESC');
		$join = array('clientmaster'=>array('clientmaster.code','restaurantordermaster.clientCode'),'usermaster'=>array('restaurantordermaster.deliveryBoyCode','usermaster.code'),'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus','restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus','paymentstatusmaster.statusSName'), "restaurant" => array("restaurantordermaster.restaurantCode","restaurant.code"), "citymaster" => array("restaurant.cityCode","citymaster.code"), "customaddressmaster" => array("restaurant.addressCode","customaddressmaster.code"),"bookorderstatuslineentries" => array("restaurantordermaster.orderStatus","bookorderstatuslineentries.statusLine"));
		$joinType = array('clientmaster'=>'inner','usermaster'=>'left','restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner', "restaurant" => "inner", "citymaster" => "left", "customaddressmaster" => "left","bookorderstatuslineentries" => "left");
		$extracondition=" bookorderstatuslineentries.orderCode = restaurantordermaster.code";
		$resultQuery = $this->model->selectQuery($orderColumns, $tableName, $join,$cond, $orderBy,array(), "", "",$extracondition,$joinType);
		//$imageArray = array();
		//$query_1 = DB::getQueryLog();
		if ($resultQuery && count($resultQuery)>0) { 
			$totalOrders = sizeof($resultQuery);
			//$clientOrderList = json_encode($resultQuery);
			foreach($resultQuery as $res){
				$res->statusTime=date('d-m-Y h:i A',strtotime($res->statusTime));
				$addonArray=$resultAddonArray=[];
				$linetableName = "restaurantorderlineentries";
				$lineorderColumns = array(DB::raw("restaurantorderlineentries.restaurantItemCode,restaurantorderlineentries.quantity,restaurantorderlineentries.priceWithQuantity as priceWithQuantity,restaurantorderlineentries.addons,restaurantorderlineentries.addonsCode,restaurantitemmaster.itemName,restaurantorderlineentries.itemPackagingCharges,restaurantitemmaster.itemPhoto"));
				$linecond = array("restaurantorderlineentries.orderCode" => array("=",$res->orderCode));
				$lineorderBy = array('restaurantorderlineentries' . ".id" => 'ASC');
				$linejoin = array('restaurantitemmaster' => array('restaurantorderlineentries.restaurantItemCode','restaurantitemmaster.code'));
				$linejoinType = array('restaurantitemmaster' => 'inner');
				$orderProductRes = $this->model->selectQuery($lineorderColumns, $linetableName,$linejoin, $linecond, $lineorderBy,array(),"","","", $linejoinType);
				if ($orderProductRes) {
					foreach($orderProductRes as $subItem){
						$addonArray=$subItem;
						$resultArr=[];
						
						/*if($subItem->addonsCode!='' && $subItem->addonsCode!=NULL){
							$subItem->addonsCode = rtrim($subItem->addonsCode,', ');
							$savedaddonsCodes = explode(', ',$subItem->addonsCode);
							foreach($savedaddonsCodes as $addon){
								$categoryArr=[];
								$joinType1 = array('customizedcategory' => 'inner');
								$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
								$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
								$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),"","","",$joinType1);
								if($getAddonDetails && count($getAddonDetails)>0){
									$categoryArr = $getAddonDetails[0];
								}
								$resultArr[]=$categoryArr; 
							}
						}
						$addonArray->addonsDetails=$resultArr;
						$resultAddonArray[]= $addonArray;
						$addonArray->itemImage=$itemPhoto;*/
						$resultAddonArray[]=$addonArray;
					}
				}
				$res->orderedItems= $resultAddonArray; 
			}
			return response()->json(["status" => 200, "totalOrders" => $totalOrders, "result" => $res], 200);
		} else {
			return response()->json(["status" => 300, "message" => "Data not found."], 200);
		}
    }
	
   
   	public function sendNotification($DeviceIdsArr = array(), $title, $message, $orderCode)
	{
		$random = rand(0, 999);
		$dataArr = $notification = array();
		$dataArr['device_id'] = $DeviceIdsArr;
		$dataArr['message'] = $message; 
		$dataArr['title'] = $title;
		$dataArr['order_id'] = $orderCode;
		$dataArr['random_id'] = $random;
		$dataArr['type'] = 'order';
		$notification['device_id'] = $DeviceIdsArr;
		$notification['message'] = $message; 
		$notification['title'] = $title;
		$notification['order_id'] = $orderCode;
		$notification['random_id'] = $random;
		$notification['type'] = 'order';
		$noti = new Notificationlibv_3;
        $result = $noti->sendNotification($dataArr, $notification);
		//print_r($result);
	}
	
	
	public function getOrderBillingList(Request $r){
		$dataValidate=$r->validate([
		    'restaurantCode'=>'required',
			'selectedDate'=>'nullable',
         ]);
		$ip = $_SERVER['REMOTE_ADDR'];
		$code=$dataValidate['restaurantCode'];
		$date=$dataValidate['selectedDate'];
		$ordersArray=array();
	    DB::enableQueryLog();
	    $query="SELECT restaurantordermaster.code, restaurantordermaster.grandTotal,restaurantordermaster.discount,restaurantordermaster.shippingCharges,restaurantordermaster.tax,restaurantordermaster.totalPackgingCharges,round((((restaurantordermaster.grandTotal-restaurantordermaster.shippingCharges)*restaurant.commission)/100),2) as adminComission,round(((restaurantordermaster.grandTotal-restaurantordermaster.shippingCharges)-(((restaurantordermaster.grandTotal-restaurantordermaster.shippingCharges)*restaurant.commission)/100)),2) as payableAmount,restaurantordermaster.addDate FROM `restaurantordermaster` inner join restaurant ON restaurantordermaster.restaurantCode=restaurant.code where Date(restaurantordermaster.addDate)='".$date."' AND restaurantordermaster.restaurantCode='".$code."' AND restaurantordermaster.orderStatus='DEL'";
		$result = DB::select($query);
		$total=0;
		if(!empty($result) && count($result)>0){
		    foreach($result as $row){
		        $total+=$row->payableAmount;
		    }
			$response = [
				'status' => 200,
				'message' =>'Billing details',
				'totalPayable'=> round(number_format($total,2,".","")),
                'result' =>$result				
			  ];
			return response()->json($response, 200); 	
		
		}else{
			 $response = [
							'status' => 300,
							'message' =>'Orders not found.',
							'totalPayable'=>$total,
						];
			return response()->json($response, 200);
		}
	}
	
	public function testNotification(Request $r)
	{
	    $dataValidate=$r->validate([
		    'firebase_id'=>'required',
			'type'=>'nullable',
         ]);
		$DeviceIdsArr =array();
		$DeviceIdsArr[] = $dataValidate['firebase_id'];
		$message = 'Test ringing notification ';
		$title = 'Check Notification';
		$orderCode="ORDER_1";
		$type=trim($dataValidate['type'])!="" ?"forDB": "";
		$res=$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode,$type);
		$response = [
							'status' => 200,
							'message' =>$res,
							'data'=>$dataValidate,
						];
		return response()->json($response, 200);
	}
}
