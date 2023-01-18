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
use App\Models\RestaurantOrder;
use App\Models\RestaurantStatusMaster;
use App\Models\Users;
use App\Models\Settings;
use DB;
use App\Classes\Notificationlibv_3;
use App\Models\ClientDevicesDetails;
use App\Classes\FirestoreActions;


class RestaurantConfirmOrderController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;   
    }
	
	public function index()
	{
		$code=Auth::guard('restaurant')->user()->code;
        //DB::enableQueryLog();		
	    $data['restaurantstatus'] = RestaurantStatusMaster::get();
		//$query_1 = DB::getQueryLog();  
        //print_r($query_1);
		$data['restaurantorder'] = RestaurantOrder::where('isActive', 1)->where('restaurantCode',$code)->get();
		return view('restaurant.confirmorder.list',$data);
	}
	
	public function getRestaurantConfirmOrder(Request $req)
	{
		DB::enableQueryLog();
		$code=Auth::guard('restaurant')->user()->code;
		$orderStatus = $req->status;
		$ordercode = $req->ordercode;
		if ($orderStatus == "") {
			$orderStatus = "PRE";
		}
		$fromDate = $req->fromdate;
		$toDate = $req->todate;
		$datw="";
		if ($fromDate != '') {
			//$startDate = date('Y-m-d', strtotime($fromDate));
			//$endDate = date('Y-m-d', strtotime($toDate));
			$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
			$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		 $datw="and ( restaurantordermaster.addDate between '".$startDate."' And '".$endDate."')";
		}
		$search = $req->input('search.value');
	    $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","clientmaster.name","clientmaster.mobile","bookorderstatuslineentries.statusTime","bookorderstatuslineentries.statusLine");
        $condition = array('restaurantordermaster.code'=>array('=',$ordercode),'restaurantordermaster.restaurantCode' => array('=', $code),'bookorderstatuslineentries.statusLine' => array('=',$orderStatus));
        $orderBy = array('restaurantordermaster' . '.id' => 'DESC');
        $join = array('clientmaster' => array('clientmaster.code', 'restaurantordermaster.clientCode'), 'bookorderstatuslineentries' => array('bookorderstatuslineentries.orderCode', 'restaurantordermaster.code'));
        $like = array('clientmaster.name' => $search, 'restaurantordermaster.restaurantCode' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "(restaurantordermaster.orderStatus in ('PRE','DEL','RFP','PUP','RCH')) and (restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null)".$datw;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition); 
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
        //$query_1 = DB::getQueryLog();    
        //print_r($query_1);
        if($result && $result->count() > 0) {
			 foreach ($result as $row) {
				$statusTime = $row->statusTime;
				$DBName = "";
					if($row->deliveryBoyCode !='' or $row->deliveryBoyCode !=null)
					{
						$dBoy = Users::select("usermaster.*")
									->where("usermaster.code",$row->deliveryBoyCode)
									->first();
						if(!empty($dBoy)){
							$DBName=$dBoy->name;
							$DBContact=$dBoy->mobile;
						}
                    
					}
					if ($row->isActive == 1) {
					$status = "<span class='label label-sm label-success'>Active</span>";
					} else {
						$status = "<span class='label label-sm label-warning'>Inactive</span>";
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
							$orderStatus = "Pending";
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
						case "RFP":
							$orderStatus = "Ready for Pickup";
							$chkRJT = 'checked';
							$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
							break;
						case "PUP":
							$orderStatus = "On the Way"; 
							$chkRJT = 'checked';
							$orderDate = date('d-m-Y h:i A', strtotime($statusTime)); 
							break; 
					  }
					  $actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' .url("restaurantConfirmOrder/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' .url("restaurantConfirmOrder/invoice/" . $row->code) . '"><i class="ti-notepad"></i> Invoice</a>
							</div>
						</div>';
							$data[] = array(
								$srno,
								$row->code,
								$row->name,
								$row->address,
								$row->mobile,
								$orderStatus,
								$row->grandTotal,
								$orderDate,
								$DBName,
								$actionHtml
							); 
                            
                         $srno++;						
				     }
				
				   $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, array(), '', '',$extraCondition)); 
				}
				$output = array(
					"draw"			  =>     intval($_GET["draw"]),
					"recordsTotal"    =>     $dataCount,
					"recordsFiltered" =>     $dataCount,
					"data"            =>     $data
				);
				echo json_encode($output);		
       }
	   
	 public function view($code)
	{
		$data['discountInPercent'] = 0;
		$data['orderData'] = false;
		$tableName = "restaurantordermaster";
		$orderColumns = array("restaurantordermaster.*","clientmaster.name as Clientname","clientmaster.mobile","usermaster.name as DeliveryBoyName","clientmaster.cityCode");
		$condition = array('restaurantordermaster.code' => array("=",$code));
		$orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner','usermaster'=>'left');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'),'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'));
		$extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL)";
		$like = array();
		$Records = $this->model->selectQueryWithJoinType($orderColumns, $tableName, $join,$condition, $orderBy, array(),'','', $extraCondition,$joinType);
		$data['query'] = false;
		if ($Records) {
			$data['query'] = $Records;
			$couponCode = $Records[0]->couponCode;
			$restaurantCode = $Records[0]->restaurantCode;
			$dataCC = $this->model->selectQuery(array('restaurantoffer.discount'), 'restaurantoffer',array(), array('restaurantoffer.couponCode' => array("=",$couponCode), 'restaurantoffer.restaurantCode' => array("=",$restaurantCode)));
			if (!empty($dataCC) && count($dataCC)>0) { 
				$data['discountInPercent'] = $dataCC[0]->discount;
			}
		} 
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
        return view('restaurant.confirmorder.view', $data);  
	}
	
	public function invoice($code){
		$data['discountInPercent'] = 0;
		$data['orderData'] = false;
		$tableName = "restaurantordermaster";
		$orderColumns = array("restaurantordermaster.*","clientmaster.name as Clientname","clientmaster.mobile","usermaster.name as DeliveryBoyName","clientmaster.cityCode");
		$condition = array('restaurantordermaster.code' => array("=",$code));
		$orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner','usermaster'=>'left');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'),'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'));
		$extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL)";
		$like = array();
		DB::enableQueryLog();
		$Records = $this->model->selectQueryWithJoinType($orderColumns, $tableName, $join,$condition, $orderBy, array(),'','', $extraCondition,$joinType);
		$data['query'] = false;
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
		if (!empty($Records)) {
			$couponCode = $Records[0]->couponCode;
			$restaurantCode = $Records[0]->restaurantCode;
			$dataCC = $this->model->selectQuery(array('restaurantoffer.discount'), 'restaurantoffer',array(), array('restaurantoffer.couponCode' => array("=",$couponCode), 'restaurantoffer.restaurantCode' => array("=",$restaurantCode)));
			if (!empty($dataCC) && count($dataCC)>0) {
				$data['discountInPercent'] = $dataCC[0]->discount;
			}
			
			$tableName = 'restaurantorderlineentries';
			$orderColumns = array("restaurantorderlineentries.*","restaurantitemmaster.restaurantCode","restaurantitemmaster.itemName","restaurantitemmaster.salePrice","restaurantitemmaster.itemPhoto");
			$condition = array('restaurantorderlineentries.orderCode' => array("=",$code));
			$orderBy = array('restaurantorderlineentries.id' => 'desc');
			$joinType = array('restaurantitemmaster' => 'inner');
			$join = array('restaurantitemmaster' =>array('restaurantitemmaster.code','restaurantorderlineentries.restaurantItemCode'));
			$groupByColumn = array();
			$limit = '';
			$offset = '';
			//$srno = $offset + 1;
			$addonText='';
			$extraCondition = " restaurantorderlineentries.isActive=1";
			$like = array();
			$data = array();
			$srno =0;
			$lineData='';
			$res = $this->model->selectQuery($orderColumns, $tableName,$join, $condition,  $orderBy, $like, $limit, $offset, $extraCondition, $joinType);
			if ($res) {
			foreach ($res as $row) {
				$addonText='';
				if($row->addonsCode!='' && $row->addonsCode!=NULL){
					$row->addonsCode = rtrim($row->addonsCode,', ');
					$savedaddonsCodes = explode(', ',$row->addonsCode);
					foreach($savedaddonsCodes as $addon){
						$joinType1 = array('customizedcategory' => 'inner');
						$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
						$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
						$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),'','','',$joinType1);
						$prevMainCateg=$prevMainCateg1='';
						if($getAddonDetails){
							foreach($getAddonDetails as $ad){
								$mainCategory = $ad->categoryTitle;
								$addonText.='<ul>
										<li>'.$ad->subCategoryTitle.' - '.$ad->price.' Rs.</li>
									</ul>';
							}
						}
					}
				}
				$start = '<div class="d-flex align-items-center">';
				$end = ' <h5 class="m-b-0 font-16 font-medium">' . $row->itemName . '</h5></div></div>';
				$itemPhotoCheck = $row->itemPhoto;
				if ($itemPhotoCheck != "") {
					$itemPhoto = env('IMG_URL').'uploads/restaurant/restaurantitemimage/' . $row->restaurantCode .'/'. $itemPhotoCheck;
					$photo = '<div class="m-r-10"><img src="' . $itemPhoto . '?' . time() . '" alt="user" class="circle" width="45"></div><div class="">';
					$itemName = $start . $photo . $end;
					
				}
				 $srno++;
				$lineData .= '
				<tr>
				<td colspan="2"><div align="center">' . $srno  . '</div></td>
				<td colspan="2"><div><b>' . $row->itemName .'</b><br>'.$addonText. '</div></td>
				<td colspan="2"><div align="center">' . $row->salePrice . '</div></td>
				<td colspan="2"><div align="center">' . $row->quantity . '</div></td>
				<td colspan="2"><div align="center">' . $row->priceWithQuantity . '</div></td>
			   </tr>
			   ';
			  
			}
		  }
		}
        $data['lineData'] = $lineData;
	    $data['query'] = $Records;		
		$data['company'] = $this->model->selectAllDataFromTable('companyinfo');
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
		return view('restaurant.confirmorder.invoice',$data); 
	}
	
	public function getOrderDetails(Request $r)
	{
		DB::enableQueryLog();
		$orderCode = $r->orderCode;
		$tableName = 'restaurantorderlineentries';
		$orderColumns = array("restaurantorderlineentries.*","restaurantitemmaster.restaurantCode","restaurantitemmaster.itemName","restaurantitemmaster.salePrice","restaurantitemmaster.itemPhoto");
		$condition = array('restaurantorderlineentries.orderCode' => array("=",$orderCode));
		$orderBy = array('restaurantorderlineentries.id' => 'desc');
		$joinType = array('restaurantitemmaster' => 'inner');
		$join = array('restaurantitemmaster' =>array('restaurantitemmaster.code','restaurantorderlineentries.restaurantItemCode'));
		$groupByColumn = array();
		$limit = $r->length;
		$offset = $r->start;
		$srno = $offset + 1;
		$addonText='';
		$extraCondition = " restaurantorderlineentries.isActive=1";
		$like = array();
		$data = array();
		$Records = $this->model->selectQuery($orderColumns, $tableName,$join, $condition,  $orderBy, $like, $limit, $offset, $extraCondition, $joinType);
		$query_1 = DB::getQueryLog();    
        //print_r($query_1); 
		if ($Records) {
			foreach ($Records as $row) {
				$addonText='';
				if($row->addonsCode!='' && $row->addonsCode!=NULL){
					$row->addonsCode = rtrim($row->addonsCode,', ');
					$savedaddonsCodes = explode(', ',$row->addonsCode);
					foreach($savedaddonsCodes as $addon){
						$joinType1 = array('customizedcategory' => 'inner');
						$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
						$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
						$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),'','','',$joinType1);
						$prevMainCateg=$prevMainCateg1='';
						if($getAddonDetails){
							foreach($getAddonDetails as $ad){
								$mainCategory = $ad->categoryTitle;
								$addonText.='<ul>
									
										<li>'.$ad->subCategoryTitle.' - '.$ad->price.' Rs.</li>
									
								</ul>';
							}
						}
					}
				}
				$start = '<div class="d-flex align-items-center">';
				$end = ' <h5 class="m-b-0 font-16 font-medium">' . $row->itemName . '</h5></div></div>';
				$itemPhotoCheck = $row->itemPhoto;
				if ($itemPhotoCheck != "") {
					$itemPhoto = env('IMG_URL').'uploads/restaurant/restaurantitemimage/' . $row->restaurantCode .'/'. $itemPhotoCheck;
					$photo = '<div class="m-r-10"><img src="' . $itemPhoto . '?' . time() . '" alt="user" class="circle" width="45"></div><div class="">';
					$itemName = $start . $photo . $end;
					$data[] = array($srno, $row->restaurantItemCode, $itemName.'<br>'.$addonText, $row->salePrice, $row->quantity, $row->priceWithQuantity);
				} else {
					$itemName = ' <h5 class="m-b-0 font-16 font-medium">' . $row->itemName . '</h5></div></div>';
					$data[] = array($srno, $row->restaurantItemCode, $itemName.'<br>'.$addonText, $row->salePrice, $row->quantity, $row->priceWithQuantity);
				}
				$srno++;
			}
		}
		// $dataCount = sizeOf($this->GlobalModel->selectQuery($orderColumns, $tableName, $condition, $orderBy, $join, $joinType, $like, '', '', $groupByColumn, $extraCondition)->result());
		$dataCount = 0;
		$dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
		
		$output = array("draw" => intval($_GET["draw"]), "recordsTotal" => $dataCount, "recordsFiltered" => $dataCount, "data" => $data);
		echo json_encode($output); 
	}
	
	public function reject(Request $req)
	{
		DB::enableQueryLog();
		$code=Auth::guard('restaurant')->user()->code;
		$currentdate = Carbon::now();
		$orderCode=$req->code;
		$ip = $_SERVER['REMOTE_ADDR'];
		$timeStamp = date("Y-m-d h:i:s");
		$data = array('orderStatus' => 'RJT', 'paymentStatus' => 'RJCT', 'editDate' => $timeStamp);
		$result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
		if ($result == true) {
			
		   $firestoreAction = new FirestoreActions();
           $firestoreAction->update_refresh_code($code);
			
			 //activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Order ".$orderCode." is updated.";
			$this->model->activity_log($data); 
			//activity log end
			$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=","RJT")));
			if ($order_status && count($order_status)>0) {
				$order_status_record = $order_status[0];
				$statusTitle = $order_status_record->messageTitle;
				#replace $ template in title 
				$statusDescription = $order_status_record->messageDescription;
				$statusDescription = str_replace("$", $orderCode, $statusDescription);		
				$dataBookLine = array(
					"orderCode" => $orderCode,
					"statusPutCode" => $code,
					"statusLine" => 'RJT',
					"reason" => 'Order Rejected By Vendor',
					"statusTime" => $timeStamp,
					"statusTitle"=>$statusTitle,
					"statusDescription"=>$statusDescription,
					"isActive" => 1
				);
				$bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
			}
			$resResult = RestaurantOrder::select("restaurantordermaster.*")
						->where('restaurantordermaster.code',$orderCode)
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

                    if($DBFlag==1){							
						$dataRjt['orderCode'] = null;
						$dataRjt['editID'] = Auth::guard('restaurant')->user()->code;
						$dataRjt['editIP'] = $ip;
						$dataRjt['orderCount'] = 0; 
						$dataRjt['orderType']='food';
						$delbRejectOrder = $this->model->doEditWithCondition($dataRjt, 'deliveryBoyActiveOrder', $deliveryBoyCode,'deliveryBoyCode');
						
						 //activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Deliveryboy Active Order ".$deliveryBoyCode." is updated.";
						$this->model->activity_log($data); 
						//activity log end
						
						//$dBoyRelease['deliveryBoyCode']=null;
						//$resultRelase = $this->model->doEdit($dBoyRelease, 'restaurantordermaster', $orderCode);						
				
				         //activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Order master ".$orderCode." is updated.";
						$this->model->activity_log($data); 
						//activity log end
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
						    //activity log start
							$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Deliveryboy earn commission ".$deliveryBoyCode." is added";
							$this->model->activity_log($data); 
							//activity log end
						}
						
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
							} */
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
			$res['status']=true;
	    }
		else {
			$res['status']=false;
		}
		echo json_encode($res);
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
	
}