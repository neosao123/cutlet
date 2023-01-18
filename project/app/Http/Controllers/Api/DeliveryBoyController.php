<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use App\Models\Users;
use App\Models\DeliveryBoyActiveOrder;
use App\Models\RestaurantWallet;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 
use App\Classes\Notificationlibv_3; 
use Carbon\Carbon;
use DB;
use App\Classes\FirestoreActions;
use App\Models\RestaurantOrder;

class DeliveryBoyController extends Controller
{
    public function __construct(GlobalModel $model,ApiModel $apimodel)
	{
		$this->model = $model;
		$this->apimodel = $apimodel;
	}
	
	public function resetpassword(Request $r)
    {
        $dataValidate=$r->validate([
            'mobile' => 'required|digits:10',
        ]);
		$mobile= $dataValidate['mobile'];
		$cond = array('usermaster.mobile' => array('=',$mobile),'usermaster.role' => array('=','DBOY'), 'usermaster.isActive' => array('=',1));
        $member = $this->model->selectQuery(array('usermaster.code'),'usermaster',array(),$cond);
        if ($member && count($member)>0) {
			foreach($member as $mem){
				$userCode = $mem->code;
				$checkRequest = $this->model->selectQuery(array('resetpassword.id'),'resetpassword',array(),array('resetpassword.userCode'=>array('=',$userCode)));
				if($checkRequest && count($checkRequest)==0){
					$insertArr = array(
						"userCode" => $userCode,
						"isActive" => 1
					);
				$currentId = DB::table('resetpassword')->insertGetId($insertArr);
					if ($currentId > 0) {
						return response()->json(["status" => 200, "message" => " Reset password Request sent. default password is 123456 ... try after admin reset. Change your password after login."], 200);
					} else {
						return response()->json(["status" => 300, "message" => " Opps...! Something went wrong please try again."], 200);
					}
				}else{
					return response()->json(["status" => 300, "message" => "Request already sent...Please wait for reset"], 200);
				}
			}
        } else {
            return response()->json(["status" => 300, "message" => "Invalid delivery boy"], 200);
        }
	}
	
	public function loginProcess(Request $r)
	{
		$dataValidate=$r->validate([
            'mobile' => 'required|digits:10',
            'password' => 'required',
        ]);
		$user = Users::where('mobile',$dataValidate['mobile'])->where('role','DBOY')->where('isActive',1)->first();
		if(! $user ||  ! Hash::check($dataValidate['password'], $user->password)) {
            return response()->json(["status"=>300,"message"=>"Invalid Mobile number or password"], 200);
        }else{
			 DB::enableQueryLog();
			$data = array(
				"mobile" => $r->mobile,"isActive"=>1 
			);
			$resultData = $this->apimodel->read_Delivery_information($data);
			$userCode = $resultData->code;
			$loginStatus = 0;  
			$res = $this->model->selectQuery(array('deliveryBoyActiveOrder.loginStatus'), 'deliveryBoyActiveOrder', array(),array('deliveryBoyActiveOrder.deliveryBoyCode' => array("=",$userCode)));
			//$query_1 = DB::getQueryLog();
			//print_r($query_1);
			if (!empty($res)) {
				foreach($res as $rest){
				    $loginStatus = $rest->loginStatus;
				}
				if($loginStatus==0){
					$this->model->doEditWithField(array("deliveryBoyActiveOrder.loginStatus"=>array("=",1)),"deliveryBoyActiveOrder",'deliveryBoyCode',array("=",$userCode));
				    $loginStatus=1;
				}
			}else {
				$dataDbActive['deliveryBoyCode'] = $userCode;
				$dataDbActive['orderCount'] = 0;
				$dataDbActive['loginStatus'] = 1;
				$dataDbActive['isActive'] = 1;
				$resultDbActive = $this->model->addNew($dataDbActive, 'deliveryBoyActiveOrder', 'DBA');
			}
			$path='';
			if($resultData->profilePhoto!='' && $resultData->profilePhoto!=NULL){
				$path = url('uploads/profile/'.$resultData->profilePhoto);
			}
			$token = $user->createToken('CutletDeliveryApp')->plainTextToken;
			$resultArray = array(
				'code' => $resultData->code,
				'name' => $resultData->name,
				'role' => $resultData->role,
				'userEmail' => $resultData->userEmail,
				'profilePhoto' =>$path,
				'isActive' => $resultData->isActive,
				'contactNumber' => $resultData->mobile,
				'loginStatus' => $loginStatus,
				'token'=>$token
			);
			$result['userData'] = $resultArray;
			return response()->json(["status" => 200,"message" => "Login Successfully...", "result" => $result], 200);
		}
	} //end login  Process

		//login process
	public function getProfileDetails(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		$resultData = $this->apimodel->read_Delivery_information(array("code"=>$r->code));
		if($resultData){
			$userCode = $resultData->code;
			$isActive = $resultData->isActive;
			if($isActive==1){
				$loginStatus = 0;  
				$res = $this->model->selectQuery(array('deliveryBoyActiveOrder.loginStatus'), 'deliveryBoyActiveOrder',array(), array('deliveryBoyActiveOrder.deliveryBoyCode' => array("=",$userCode)));
				if (!empty($res)) {
					foreach($res as $re){
						$loginStatus = $re->loginStatus;
					}
				}
			$path='';
			if($resultData->profilePhoto!='' && $resultData->profilePhoto!=NULL){
				$path = url('uploads/profile/'.$resultData->profilePhoto);
			}
				$resultArray = array(
					'code' => $resultData->code,
					'name' => $resultData->name,
					'role' => $resultData->role,
					'userEmail' => $resultData->userEmail,
					'profilePhoto' => $path,
					'isActive' => $resultData->isActive,
					'contactNumber' => $resultData->mobile,
					'loginStatus' => $loginStatus
				);
				$result['userData'] = $resultArray;
				return response()->json(["status" => 200, "result" => $result], 200);
			}else{
				return response()->json(["status" => 300, "result" => 'You are not an active member'], 200);
			}
		} else {
			return response()->json(["status" => 300, "message" => "No data Found"], 200);
		}
	} 

	public function deliveryLoginStatusChange(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
            'status' => 'required',
        ]);
		if ($r->status == 1) {
			$dataupdate['loginStatus'] = 1;
		} else {
			$dataupdate['loginStatus'] = 0;
		}
		$userCode = $r->code;
		$dataupdate['editID'] = $userCode;
		$dataupdate['editIP'] = $_SERVER['REMOTE_ADDR'];
		$res = $this->model->doEditWithField($dataupdate, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $userCode);
		if ($res) {
			return response()->json(["status" => 200, "message" => "Status updated successfully"], 200);
		} else {
			return response()->json(["status" => 300, "message" => " Failed to update status."], 200);
		}
	}

	//profile update 
	public function deliveryProfileUpdate(Request $r){
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		$resultData = $this->model->selectDataByCode('usermaster',$r->code);
		if ($resultData) {
			$data=array();
			if (isset($r->email) && $r->email!="") {
				$data['userEmail'] = $r->email;
			}
			if (isset($r->mobile) && $r->mobile!="") {
				$data['mobile'] = $r->mobile;
			}
			if (isset($r->name) && $r->name!="") {
				$data['name'] = $r->name;
			}
			$resultMaster = $this->model->doEdit($data, 'usermaster', $r->code);
			return response()->json(["status" => 200, "message" => "Your profile has been updated successfully."], 200);
		} else {
			return response()->json(["status" => 300, "message" => "Failed to update profile"], 200);
		}
	}

	//profile pic upload
	public function profilePicUpload(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
			echo $_FILES['file']['name'];
			$result= $this->model->selectDataByCode('usermaster',$r->code);
			if($result){
				$profilePhoto = "";
				$uploadRootDir = 'uploads/';
				$uploadDir = 'uploads/profile/';
				if (!empty($_FILES['file']['name'])) {
					$tmpFile = $_FILES['file']['tmp_name'];
					$filename = $uploadDir . '/' . $r->code . '.jpg';
					move_uploaded_file($tmpFile, $filename);
					if (file_exists($filename)) {
						$profilePhoto = $r->code . '.jpg';
					}
				}
				$subData = array(
					'profilePhoto' => $profilePhoto
				);
				$filedoc = $this->model->doEdit($subData, 'usermaster', $r->code);
				return response()->json(["status" => 200, "message" => "Profile photo uploaded successfully."], 200);
			}else{
				return response()->json(["status" => 300, "message" => "User not exists"], 200);
			}
		}
	}


	//Start update password
	public function updatePassword(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
            'oldPassword' => 'required',
            'newPassword' => 'required',
        ]);
		$password=$dataValidate['oldPassword'];
		$newpassword=$dataValidate['newPassword'];
		$deliveryBCode=$dataValidate['code'];
		$result = Users::where(['code' => $deliveryBCode])->first();
		if(empty($result)){
			 $response = [
					'status' => 300,
					'message' =>'Delivery Boy is not register.',	
			  ];
			 return response()->json($response, 200);
		}else{
			if(Hash::check($password,$result->password)){
				$updatePassword=Hash::make($newpassword);
				$updateResult=Users::where('code',$deliveryBCode)
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
	
	//update orders status
	public function updateOrderStatus(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$dataValidate=$r->validate([
            'code' => 'required',
            'orderStatus' => 'required',
            'orderCode' => 'required',
        ]);
		$orderStatus = $r->orderStatus;
		$orderCode = $r->orderCode;
		$code =	$addID =  $r->code;
		if ($orderStatus == "PLC") {
			$ordData['orderStatus'] = 'PLC';
			$reason = "Deliveryboy accepted order";
		} 
		else if ($orderStatus == "REL") {
			if (isset($r->reason)) {
				if ($r->reason != "") {
					$reason = $r->reason;
					$ordData['orderStatus'] = 'PND';
					$ordData['deliveryBoyCode'] = null;
					$dataBookLine['reason'] = $r->reason;

					$data['reason'] = $r->reason;
					$data['orderCode'] = $r->orderCode;
					$data['deliveryBoyCode'] = $code;
					$data['actionDate'] = date('Y-m-d H:i:s');
					$data['orderStatus'] = $orderStatus;
					$data['isActive'] = 1;
					$result = $this->model->addNew($data, 'deliveryboystatuslines', 'DBSL');
					$dataUpCnt['orderCount'] = 0;
					$dataUpCnt['orderCode'] = null;
					$dataUpCnt['orderType'] = null;
					$delbActiveOrder = $this->model->doEditWithField($dataUpCnt, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $r->code);

					$FoodOrder = $this->model->selectQuery(array("restaurantordermaster.code"), "restaurantordermaster", array(),array("restaurantordermaster.code" => array("=",$orderCode)));
					if ($FoodOrder) {
						$orderData['deliveryBoyCode'] = null;
						$orderData['orderStatus'] = 'PND';
						$delbActiveOrder = $this->model->doEditWithField($orderData, 'restaurantordermaster', 'code', $orderCode);
					}
				} else {
					return response()->json(["status" => 300, "message" => "Please provide valid reason to release this order."], 200);
				}
			} else {
				return response()->json(["status" => 300, "message" => "Please provide reason to release this order."], 200);
			}
		} 
		else if ($orderStatus == "PUP") {
			$ordData['orderStatus'] = 'PUP';
			$reason = "Order has been picked-up";
		} 
		else if ($orderStatus == 'RCH') {
			$ordData['orderStatus'] = 'RCH';
			$reason = "Delivery person reached near the restaurant";
		} 
		else if ($orderStatus == "DEL") {
			$ordData['orderStatus'] = 'DEL';
			$resultQuery = $this->model->selectQuery(array("clientmaster.name","restaurantordermaster.*"), "restaurantordermaster", array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode')),array("restaurantordermaster.code" => array("=",$orderCode)));
			$clientname='';
			//$resultQuery = $this->model->selectQuery($orderColumns, $tableName, $cond, array(), $join, $joinType, array(), '', '', array(), '');
			if(!empty($resultQuery)){
				$clientname = $resultQuery[0]->name;
			}
			$reason = $clientname.' ,order ('.$orderCode.') has been successfully delivered to the given address.';
			
			/*$data=RestaurantWallet::select('wallettransactions.*')
	            ->where(['orderCode' => $orderCode])
				->first();		
			if(!empty($data)){
			    $updateResult=RestaurantWallet::where('orderCode',$orderCode)
							->update(['status' => 'success','updated_at'=>date("Y-m-d h:i:s")]);
				if($updateResult){
					$walletPoint=$data->point;
					$clientCode=$data->clientCode;
					$clientId=Customer::select('clientmaster.*')
							->where(['code' => $clientCode])
							->first();
                    if($clientId){
						$points=$clientId->walletPoints+$walletPoint;
						$updateResult=Customer::where('code',$clientCode)
							->update(['walletPoints' => $points]);
					}						
				}
			}*/  
			
			$restDelv['orderCode'] = null;
			$restDelv['orderType'] = null;
			$restDelv['editID'] = $addID; 
			$restDelv['editIP'] = $ip;
			$restDelv['orderCount'] = 0;
			$delbActiveOrder = $this->model->doEditWithField($restDelv, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $r->code);
		  
		    $orderData['paymentStatus']='PID'; 
			$resData = $this->model->doEditWithField($orderData, 'restaurantordermaster', 'code', $orderCode);		
			
		} else {
			return response()->json(["status" => 300, "message" => "Invalid Order Status"], 200);
		}
		$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=",$orderStatus)));
		if ($order_status && count($order_status)>0) {
			$order_status_record = $order_status[0];
			$statusTitle = $order_status_record->messageTitle;
			#replace $ template in title 
			$statusDescription = $order_status_record->messageDescription;
			$statusDescription = str_replace("$", $orderCode, $statusDescription);
			$dataBookLine = array(
				"orderCode" => $orderCode,
				"statusPutCode" => $r->code,
				"statusLine" => $orderStatus,
				"statusTime" => date("Y-m-d H:i:s"),
				"statusTitle" => $statusTitle,
				"statusDescription" => $statusDescription,
				"reason" => $reason,
				"isActive" => 1
			);
			$bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
		if ($bookLineResult) {
			$cond2['restaurantordermaster.code'] = array("=",$r->orderCode);
			$res = $this->model->selectQuery(array("restaurantordermaster.*"), "restaurantordermaster",array(), $cond2);
			if (!empty($res)) {
				foreach($res as $resp){
					$vendorCode  = $resp->restaurantCode;
					$clientCode  = $resp->clientCode;
					$deliveryBoyCode  = $resp->deliveryBoyCode;
					$grandTotal  = $resp->grandTotal;
					$commissionPer=0;
					$commissionperSetting = $this->model->selectQuery(array('restaurant.commission'), 'restaurant', array(),array('restaurant.code' => array("=",$vendorCode)));
					if(!empty($commissionperSetting)){
						$commissionPer  = $commissionperSetting[0]->commission;
					}
					$shippingCharges  = $resp->shippingCharges;
					$dataUpCnt['orderCode'] = $r->orderCode;
					$dataUpCnt['orderType'] = 'food';
					$dataupCnt['deliveryBoyCode'] = $code;
					$dataupCnt['addID'] = $r->code;
					$dataupCnt['addIP'] = $ip;
					$dataupCnt['isActive'] = 1; 
					if ($orderStatus == "DEL") {
						//delivery boy commission
						$amountUpTo=$cumissionPercentage=$baseCumission=0;
						$amountUpToSetting = $this->model->selectQuery(array(DB::raw('ifnull(settings.settingValue,0) as settingValue')), 'settings', array(),array('settings.code' => array("=","SET_2"),'settings.isActive' => array("=",1)));
						if($amountUpToSetting && count($amountUpToSetting)>0)  $amountUpTo=$amountUpToSetting[0]->settingValue;
						
						$commissionPerSetting = $this->model->selectQuery(array(DB::raw('ifnull(settings.settingValue,0) as settingValue')), 'settings', array(),array('settings.code' => array("=","SET_3"),'settings.isActive' => array("=",1)));
						if($commissionPerSetting) $cumissionPercentage=$commissionPerSetting[0]->settingValue;
	
						$baseCommissionSetting = $this->model->selectQuery(array(DB::raw('ifnull(settings.settingValue,0) as settingValue')), 'settings', array(),array('settings.code' => array("=","SET_4"),'settings.isActive' => array("=",1)));
						if($baseCommissionSetting) $baseCumission=$baseCommissionSetting[0]->settingValue;
						
						if ($grandTotal > $amountUpTo) {
							$percnVal = round($grandTotal * ($cumissionPercentage / 100));
							$delCom = $percnVal;
						} else {
							$delCom = $baseCumission;
						}
						$this->model->deleteForeverFromField('orderCode',$orderCode,'deliveryboyearncommission');
						$dbcAdd['commissionAmount'] = $delCom;
						$dbcAdd['orderAmount'] = $grandTotal;
						$dbcAdd['commissionType'] = 'regular';
						$dbcAdd['orderCode'] = $orderCode;
						$dbcAdd['deliveryBoyCode'] = $code;
						$dbcAdd['addDate'] = date("Y-m-d H:i:s");
						$dbcAdd['isActive'] = 1;
						$delboyCommission = $this->model->addNewWithYear($dbcAdd, 'deliveryboyearncommission', 'DBEC');
				
						//restaurant commission
						$vendorComissionPercentage=0;
						$subtotal=($grandTotal-$shippingCharges);
						$vcomission = round($subtotal * ($commissionPer / 100));
						$vendorAmount=($subtotal-$vcomission);
						$vcData['comissionAmount'] = $vcomission;
						$vcData['restaurantCode'] = $vendorCode; 
						$vcData['orderCode'] = $orderCode;
						$vcData['comissionPercentage'] = $commissionPer;
						//$vcData['comissionPercentage'] = $vendorComissionPercentage;
						$vcData['subTotal'] = $subtotal;
						$vcData['restaurantAmount'] = $vendorAmount;
						$vcData['grandTotal'] = $grandTotal;
						$vcData['isActive'] = 1;
						$vcData['commissionType'] = 'regular';
						$vcData['addDate'] = date("Y-m-d H:i:s");
						
						
						$delboyCommission = $this->model->addNewWithYear($vcData, 'restaurantordercommission', 'VNDC');
					}
					$orderUpdate = $this->model->doEdit($ordData, 'restaurantordermaster', $r->orderCode);
					if ($orderUpdate) {
						sleep(1);
						if ($vendorCode != "") {
							$firestoreAction = new FirestoreActions();
                            $firestoreAction->update_refresh_code($vendorCode);
							$cond3['restaurant.code'] = array("=",$vendorCode);
							$restaurant = $this->model->selectQuery(array("restaurant.firebaseId"), "restaurant", array(),$cond3);
							if ($restaurant) {
								foreach($restaurant as $re){
									$vendorfirebaseId = $re->firebaseId;
								}
								if ($orderStatus == "PLC") {
									$title = "New Order";
									$message = "New order has been placed for order no. " . $orderCode;
									//vendor notifications 
									if ($vendorfirebaseId!='') {
										$DeviceIdsArr[] = $vendorfirebaseId;
										$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
									}
								} else if ($orderStatus == "PUP") {
									$title = "Order - " . $orderCode;
									$message = "Your order has been picked up!";
									//notifications to client										
									$dvCondition['clientCode'] = array("=",$clientCode);
									$clientDevices = $this->model->selectQuery(array("clientdevicedetails.firebaseId"), "clientdevicedetails",array(), $dvCondition);
									if ($clientDevices) {
										foreach ($clientDevices as $key) {
											$DeviceIdsArr[] = $key->firebaseId;
											$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
										}
									}
								} else if ($orderStatus == "RCH") {
									$title = "Order - " . $orderCode;
									$message = "Delivery boy has reached near the restaurant...";
									//vendor notifications
									if ($vendorfirebaseId) {
										$DeviceIdsArr[] = $vendorfirebaseId;
										$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
									}
									//client notifications										
									$dvCondition['clientCode'] = array("=",$clientCode);
									$clientDevices = $this->model->selectQuery(array("clientdevicedetails.firebaseId"), "clientdevicedetails",array(), $dvCondition);
									if ($clientDevices) {
										foreach ($clientDevices as $key) {
											$DeviceIdsArr[] = $key->firebaseId;
											$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
										}
									}
								} else if ($orderStatus == "DEL") {
									DB::enableQueryLog();
									$title = "Order - " . $orderCode;
									$message = "The order has been delivered successfully!";
									//vendor notifications
									if ($vendorfirebaseId) {
										$DeviceIdsArr[] = $vendorfirebaseId;
										$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
									}
									//client notifications										
									$dvCondition['clientCode'] = array("=",$clientCode);
									$clientDevices = $this->model->selectQuery(array("clientdevicedetails.firebaseId"), "clientdevicedetails",array(), $dvCondition);
									
									//$query_1 = DB::getQueryLog();
                                    //print_r($query_1);
									if ($clientDevices) {
										foreach ($clientDevices as $key) {
											$DeviceIdsArr[] = $key->firebaseId;
											$this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
											$DeviceIdsArr=array();
										}
									}
								}
							}
						}
						return response()->json(["status" => 200, "message" => "Order Status updated succesfully..."], 200);
					} else {
						return response()->json(["status" => 300, "message" => "Failed to update order status"], 200);
					}
				}
			}else{
				return response()->json(["status" => 300, "message" => "No Order Found"], 200);
			}
		}
		} else {
			return response()->json(["status" => 300, "message" => "Server seems busy! Please try later"], 200);
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
	
	}
	
	public function updateLatLong(Request $r){
		$dataValidate=$r->validate([
            'code' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
			$dataMaster = [
				"latitude" => $r->latitude,
				"longitude" => $r->longitude,
			];
			$resultMaster = $this->model->doEdit($dataMaster, 'usermaster', $r->code);
			if ($resultMaster) {
				return response()->json(["status" => 200, "message" => "Record updated successfully"], 200);
			} else {
				return response()->json(["status" => 300, "message" => " Failed to update record."], 200);
			}
	}
	
	public function testNotification(){
        $this->sendNotification(array('cl68thmjS2ms-F2tf5MEHQ:APA91bGmYYAvM_JccSu-O54RPRDQNBhKSr_cFsnes-9VR6sRzqpiqDD9gX1PSQr_LsrPj94AHZlOYDqV_TITVe5g9nqatfPUXbnX7hYJlJE313jgttNpRKW4-dYbrCsEzh8hmH_0GohE'),'Sample','Test Cutlet Notification','ORDER_15633');
	}
	
	public function getOrdersByStatus(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
            'orderStatus' => 'required'
        ]);
		$limit='';
		DB::enableQueryLog();
		$clientOrderLista  = array();
		$orderStatus = $dataValidate['orderStatus'];
		
		$code = $dataValidate['code'];
		$orderColumns = array("restaurantordermaster.code as orderCode","restaurantordermaster.tax","restaurantordermaster.totalPackgingCharges as totalPackagingCharges","restaurantordermaster.discount","restaurantordermaster.subTotal as orderPrice","restaurantordermaster.restaurantCode","restaurantordermaster.shippingCharges as deliveryCharges","restaurantordermaster.paymentmode","restaurantordermaster.address", "restaurantordermaster.grandTotal as orderTotalPrice","restaurantordermaster.addDate as orderDate","restaurantordermaster.phone", "restaurantordermaster.latitude", "restaurantordermaster.longitude","restaurantorderstatusmaster.statusSName as orderStatus", "paymentstatusmaster.statusName as paymentStatus", "clientmaster.code as clientCode", "clientmaster.name","restaurantordermaster.usedPoints");
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus','restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus','paymentstatusmaster.statusSName'));
		$joinType = array('clientmaster' => 'left', 'restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner');
		$cond = array("restaurantordermaster.deliveryBoyCode" => array("=",$code),"restaurantordermaster.isActive" => array("=",1)); 
		$orderBy = array('restaurantordermaster.id' => 'DESC');
	    $status_condition="'".$orderStatus."'";
		switch ($orderStatus) {
			case 'PND':
					$limit = 1;
					break;
				case 'PLC':
					$limit = 1;
					break;
				case 'PRE':
					$limit = 1;
					$status_condition="'PRE','RCH'";
					break;
				case 'RFP':
					$limit = 1;
					break;
				case 'PUP':
					$limit = 1;
					break;
				case 'RCH':
					$limit = 1;
					$status_condition="'PRE','RCH'";
					break;
				case 'DEL':
					$limit = "";
					break;
				case 'RJT':
					$limit = "";
					break;
				case 'CAN':
					$limit = "";
					break;
		}
		
		$extraCondition = " restaurantordermaster.orderStatus in (" . $status_condition . ")";
		$resultQuery = $this->model->selectQuery($orderColumns, 'restaurantordermaster',$join, $cond, $orderBy,array(), $limit, "", $extraCondition,$joinType);
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
			if ($resultQuery) {
				$clientOrderList = json_decode($resultQuery,true);
				$totalOrders = sizeof($clientOrderList);
				
				for ($i = 0; $i < sizeof($clientOrderList); $i++) {
					$order = $clientOrderList[$i];
					//	return response()->json(["status" => 200, "message" => " Order details", "result" => ], 200);
					$restaurantCode = $clientOrderList[$i]['restaurantCode'];
					$orderCode = $clientOrderList[$i]['orderCode'];
					$venCondition['restaurant.code'] = array("=",$restaurantCode);
					$vendorData = $this->model->selectQuery('restaurant.*', 'restaurant', array(),$venCondition);
					if ($vendorData) {
						$vendor = $vendorData[0];
						$vd['vendorCode'] = $restaurantCode;
						$vd['vendorName'] = $vendor->entityName;
						$vd['address'] = $vendor->address;
						$vd['vendorContact'] = $vendor->ownerContact;
						$vd['latitude'] = $vendor->latitude;
						$vd['longitude'] = $vendor->latitude;
						$path = "noimage";
						if ($vendor->entityImage != "") {
							$path = 'uploads/restaurant/restaurantimage/' . $vendor->entityImage;
							if (file_exists($path)) {
								$path = url($path);
							}
						}
						$vd['vendorImage'] = $path;
						$order['restaurantDetails'] = $vd;
					}
					$linetableName = "restaurantorderlineentries";
					$lineorderColumns = array("restaurantorderlineentries.restaurantItemCode","restaurantorderlineentries.addons","restaurantorderlineentries.addonsCode","restaurantorderlineentries.quantity","restaurantorderlineentries.priceWithQuantity","restaurantorderlineentries.itemPackagingCharges","restaurantitemmaster.restaurantCode","restaurantitemmaster.itemName","restaurantitemmaster.itemPhoto");
					$linecond = array("restaurantorderlineentries.orderCode" =>  array("=",$orderCode));
					$lineorderBy = array("restaurantorderlineentries.id" => 'ASC');
					$linejoin = array('restaurantitemmaster' => array('restaurantorderlineentries.restaurantItemCode','restaurantitemmaster.code'));
					$linejoinType = array('restaurantitemmaster' => 'inner');
					$orderProductRes = $this->model->selectQuery($lineorderColumns, $linetableName,$linejoin, $linecond, $lineorderBy,array(),"","","",  $linejoinType);
				
					if ($orderProductRes) {
						$productPrice = 0;
						$itemsArray = array();
						$orderProductList = json_decode($orderProductRes,true);
						for ($j = 0; $j < sizeof($orderProductList); $j++) {
							$itemAr['vendorItemCode'] = $orderProductList[$j]["restaurantItemCode"];
							$itemAr['itemName'] =  $orderProductList[$j]["itemName"];
							$itemAr['addons'] = $orderProductList[$j]["addons"];
							$itemAr['addonsCode'] = $orderProductList[$j]["addonsCode"];
							$itemAr['quantity'] = $orderProductList[$j]["quantity"];
							$itemAr['priceWithQuantity'] = $orderProductList[$j]["priceWithQuantity"];
							$itemAr['itemPackagingCharges'] = $orderProductList[$j]["itemPackagingCharges"];
							if ($orderProductList[$j]["itemPhoto"] != "") {
								$path = 'partner/uploads/' . $restaurantCode . '/vendoritem/' . $orderProductList[$j]["itemPhoto"];
								if (file_exists($path)) {
									$itemAr['itemPhoto'] = base_url($path);
								} else {
									$itemAr['itemPhoto'] = 'noimage';
								}
							} else {
								$itemAr['itemPhoto'] = 'noimage';
							}
						
							$itemsArray[] = $itemAr;
						}
						$order['totalItems']=sizeof($orderProductList);
						$dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $clientOrderList[$i]['orderDate']);
						$oDt = $dFormat->format('d-m-Y H:i:s');
						$order['orderDate'] = $oDt;
						$order['orderedProduct'] = $itemsArray;
					}
					$order['ratingDetails']=[];
					$orderColumns1 = array("rating.id","clientmaster.name","rating.clientCode","rating.orderCode","rating.deliveryBoyCode","rating.rating","rating.review","rating.addDate","rating.isAccept");
					$condition1=array("rating.orderCode"=>array("=",$orderCode),"rating.deliveryBoyCode"=>array("=",$code));
					$join1=array("clientmaster"=>array("clientmaster.code","rating.clientCode"));
					$extraCondition1 = " (rating.restaurantCode='' or rating.restaurantCode IS NULL)";
					$ratingDetails = $this->model->selectQuery($orderColumns1, 'rating',$join1,$condition1, array(),array(),"","",$extraCondition1);
					if (!empty($ratingDetails) && count($ratingDetails)>0) {
						$rating = $ratingDetails[0];
						$dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $rating->addDate);
						$ratingDate = $dFormat->format('d-m-Y H:i:s');
						$ratingArr['id'] = $rating->id;
						$ratingArr['orderCode'] =  $rating->orderCode;
						$ratingArr['clientCode'] =  $rating->clientCode;
						$ratingArr['clientName'] =  $rating->name;
						$ratingArr['deliveryBoyCode'] = $rating->deliveryBoyCode;
						$ratingArr['rating'] =$rating->rating;
						$ratingArr['review'] = $rating->review;
						$ratingArr['isAccept'] = $rating->isAccept;
						$ratingArr['date'] = $ratingDate;
						$order['ratingDetails'] = $ratingArr;
					}
					$clientOrderLista[] = $order;
				}
				
			}
			if (!empty($clientOrderLista)) {
				$finalResult['orders'] = $clientOrderLista;
				return response()->json(["status" => 200, "message" => " Order details", "result" => $finalResult], 200);
			} else {
				return response()->json(["status" => 300,"message" => "No Data Found"], 200);
			}
	}
	
	public function updateFirebaseId(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
            'firebaseId' => 'required',
        ]);
			$dataMaster = [
				"firebase_id" => $r->firebaseId
			];
			$resultMaster = $this->model->doEdit($dataMaster, 'usermaster', $r->code);
			if ($resultMaster) {
				return response()->json(["status" => 200, "message" => "Firebase Id Update Successfully"], 200);
			} else {
				return response()->json(["status" => 300, "message" => " Failed to update Firebase Id."], 200);
			}
		
	}  // End update firebaseId

	public function getCommissionRecords(Request $r)
	{
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		$today = date('Y-m-d');
		if (isset($r->dater) && $r->dater!='') {
			$today = date('Y-m-d', strtotime(str_replace("/", "-", $r->dater)));
		}
		$code = $dataValidate['code'];
		$orderColumns = array('deliveryboyearncommission.orderCode','deliveryboyearncommission.addDate','deliveryboyearncommission.deliveryBoyCode','deliveryboyearncommission.commissionAmount','deliveryboyearncommission.orderType','deliveryboyearncommission.orderAmount','restaurantordermaster.paymentMode');
		$table = 'deliveryboyearncommission';
		$condition['deliveryboyearncommission.deliveryBoyCode'] = array("=",$code);
		$extraCondition = " date(deliveryboyearncommission.addDate)='".$today."'";
		$orderBy['deliveryboyearncommission.addDate'] = "DESC";
		$join = array('restaurantordermaster' => array('deliveryboyearncommission.orderCode','restaurantordermaster.code'));
		$joinType = array('restaurantordermaster' => 'inner');
		$like = $groupBy = array();
		$limit = $offset = "";
		$resultData = $this->model->selectQuery($orderColumns, $table,  $join,$condition, $orderBy, $like, $limit, $offset, $extraCondition,$joinType);
		$totalAmount = 0;
		$totalReturnAmount = 0;
		$cnt=0;
		$data = array();
		$subdata=array();
		if (!empty($resultData) && count($resultData)>0) {
			
			foreach ($resultData as $r) {
				$totalAmount += $r->commissionAmount;
				/*if($r->paymentMode=='COD'){
				   $totalReturnAmount+=($r->orderAmount); 
				}*/
				$returnamount=0;
				if($r->paymentMode=='COD'){
					//$returnamount=(($r->orderAmount/100)*$r->commissionAmount);
					$returnamount=($r->orderAmount-$r->commissionAmount);
					$totalReturnAmount+=$returnamount;
				}
				$data[] = array(
								"orderCode" => $r->orderCode,
								"addDate" => $r->addDate,
								"deliveryBoyCode" => $r->deliveryBoyCode,
								"commissionAmount" => $r->commissionAmount,
								"orderAmount" => $r->orderAmount, 
                                "returnamount"=>number_format($returnamount,2),								
								"paymentMode" => $r->paymentMode,
							);	
			}
			//$res['earnedList'] = $data;
			return response()->json(["status" => 200,"msg" => "Data found",  "deliveryAmountEarned" => $totalAmount,"totalReturnAmount"=>number_format($totalReturnAmount,2), "earnedList" => $data], 200);
		} else {
			return response()->json(["status" => 300,"msg" => "No Data Found",  "deliveryAmountEarned" => $totalAmount,"totalReturnAmount"=>number_format($totalReturnAmount,2)], 200); 
		}
	}
	
	public function getPenultyDetails(Request $r){ 
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		$code = $dataValidate['code'];
		$tableName = 'deliveryboyearncommission';
		$orderColumns = array("deliveryboyearncommission.id","deliveryboyearncommission.code","deliveryboyearncommission.deliveryBoyCode","deliveryboyearncommission.orderCode","commissionAmount","orderType");
		$condition = array('deliveryboyearncommission.commissionType'=>array("=",'penalty'),'deliveryboyearncommission.deliveryBoyCode'=>array("=",$code));
		$orderBy = array('deliveryboyearncommission.id'=>'DESC');
		$totalAmount=0;
		$Records = $this->model->selectQuery($orderColumns, $tableName, array(),$condition, $orderBy, array(), "", "","", array());
		if(!empty($Records) && count($Records)>0){
			foreach($Records as $Rec){
				$arr = $Rec;
				$totalAmount = $totalAmount+$Rec->commissionAmount;
			}
			return response()->json(["status" => 200, "totalAmount"=>$totalAmount,"result"=>$arr], 200);
		}else{
			return response()->json(["status" => 300, "message" => " No data found"], 200);
		}
	}
	
	public function isUserActive(Request $r){
		$dataValidate=$r->validate([
            'code' => 'required',
        ]);
		$code = $dataValidate['code'];
		$tableName = 'usermaster';
		$orderColumns = array("usermaster.isActive");
		$condition = array('usermaster.isActive'=>array("=",1),'usermaster.code'=>array("=",$code));
		$orderBy = array(); 
		$Records = $this->model->selectQuery($orderColumns, $tableName, array(),$condition, $orderBy, array(), "", "","", array());
		if(!empty($Records) && count($Records)>0){
			return response()->json(["status" => 200, "message"=>"User Active"], 200);
		}else{
			return response()->json(["status" => 300, "message" => "User InActive"], 200);
		}
	}
	
}
