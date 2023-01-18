<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\Restaurants;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderLineEntry;
use App\Models\ClientDevicesDetails;
use App\Models\Settings;
use App\Models\Users;
use DB;
use App\Classes\Notificationlibv_3;
use App\Classes\FirestoreActions;

class HomeController extends Controller
{
	public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	
	public function recentOrderDashboard(Request $r)
	{
		//$data['ordersData']='';
		$code=Auth::guard('restaurant')->user()->code;
		$today = date('Y-m-d');
		$ordersArray=array(); 
		DB::enableQueryLog();
		$resResult = RestaurantOrder::select("restaurantordermaster.*","clientmaster.name","clientmaster.mobile","A.statusTime","A.statusLine")
						->join("bookorderstatuslineentries as A", "A.orderCode", "=", "restaurantordermaster.code")
						->join("bookorderstatuslineentries as B", "B.statusLine", "=", "restaurantordermaster.orderStatus")
						->join("clientmaster", "clientmaster.code", "=", "restaurantordermaster.clientCode")
						//->where('restaurantordermaster.paymentStatus','PID')
						->where('restaurantordermaster.restaurantCode',$code)
						->whereIn('restaurantordermaster.orderStatus', ['PLC','PRE','RFP']) 
						->whereRaw('restaurantordermaster.deliveryBoyCode !="" and restaurantordermaster.deliveryBoyCode is not null')
						->whereRaw('restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null') 
						->orderBy('restaurantordermaster.id', 'desc')
						->groupBy('restaurantordermaster.code')
						->get();
	
		$query_1 = DB::getQueryLog();
        //print_r($query_1);  
				if (!empty($resResult)) {
					foreach ($resResult as $row) {
						$orderCode = $row->code;
						//deliveryBoy Details
						$dbCode = $row->deliveryBoyCode;
						$orderDate = date('d-m-Y h:i A',strtotime($row->addDate)); 
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
						$order['statusLine'] = $row->statusLine;

						$ordersArray[] = $order;							
					}
					$data["ordersData"] = $ordersArray;
					//echo '<pre>';
					//print_r($data["ordersData"]);
				}
				else {
					$data["ordersData"] = false;
				}
					
		return view('restaurant.recentorders',$data);
	}
	public function updateOrderStatus(Request $r){
		DB::enableQueryLog();
	   $orderCode=$r->orderCode;
	   $currentdate = Carbon::now();
	   $ip = $_SERVER['REMOTE_ADDR'];
	   $timeStamp = date("Y-m-d H:i:s");
	   $resCode=Auth::guard('restaurant')->user()->code;
	   $string = "Placed";
	   $orderStatus=$r->orderStatus;
	   $data = array('orderStatus' => $orderStatus, 'editID' =>Auth::guard('restaurant')->user()->code, 'editDate' => $timeStamp);
	   if($orderStatus=="PRE")
	   {
			$data["preparingMinutes"]=25;
	   }
	   $result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
	   //activity log start
		$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Order ".$orderCode." is updated.";
		$this->model->activity_log($data); 
		//activity log end
	   if ($result == true) {
		   $firestoreAction = new FirestoreActions();
           $firestoreAction->update_refresh_code($resCode);
		   if($orderStatus=="PRE")
			{
				$statusReason="Food is preparing...";
			}
			else if($orderStatus=="RJT")
			{
				$statusReason="Rejected by vendor";
			}
			else
			{
				$statusReason="Ready for picked up";
			}
			$bookLineResult=true;
			$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=",$orderStatus)));
			if ($order_status && count($order_status)>0) {
				$order_status_record = $order_status[0];
				$statusTitle = $order_status_record->messageTitle;
				#replace $ template in title 
				$statusDescription = $order_status_record->messageDescription;
				$statusDescription = str_replace("$", $orderCode, $statusDescription);
				$dataBookLine = array(
					"orderCode" => $orderCode, 
					"statusPutCode" => Auth::guard('restaurant')->user()->code,
					"statusLine" => $orderStatus,
					"reason" => $statusReason,
					"statusTime" => $timeStamp,
					"statusTitle"=>$statusTitle,
					"statusDescription"=>$statusDescription,
					"isActive" => 1
				);
				$bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
			}
            if($bookLineResult == true){
				$resOrders = RestaurantOrder::select("restaurantordermaster.*")
								->where('restaurantordermaster.code',$orderCode) 
								->first();
				
				if(!empty($resOrders)){
					//set client code and delivery boy
					$orderStatus = $resOrders->orderStatus;
					$clientCode = $resOrders->clientCode;
					$deliveryBoyCode =$resOrders->deliveryBoyCode;				
					$vendCode = $resOrders->vendorCode; 
					$subTotal=$resOrders->subTotal;
					$grandTotal=$resOrders->grandTotal;
					$type=0;
					$port='';
					$message1='';
					$url='';
					$clientData = ClientDevicesDetails::select("clientdevicedetails.firebaseId")
								->where('clientdevicedetails.clientCode',$clientCode) 
								->first();
								
					if(!empty($clientData)){
						if ($orderStatus == 'PRE') { 
							$type=1;
							$message = 'Be patient, we are preparing your delicious food!';
							$title = 'Food Preparing';
							
							//Delivery boy touch point cumission
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
							    //activity log start
								$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Deliveryboy earn commission ".$deliveryBoyCode." is added";
								$this->model->activity_log($data); 
								//activity log end
							}
							$checkActiveConnectedPort = $this->model->selectQuery(array('activeports.port','activeports.id'),'activeports',array(),array('activeports.status'=>array('=',1),"activeports.isConnected"=>array('=','0'),"activeports.isJunk"=>array('=','0')),array('activeports.id'=>'ASC'),array(),'1');
						    //$query_1 = DB::getQueryLog();
							//print_r($query_1); 
							if($checkActiveConnectedPort && count($checkActiveConnectedPort)>0){
									$port=$checkActiveConnectedPort[0]->port;
									$url = "https://myvegiz.com";
									$id=$checkActiveConnectedPort[0]->id;
									$updateOrder['trackingPort']=$port;
									$update = $this->model->doEdit($updateOrder,'restaurantordermaster',$orderCode);
									DB::table('activeports')->where('id',$id)->update(['isConnected'=>1]);
							}
							
						}else if($orderStatus == 'RJT')
						{
							$message = 'We apologise for the inconvenience, we regret to inform you that we are unable to complete your order #'.$orderCode;
							$title = 'Reject Order'; 
                            $ip = $_SERVER['REMOTE_ADDR'];								
							$dataRjt['orderCode'] = null;
							$dataRjt['editID'] = Auth::guard('restaurant')->user()->code;
							$dataRjt['editIP'] = $ip;
							$dataRjt['orderCount'] = 0; 
							$dataRjt['orderType']='food';
							$delbRejectOrder = $this->model->doEditWithCondition($dataRjt, 'deliveryBoyActiveOrder', $deliveryBoyCode,'deliveryBoyCode');
                            
							//$dBoyRelease['deliveryBoyCode']=null;
							//$resultRelase = $this->model->doEdit($dBoyRelease, 'restaurantordermaster', $orderCode);
							
							//vendor penaulty
							/*$settingResult = Settings::select('settings.*')
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
								    //activity log start
									$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant penalty ".$vendCode." is added";
									$this->model->activity_log($data); 
									//activity log end
								} 
							}*/ 
						}
						else {
							$message = 'Yay, Your food is ready for delivery!!';
							$title = 'Food Ready'; 
						}
					  }
					 	//send notification to delivery boy
						$userData  = Users::select("usermaster.firebase_id")
								->where('usermaster.code',$deliveryBoyCode) 
								->first();
						if(!empty($userData)){
							$DeviceIdsArr =array();
								$DeviceIdsArr[] = $userData->firebase_id;
							if ($orderStatus == 'PRE') { 
								$message = 'Be ready, we are preparing customer order #'.$orderCode.' Yay, you have received touch point amount';
								$title = 'Order Accepted';
							}
							else if($orderStatus == 'RJT')
							{
								$message = 'Order ('.$orderCode.') is rejected. ';
								$title = 'Reject Order'; 		
							}
							else {
								$message = 'Food is Ready! Please Pick-up for Delivery';
								$title = 'Food Ready'; 
							}
							$this->sendNotification($DeviceIdsArr,$title,$message,$orderCode,$type,$url,$port);
						}
				  }
				
				$response['status'] = true;
				$response['message'] = "Order Status Changed Successfully.";
				
	      }else{
				$response['status'] = false;
				$response['message'] = "Failed To Change Status."; 
		 }	  
	    }else{
		   $response['status'] = false;
		   $response['message'] = "Failed To Change Status";
	   }
	   echo json_encode($response);
	}
	
	public function updatePreparingTime(Request $r){
		$timeStamp = date("Y-m-d h:i:s");
		$addID  = Auth::guard('restaurant')->user()->code;
	    $currentdate = Carbon::now();
	    $ip = $_SERVER['REMOTE_ADDR'];
		$orderCode = $r->orderCode;
		$preparingTime = $r->preparingTime;
		$previousTime = $r->previousTime;
		$newTime=($preparingTime+$previousTime);
		$data = array('preparingMinutes' => $newTime, 'editID' => $addID, 'editDate' => $timeStamp);
		$result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
		 //activity log start
		$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Order Preparing Time updated ".$orderCode." is updated.";
		$this->model->activity_log($data); 
		//activity log end
		$response['status'] = true;
		$response['message'] = "Preparing Time Changed";
		echo json_encode($response);
	}
	
	public function getDashboardOrders(){
		return view('restaurant.welcome');
	}
    public function dashboard() {
        return view('restaurant.dashboard');
    }
	public function getRestaurantStatus(){
		$code=Auth::guard('restaurant')->user()->code;
		$restaurant = Restaurants::where("code","=",$code)->first();
		$res['settingValue'] = 0;
		if($restaurant){
			$vendorStatus=$restaurant->isServiceable;
			$res['settingValue'] = $vendorStatus;
		}
		echo json_encode($res);
	}
	public function updateRestaurantStatus(Request $r){
		$currentdate = Carbon::now();
	    $ip = $_SERVER['REMOTE_ADDR'];
		$code=Auth::guard('restaurant')->user()->code;
        $currentdate = Carbon::now();
		$data["isServiceable"] =$r->settingValue;
		$data["manualIsServiceable"] =$r->settingValue;
	    $data["editID"] =$code;
	    $data["editIP"] =$_SERVER['REMOTE_ADDR'];
	    $result = $this->model->doEdit($data,"restaurant",$code); 
	    if($result == true){
			 //activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Status ".$code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
	       echo true;
	    } else{
	        echo  false;
	    } 
	}
	
	public function getOrderCounts(Request $r){
		$today = date('Y-m-d');
		$code=Auth::guard('restaurant')->user()->code;
		
		//$con['restaurantordermaster.paymentStatus']="PID";
		$con['restaurantordermaster.restaurantCode']=$code;
		$orderQuery = $this->model->countQuery("restaurantordermaster",$con);
		if($orderQuery) $totalOrders = $orderQuery->cnt;
		
		
		//$con1['restaurantordermaster.paymentStatus']="PID";
		$con1['restaurantordermaster.restaurantCode']=$code;
		$extraCondition = " (restaurantordermaster.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$todaysorderQuery = $this->model->countQuery("restaurantordermaster",$con1,array(),$extraCondition);
		if($todaysorderQuery) $todaysOrders = $todaysorderQuery->cnt;
		
		$condition4['restaurantordermaster.orderStatus'] = 'PLC';
		$condition4['restaurantordermaster.restaurantCode']=$code;
		//$condition4['restaurantordermaster.paymentStatus'] = 'PID'; 
		$pendingOrderQuery = $this->model->countQuery("restaurantordermaster",$condition4);
		if($pendingOrderQuery) $pendingOrders = $pendingOrderQuery->cnt;
	
		$placedOrderQuery=RestaurantOrder::select(DB::raw('COUNT(id) as confirmOrders'))
	            ->where(['restaurantCode' => $code])
				->where(['orderStatus' =>'PRE'])
				->orWhere(['orderStatus'=>'RFP'])
				->orWhere(['orderStatus'=>'PUP'])
				->first();
		if(!empty($placedOrderQuery)){
			$placedOrders=$placedOrderQuery->confirmOrders;
		}
		
		//$condition6['bookorderstatuslineentries.statusLine'] = 'DEL';
		$condition6['restaurantordermaster.orderStatus'] = 'DEL';
		$condition6['restaurantordermaster.restaurantCode']=$code;
		//$join1=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		//$extraCondition1 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$deliveredOrderQuery = $this->model->countQuery("restaurantordermaster",$condition6,array(),"");
		if ($deliveredOrderQuery) {
			$deliveredOrders = $deliveredOrderQuery->cnt;
		}
		
		/*$condition7['bookorderstatuslineentries.statusLine'] = 'CAN';
		//$condition7['restaurantordermaster.restaurantCode']=$code;
		//$join2=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		$extraCondition2 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery = $this->model->countQuery("bookorderstatuslineentries",$condition7,$join2,$extraCondition2);
		if ($cancelledOrderQuery) {
			$cancelledOrders = $cancelledOrderQuery->cnt;
		}
		/*$condition8['bookorderstatuslineentries.statusLine'] = 'RJT';
		$condition8['restaurantordermaster.restaurantCode']=$code;
		$join3=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		$extraCondition3 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery1 = $this->model->countQuery("bookorderstatuslineentries",$condition8,$join3,$extraCondition3);
		if ($cancelledOrderQuery1) {
			$cancelledOrders1 = $cancelledOrderQuery1->cnt; 
		}*/
		
		$getRejectOrders=RestaurantOrder::select(DB::raw('COUNT(id) as rejectOrders'))
	            ->where(['restaurantCode' => $code])
				->where(['orderStatus' =>'RJT'])
				->orWhere(['orderStatus'=>'CAN'])
				->first();
		if(!empty($getRejectOrders)){
			$resrejectOrders=$getRejectOrders->rejectOrders;
		}
		//$cancelledOrders = $cancelledOrders1+$cancelledOrders;
		$res['totalOrders'] = $totalOrders;
		$res['todaysOrders'] = $todaysOrders; 
		$res['pendingOrders'] = $pendingOrders;
		$res['cancelledOrders'] = $resrejectOrders;
		$res['confirmedOrders'] = $placedOrders;
		$res['deliveredOrders'] = $deliveredOrders;
		echo json_encode($res);
		
	}
	
	public function getOrdersDoughnutChartData(){
		DB::enableQueryLog();
		$today = date('Y-m-d');
		$code=Auth::guard('restaurant')->user()->code;
		
		$condition4['restaurantordermaster.orderStatus'] = 'PLC';
		$condition4['restaurantordermaster.restaurantCode']=$code;
		//$condition4['restaurantordermaster.paymentStatus'] = 'PID';
		$extra = " (restaurantordermaster.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$pendingOrderQuery = $this->model->countQuery("restaurantordermaster",$condition4,array(),$extra);
		if($pendingOrderQuery) $pendingOrders = $pendingOrderQuery->cnt;
		
		$placedOrderQuery=RestaurantOrder::select(DB::raw('COUNT(id) as confirmOrders'))
	            ->where(['restaurantCode' => $code])
				->where(['orderStatus' =>'PRE'])
				->orWhere(['orderStatus'=>'RFP'])
				->orWhere(['orderStatus'=>'PUP'])
				->first();
		if(!empty($placedOrderQuery)){
			$placedOrders=$placedOrderQuery->confirmOrders;
		}
		
		//$condition6['bookorderstatuslineentries.statusLine'] = 'DEL';
		$condition6['restaurantordermaster.orderStatus'] = 'DEL';
		$condition6['restaurantordermaster.restaurantCode']=$code;
		//$join1=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		//$extraCondition1 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$deliveredOrderQuery = $this->model->countQuery("restaurantordermaster",$condition6,array(),"");
		if ($deliveredOrderQuery) {
			$deliveredOrders = $deliveredOrderQuery->cnt;
		}
		
		/*$condition7['bookorderstatuslineentries.statusLine'] = 'CAN';
		$condition7['restaurantordermaster.restaurantCode']=$code;
		$join2=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		$extraCondition2 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery = $this->model->countQuery("bookorderstatuslineentries",$condition7,$join2,$extraCondition2);
		if ($cancelledOrderQuery) {
			$cancelledOrders = $cancelledOrderQuery->cnt;
		}
		$condition8['bookorderstatuslineentries.statusLine'] = 'RJT';
		$condition8['restaurantordermaster.restaurantCode']=$code;
		$join3=array('restaurantordermaster'=> array('restaurantordermaster.orderStatus','bookorderstatuslineentries.statusLine'));
		$extraCondition3 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery1 = $this->model->countQuery("bookorderstatuslineentries",$condition8,$join3,$extraCondition3);
		if ($cancelledOrderQuery1) {
			$cancelledOrders1 = $cancelledOrderQuery1->cnt; 
		}
		$cancelledOrders = $cancelledOrders1+$cancelledOrders;*/
		
		$getRejectOrders=RestaurantOrder::select(DB::raw('COUNT(id) as rejectOrders'))
	            ->where(['restaurantCode' => $code])
				->where(['orderStatus' =>'RJT'])
				->orWhere(['orderStatus'=>'CAN'])
				->first();
		if(!empty($getRejectOrders)){
			$resrejectOrders=$getRejectOrders->rejectOrders;
		}
		$dataArr=[$pendingOrders,$resrejectOrders,$placedOrders,$deliveredOrders];
		//$dataArr=[100,200,250,300];
		$data=[];
		$textArr=["Pending Order","Cancel Order","Confirm Order","Delivered Order"];
		for($i=0;$i<count($textArr);$i++){
			$data['label'][] = $textArr[$i];
			$data['data'][] = $dataArr[$i];
            $data['color'][]= "#".substr(md5(rand()), 0, 6);
        }

        $result_array = [
          'data'  => $data
          ];
		  
          echo json_encode($result_array);
         
	}
	
	public function getOrders(Request $req)
    {
		$code=Auth::guard('restaurant')->user()->code;
        $search = $req->input('search.value');
        $type = $req->type;
        $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","usermaster.name as deliveryboy","clientmaster.name","clientmaster.mobile","restaurant.entityName","restaurantorderstatusmaster.statusSName","restaurantordermaster.code");
        $condition = array("restaurantordermaster.isActive"=>array("=",1),"restaurantordermaster.restaurantCode"=>array("=",$code));
        $orderBy = array('restaurantordermaster.id' => 'DESC');
		if ($type ==5) {
			$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'inner', 'restaurantorderstatusmaster' => 'inner');
			$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
		} else {
			$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner');
			$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
		}
        $like = array('restaurant.entityName' => $search, 'restaurantordermaster.code' => $search);
        $limit = $req->length;
        $offset = $req->start;
		$extraCondition='';
		if($type==2){
			$extraCondition = " restaurantordermaster.addDate between '".date("Y-m-d")." 00:00:00' And '".date("Y-m-d")." 23:59:59'";
		}
		if($type==3){
			$condition['restaurantordermaster.orderStatus']=array("=","PND");
		}else if($type==4){
			$extraCondition .= " restaurantordermaster.orderStatus in('CAN','RJT')";
		}else if($type==5){
			$condition['restaurantordermaster.orderStatus']=array("=","PLC");
		}else if($type==6){
			$condition['restaurantordermaster.orderStatus']=array("=","DEL");
		}
        $result = $this->model->selectQueryWithJoinType($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			$statusTime = $row->addDate;
			$recordsLineStatus = $this->model->selectQueryWithJoinType(array("bookorderstatuslineentries.addDate as orderaddDate","bookorderstatuslineentries.statusTime"), "bookorderstatuslineentries", array(),array("bookorderstatuslineentries.orderCode" => array("=",$row->code)), array(), array(), 1);
			if ($recordsLineStatus && count($recordsLineStatus)>0) {
				$statusTime = $recordsLineStatus[0]->statusTime;
			}
		    $orderDate = date('d-m-Y h:i:s', strtotime($row->addDate));
				$orderStatus = $row->orderStatus;
				$odStatus = $row->orderStatus;
				switch ($orderStatus) {
					case "PND":
						$orderStatus = "Pending";
						$orderDate = date('d-m-Y h:i A', strtotime($row->addDate));
						break;
					case "PLC":
						$orderStatus = "Placed";
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "SHP":
						$orderStatus = "Shipped";
						$chkSHP = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "DEL":
						$orderStatus = "Delivered";
						$chkDEL = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "CAN":
						$orderStatus = "Cancelled By User";
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "RJT":
						$orderStatus = "Rejected";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "PRE":
						$orderStatus = "Preparing";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "FRE":
						$orderStatus = "Food Ready";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "REL":
						$orderStatus = "Release";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "PUP":
						$orderStatus = "On the Way";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break; 
					case "RFP":
						$orderStatus = "Ready For Pickup";
						$chkSHP = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
					break;
				}
				$actionHtml = '  <a class="btn btn-primary" href="' . url("restaurantPendingOrder/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>';
				$data[] = array(
					$srno,
					$row->code,
					$row->name,
					$row->address,
					$row->mobile,
					$orderStatus,
					$row->grandTotal,
					$orderDate,
					$row->deliveryboy, 
					$actionHtml
				);
			$srno++;
		}
		$dataCount = sizeof($this->model->selectQueryWithJoinType($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition,$joinType));
	}
	$output = array(
		"draw" => intval($_GET["draw"]),
		"recordsTotal" => $dataCount,
		"recordsFiltered" => $dataCount,
		"data" => $data
	);
	echo json_encode($output);
    }
	
	public function sendNotification($DeviceIdsArr = array(), $title, $message, $orderCode,$trackingNoti='',$url='',$port='')
	{
		$random = rand(0, 999);
		$dataArr = $notification = array();
		$dataArr['device_id'] = $DeviceIdsArr;
		$dataArr['message'] = $message; 
		$dataArr['title'] = $title;
		$dataArr['order_id'] = $orderCode;
		$dataArr['random_id'] = $random;
		$dataArr['type'] = 'order';
		if($trackingNoti==1){
			$dataArr['url'] = $url;
			$dataArr['port'] = $port;
		}
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
}
