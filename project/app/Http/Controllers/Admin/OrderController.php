<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Classes\Notificationlibv_3;
use App\Models\DeliveryBoyActiveOrder; 
use DB;
use App\Classes\FirestoreActions;


class OrderController extends Controller
{
    public function __construct(GlobalModel $model) 
    {
        $this->model = $model;
    }
	
	public function index()
	{
		$data['orderList'] = $this->model->selectQuery(array('restaurantordermaster.code'),'restaurantordermaster',array(),array('restaurantordermaster.isActive'=>array('=',1),'restaurantordermaster.orderStatus'=>array('!=','PND')),array('restaurantordermaster.id'=>'DESC'));
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['deliveryboy'] = $this->model->selectQuery(array('usermaster.code','usermaster.name'),'usermaster',array(),array("usermaster.isActive"=>array("=",1),"usermaster.role"=>array("=","DBOY")));
		return view('admin.order.list',$data);
	}
	
	public function pendingList()
	{
		$data['orderList'] = $this->model->selectQuery(array('restaurantordermaster.code'),'restaurantordermaster',array(),array('restaurantordermaster.isActive'=>array('=',1),'restaurantordermaster.orderStatus'=>array('=','PND')),array('restaurantordermaster.id'=>'DESC'));
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		return view('admin.order.pendingList',$data);
	}
	
	public function getOrders(Request $req)
	{
		DB::enableQueryLog();
		$search = $req->input('search.value');
		$orderCode = $req->orderCode;
        $restaurantCode = $req->restaurantCode;
		$orderStatus= $req->orderStatus;
		$deliveryBoyCode = $req->deliveryBoyCode;
		$fromDate = $req->fromDate;
		$toDate = $req->toDate;
		$datw="";
		if ($fromDate != '') {
			$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
			$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		 $datw=" AND restaurantordermaster.editDate between '".$startDate."' And '".$endDate."'";
		}
        $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","usermaster.name as deliveryboy","clientmaster.name","clientmaster.mobile","restaurant.entityName","restaurantorderstatusmaster.statusSName","restaurantordermaster.code");
        $condition = array("restaurantordermaster.restaurantCode" => array("=",$restaurantCode), "restaurantordermaster.deliveryBoyCode" => array("=",$deliveryBoyCode), "restaurantordermaster.orderStatus" => array("=",$orderStatus), "restaurantordermaster.code" => array("=",$orderCode));
        $orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'inner', 'restaurantorderstatusmaster' => 'inner');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
        $like = array('restaurant.entityName' => $search, 'restaurantordermaster.code' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL) and restaurantordermaster.orderStatus!='PND' ".$datw;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		//$query_1 = DB::getQueryLog(); 
        //print_r($query_1);
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			$statusTime='';
			$recordsLineStatus = $this->model->selectQuery(array("bookorderstatuslineentries.addDate as orderaddDate","bookorderstatuslineentries.statusTime"), "bookorderstatuslineentries", array(),array("bookorderstatuslineentries.orderCode" => array("=",$row->code)), array(), array(), 1);
			if ($recordsLineStatus && count($recordsLineStatus)>0) {
				$statusTime = $recordsLineStatus[0]->statusTime;
			}
		    $orderDate = date('d-m-Y h:i:s', strtotime($row->addDate));
				$orderStatus = $row->orderStatus;
				$odStatus = $row->orderStatus;
				switch ($orderStatus) {
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
					case "RCH":
						$orderStatus = "Reached";
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
				$actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' .url("order/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>';								
								if($row->orderStatus!='PND' && $row->orderStatus!='CAN' && $row->orderStatus!='RJT'){
										$actionHtml .= ' <a class="dropdown-item" href="' .url("order/tracking/" . $row->code) . '"><i class="ti-direction"></i> Tracking</a>';
									}
								$actionHtml .= '<a class="dropdown-item" href="' .url("order/invoice/" . $row->code) . '"><i class="ti-notepad"></i> Invoice</a>
							</div>
						</div>';
				$paymentMode = "<span class='label label-sm label-success'>".$row->paymentMode."</span>";
				$data[] = array(
					$srno,
					$row->code.'<br>'.$paymentMode,
					$row->name,
					$row->entityName,
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
		$dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition,$joinType));
	}
	$output = array(
		"draw" => intval($_GET["draw"]),
		"recordsTotal" => $dataCount,
		"recordsFiltered" => $dataCount,
		"data" => $data
	);
	echo json_encode($output);
	}

	public function getPendingOrders(Request $req)
	{
		$search = $req->input('search.value');
		$orderCode = $req->orderCode;
        $restaurantCode = $req->restaurantCode;
		$deliveryBoyCode = $req->deliveryBoyCode;
		$fromDate = $req->fromDate;
		$toDate = $req->toDate;
		$datw="";
		DB::enableQueryLog();
		if ($fromDate != '') {
			$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
			$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		   $datw=" AND restaurantordermaster.addDate between '".$startDate."' And '".$endDate."'";
		}
        $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","usermaster.name as deliveryboy","clientmaster.name","clientmaster.mobile","restaurant.entityName","restaurantorderstatusmaster.statusSName","restaurantordermaster.code");
        $condition = array("restaurantordermaster.restaurantCode" => array("=",$restaurantCode), "restaurantordermaster.code" => array("=",$orderCode));
        $orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
        $like = array('restaurant.entityName' => $search, 'restaurantordermaster.code' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL) and restaurantordermaster.orderStatus='PND' ".$datw;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		$query_1 = DB::getQueryLog();
		//print_r($query_1);
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
		    $orderDate = date('d-m-Y h:i:s', strtotime($row->addDate));
				$orderStatus = $row->orderStatus;
				$odStatus = $row->orderStatus;
				switch ($orderStatus) {
					case "PND":
						$orderStatus = "Pending";
						$orderDate = date('d-m-Y h:i A', strtotime($row->addDate));
						break;
				}
				$actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' .url("order/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
							</div>
						</div>';
				$paymentMode = "<span class='label label-sm label-success'>".$row->paymentMode."</span>";
				$data[] = array(
					$srno,
					$row->code.'<br>'.$paymentMode,
					$row->name,
					$row->entityName,
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
		$dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition,$joinType));
	}
	$output = array(
		"draw" => intval($_GET["draw"]),
		"recordsTotal" => $dataCount,
		"recordsFiltered" => $dataCount,
		"data" => $data
	);
	echo json_encode($output);
	}
	
	public function view($code)
	{
		$data['discountInPercent'] = 0;
		$data['orderData'] = false;
		$tableName = "restaurantordermaster";
		$orderColumns = array("restaurantordermaster.*","clientmaster.name as clientName","clientmaster.mobile","usermaster.name","clientmaster.cityCode");
		$condition = array('restaurantordermaster.code' => array("=",$code));
		$orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner','usermaster'=>'left');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'),'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'));
		$extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL)";
		$like = array();
		$Records = $this->model->selectQuery($orderColumns, $tableName, $join,$condition, $orderBy, array(),'','', $extraCondition,$joinType);
		$data['query'] = false;
		if ($Records && count($Records)>0) {
			$data['query'] = $Records;
			$couponCode = $Records[0]->couponCode;
			$restaurantCode = $Records[0]->restaurantCode;
			$dataCC = $this->model->selectQuery(array('restaurantoffer.discount'), 'restaurantoffer',array(), array('restaurantoffer.couponCode' => array("=",$couponCode), 'restaurantoffer.restaurantCode' => array("=",$restaurantCode)));
			if ($dataCC && count($dataCC)>0) {
				$data['discountInPercent'] = $dataCC[0]->discount;
			}
		}
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
        return view('admin.order.view', $data);  
	}
	
	public function getPendingDeliveryBoys(Request $r){
		$deliveryBoyCode = $r->deliveryBoyCode;
		$cityCode = $r->cityCode;
		//DB::enableQueryLog();
		$statusType = 'PND';
		$data = array();
		$dataCount= 0;
		$condition =array("usermaster.code"=>array("!=",$deliveryBoyCode),"usermaster.isActive"=>array("=",1),"usermaster.cityCode"=>array("=",$cityCode),"usermaster.role"=>array("=","DBOY"));
		$orderBy = array("usermaster.id"=>"ASC");
		if($statusType=='all'){
		} else {
			if($statusType=='present'){
				$condition['deliveryBoyActiveOrder.loginStatus'] = array("=",'1');
			} else if($statusType=='absent') {
				$condition['deliveryBoyActiveOrder.loginStatus']=  array("=",'0');
			} else {
				$condition['deliveryBoyActiveOrder.loginStatus'] = array("=",'1');
				$condition['deliveryBoyActiveOrder.orderCount'] = array("=",'0');
			}
			$join = array("deliveryBoyActiveOrder"=>array("usermaster.code","deliveryBoyActiveOrder.deliveryBoyCode"));
			$joinType = array("deliveryBoyActiveOrder"=>"inner"); 
		}
		
		$Result =$this->model->selectQuery(array("usermaster.*"),"usermaster",$join,$condition,$orderBy,array(),"","","",$joinType);
		$query_1 = DB::getQueryLog();
         //print_r($query_1); 
		$html='<option value="" readonly>Select another delivery boy</option>';
		if($Result){
			foreach ($Result as $key) {
				$html.='<option value="' . 	$key->code . '">'.$key->name.'</option>';
			}
		}else{
		    $html=false; 
		}
		echo $html;
	}
	
	public function getOrderDetails(Request $r)
	{
		 DB::enableQueryLog();
		$orderCode = $r->orderCode;
		$noPic = $r->noPic;
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
					$itemPhoto = url('uploads/restaurant/restaurantitemimage/' . $row->restaurantCode .'/'. $itemPhotoCheck);
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
		$dataCount1 = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
		
		$output = array("draw" => intval($_GET["draw"]), "recordsTotal" => $dataCount, "recordsFiltered" => $dataCount, "data" => $data);
		echo json_encode($output);
	}

	public function transferOrder(Request $r)
	{
		$orderCode = $r->orderCode;
		$resCode=$r->resCode;
		$fromDeliveryBoy = $r->fromDeliveryBoy;
		$toDeliveryBoy = $r->toDeliveryBoy;
		$orderStatus = $r->orderStatus;
		$orderType = $r->orderType;
		$orderData['deliveryBoyCode'] = $toDeliveryBoy;
		if($orderStatus=='PND'){
			$orderData['orderStatus'] = 'PLC';
		}
		$orderUpdateResult = $this->model->doEditWithField($orderData, 'restaurantordermaster', 'code', $orderCode);
		$firestoreAction = new FirestoreActions();
        $firestoreAction->update_refresh_code($resCode);
		if($orderStatus=='PND'){
			$order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster",array(), array("restaurantorderstatusmaster.statusSName" => array("=","PLC")));
			if ($order_status && count($order_status)>0) {
				$order_status_record = $order_status[0];
				$statusTitle = $order_status_record->messageTitle;
				#replace $ template in title 
				$statusDescription = $order_status_record->messageDescription;
				$statusDescription = str_replace("$", $orderCode, $statusDescription);
				$dataStatusChangeLine = array(
					"orderCode" => $orderCode,
					"statusPutCode" => Auth::guard('admin')->user()->code,
					"statusLine" => 'PLC',
					"reason" => 'Delivery boy is assigned to order',
					"statusTime" => date("Y-m-d H:i:s"),
					"statusTitle"=>$statusTitle,
					"statusDescription"=>$statusDescription,
					"isActive" => 1
				);
				$bookLineResult = $this->model->addNew($dataStatusChangeLine, 'bookorderstatuslineentries', 'BOL');
			}
		}
		//assign order status 0 to previous delivery boy
		$dataUpCnt['orderCount'] = 0;
		$dataUpCnt['orderCode'] = null;
		$dataUpCnt['orderType'] = null;
		$dataUpCnt['editDate'] = date('Y-m-d H:i:s');
		$dataUpCnt['editIP'] = $_SERVER['REMOTE_ADDR'];
		$fromDeliveryBoyResult = $this->model->doEditWithField($dataUpCnt, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $fromDeliveryBoy);
		
		//assign order status 1 to new delivery boy
		$dataUpCnt['orderCount'] = 1;
		$dataUpCnt['orderCode'] = $orderCode;
		$dataUpCnt['orderType'] = $orderType;
		$dataUpCnt['editDate'] = date('Y-m-d H:i:s');
		$dataUpCnt['editIP'] = $_SERVER['REMOTE_ADDR'];
		$toDeliveryBoyResult = $this->model->doEditWithField($dataUpCnt, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $toDeliveryBoy);	
		if ($toDeliveryBoyResult) {
			//also transfer touch point amount
			if($orderStatus=="PRE" || $orderStatus=="RFP" || $orderStatus=="PUP" || $orderStatus=="RCH"){
				$tableName='deliveryboyearncommission';
				$transData["deliveryBoyCode"]=array("=",$toDeliveryBoy);
				$this->model->doEditWithField($transData,$tableName, 'orderCode', $orderCode);
			}
			
			//send notification to the delivery boy
			$random = rand(0, 999);
			$dataNoti = array("title" => 'Transfer Order!', "message" => 'You have new transfered order', "order_id" => $orderCode, "random_id" => $random, 'type' => 'order');
			$delBoy = $this->model->selectQuery(array("usermaster.firebase_id"), "usermaster",array(), array("usermaster.code" => array("=",$toDeliveryBoy)));
			if ($delBoy) {
				$firebaseId = $delBoy[0]->firebase_id;
				if ($firebaseId != "" && $firebaseId != null){
					$DeviceIdsArr = [$firebaseId];
					$dataArr = array();
					$dataArr['device_id'] = $DeviceIdsArr;
					$dataArr['message'] = $dataNoti['message']; //Message which you want to send
					$dataArr['title'] = $dataNoti['title'];
					$dataArr['order_id'] = $dataNoti['order_id'];
					$dataArr['random_id'] = $dataNoti['random_id'];
					$dataArr['type'] = $dataNoti['type'];
					$notification['device_id'] = $DeviceIdsArr;
					$notification['message'] = $dataNoti['message']; //Message which you want to send
					$notification['title'] = $dataNoti['title'];
					$notification['order_id'] = $dataNoti['order_id'];
					$notification['random_id'] = $dataNoti['random_id'];
					$notification['type'] = $dataNoti['type'];
					$noti = new Notificationlibv_3;
					$result = $noti->sendNotification($dataArr, $notification);
					//$notify = $this->notificationlibv_3->sendDeliveryNotification($dataArr, $notification,"ringing");
				}
			}
			
			//send notification to restaurant
			
			$randomRes = rand(0, 999);
			$dataNotiRes = array("title" => 'New Order!', "message" => 'You have new order', "order_id" => $orderCode, "random_id" => $random, 'type' => 'order');
			$getResResult = $this->model->selectQuery(array("restaurant.firebaseId"), "restaurant",array(), array("restaurant.code" => array("=",$resCode)));
			if ($getResResult) {
				$resfirebaseId = $getResResult[0]->firebaseId;
				if ($resfirebaseId != "" && $resfirebaseId != null){
					$resDeviceIdsArr = [$resfirebaseId];
					$resdataArr = array();
					$resdataArr['device_id'] = $resDeviceIdsArr;
					$resdataArr['message'] = $resdataNoti['message']; //Message which you want to send
					$resdataArr['title'] = $resdataNoti['title'];
					$resdataArr['order_id'] = $resdataNoti['order_id'];
					$resdataArr['random_id'] = $resdataNoti['random_id'];
					$resdataArr['type'] = $resdataNoti['type'];
					$resnotification['device_id'] = $resDeviceIdsArr;
					$resnotification['message'] = $resdataNoti['message']; //Message which you want to send
					$resnotification['title'] = $resdataNoti['title'];
					$resnotification['order_id'] = $resdataNoti['order_id'];
					$resnotification['random_id'] = $resdataNoti['random_id'];
					$resnotification['type'] = $resdataNoti['type'];
					$resnoti = new Notificationlibv_3;
					$result = $resnoti->sendNotification($resDeviceIdsArr, $resnotification);
				} 
			}
			$response["status"]=true;
			$response["message"]="Order Successfully Transfered to another delivery boy";
		}else{
			$response["status"]=false;
			$response["message"]="Failed to transfer order";
		}
		echo json_encode($response);
	}
	
	public function getOrderStatusList(Request $req){
		$dataCount = 0;
		$data = array();
		$cnt =0;
		$orderCode = $req->orderCode;
		//DB::enableQueryLog();
		$orderColumns= array("restaurantorderstatusmaster.statusName","bookorderstatuslineentries.statusPutCode","bookorderstatuslineentries.reason","bookorderstatuslineentries.statusTime");
		$condition["bookorderstatuslineentries.orderCode"] = array("=",$orderCode);
		$orderBy["bookorderstatuslineentries.id"]="DESC";
		$join["restaurantorderstatusmaster"] = array("bookorderstatuslineentries.statusLine","restaurantorderstatusmaster.statusSName");
		$joinType["restaurantorderstatusmaster"]="inner";
		$result = $this->model->selectQuery($orderColumns,"bookorderstatuslineentries",$join,$condition,$orderBy,array(),"","","",$joinType);
	//	$query_1 = DB::getQueryLog();
       // print_r($query_1);
		if($result){
			foreach($result as $r){
				$cnt++;
				$statusPutCode = $r->statusPutCode;
				$name =""; 
				$firstTwoCharacters = substr($statusPutCode, 0, 3);
				if($firstTwoCharacters == "CLN"){
					$q = $this->model->selectQuery(array("clientmaster.name"),"clientmaster",array(),array("clientmaster.code"=>array("=",$statusPutCode)));
					if($q && count($q)>0){
						$name = $q[0]->name;
					}
				} else if($firstTwoCharacters == "USR"){
					$q = $this->model->selectQuery(array("usermaster.name"),"usermaster",array(),array("usermaster.code"=>array("=",$statusPutCode)));
					if($q && count($q)>0){
						$name = $q[0]->name;
					}
				} else {
					$q = $this->model->selectQuery(array("restaurant.entityName"),"restaurant",array(),array("restaurant.code"=>array("=",$statusPutCode)));
					if($q && count($q)>0){
						$name = $q[0]->entityName;
					}
				}				
				$ar = array(
					$cnt,
					$r->statusName,
					date("d-M-Y h:i A",strtotime($r->statusTime)),
					$name,
					$r->reason
				);
				$data[] = $ar;	
			}
			$dataCount = sizeof($this->model->selectQuery($orderColumns, "bookorderstatuslineentries", $join,$condition, $orderBy,array(),"","","", $joinType));	
		} 
		$output = array("draw" => intval($_GET["draw"]), "recordsTotal" => $dataCount, "recordsFiltered" => $dataCount, "data" => $data);
		echo json_encode($output);
	}
	public function checkDeliveryBoyOrders(Request $r) {
        $orderCode = $r->code; 
		$Result = $this->model->selectDataByCode('restaurantordermaster',$orderCode);
		$response['status']=false;
		if($Result){
			$res = $Result->deliveryBoyCode;
			if ($res=="") {
				$response['status'] = false;
			} else { 
				$orderResult = DB::table('deliveryBoyActiveOrder')
							->selectRaw('ifnull(count(id),0) as cnt')
							->where('orderCode', $orderCode)
							->where('deliveryBoyCode', $res)
							->first();
				if ($orderResult) {
					$response['status'] = true;
					$response['dbCode'] = $res;
				}else{
					$response['status'] = false;
				}
			}
		}
		echo json_encode($response);
    }
	
	public function expiredByAdmin(Request $r){
		$isExpired = $r->isExpired;
		$orderCode = $r->orderCode;
		$dbCode = $r->dbCode;
		if($dbCode!=""){
			$orderResult = DB::table('deliveryBoyActiveOrder')
							->select('orderCode')
							->where('deliveryBoyCode', $dbCode)
							->first();
			if($orderResult){
				if($orderResult->orderCode==$orderCode) {
                     //order make free from dBoy
					 $data['deliveryBoyCode']=null;
					 $OrderResult = $this->model->doEditWithField($data, 'restaurantordermaster', 'code', $orderCode);
					
					//assign order status 0 to previous delivery boy 
					$dataUpCnt['orderCount'] = 0;
					$dataUpCnt['orderCode'] = null;
					$dataUpCnt['orderType'] = null;
					$dataUpCnt['editDate'] = date('Y-m-d H:i:s');
					$dataUpCnt['editIP'] = $_SERVER['REMOTE_ADDR'];
					$fromDeliveryBoyResult = $this->model->doEditWithField($dataUpCnt, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $dbCode);
				}
			}
		}
		$data = array('isExpired' => $isExpired,
					  'isDelete' =>1,	
		              'isActive' =>0 					  
					 );
		$Records = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
		if ($Records) {	
			$response['status'] = true;
			$response['message'] = "successfully Changed Expired Status!";
		} else {
			$response['status'] = false;
			$response['message'] = "Failed to Change Expired Status!";
		}
		echo json_encode($response);
	}
	
	public function cancelOrderList()
	{
		$data['orderList'] = $this->model->selectActiveDataFromTable('restaurantordermaster');
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['deliveryboy'] = $this->model->selectQuery(array('usermaster.code','usermaster.name'),'usermaster',array(),array("usermaster.isActive"=>array("=",1),"usermaster.role"=>array("=","DBOY")));
		return view('admin.order.cancelOrderList',$data);
	}
	
	public function getcancelorderlist(Request $req)
	{
		$search = $req->input('search.value');
		$orderCode = $req->orderCode;
        $restaurantCode = $req->restaurantCode;
		$deliveryBoyCode = $req->deliveryBoyCode;
		$fromDate = $req->fromDate;
		$toDate = $req->toDate;
		$datw="";
		if ($fromDate != '') {
			$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
			$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		 $datw=" AND restaurantordermaster.editDate between '".$startDate."' And '".$endDate."'";
		}
        $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","usermaster.name as deliveryboy","clientmaster.name","clientmaster.mobile","restaurant.entityName","restaurantorderstatusmaster.statusSName","restaurantordermaster.code");
		//$condition = array("restaurantordermaster.paymentStatus" => array("=","PID"),"restaurantordermaster.restaurantCode" => array("=",$restaurantCode), "restaurantordermaster.deliveryBoyCode" => array("=",$deliveryBoyCode),  "restaurantordermaster.code" => array("=",$orderCode));
        $condition = array("restaurantordermaster.restaurantCode" => array("=",$restaurantCode), "restaurantordermaster.deliveryBoyCode" => array("=",$deliveryBoyCode),  "restaurantordermaster.code" => array("=",$orderCode));
        $orderBy = array('restaurantordermaster.id' => 'DESC');
		if ($deliveryBoyCode != "") {
			$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'inner', 'restaurantorderstatusmaster' => 'inner');
			$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
		} else {
			$joinType = array('clientmaster' => 'inner', 'restaurant' => 'inner', 'usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner');
			$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'), 'restaurant' => array('restaurant.code','restaurantordermaster.restaurantCode'), 'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'), 'restaurantorderstatusmaster' => array('restaurantorderstatusmaster.statusSName','restaurantordermaster.orderStatus'));
		}
        $like = array('restaurant.entityName' => $search, 'restaurantordermaster.code' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = " restaurantordermaster.orderStatus = 'CAN' OR restaurantordermaster.orderStatus = 'RJT' AND (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL)".$datw;	 
		//DB::enableQueryLog();
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			$statusTime = $row->addDate;
			$recordsLineStatus = $this->model->selectQuery(array("bookorderstatuslineentries.addDate as orderaddDate","bookorderstatuslineentries.statusTime"), "bookorderstatuslineentries", array(),array("bookorderstatuslineentries.orderCode" => array("=",$row->code)), array(), '', 1);
			if (!empty($recordsLineStatus)) {
				$statusTime = $recordsLineStatus[0]->statusTime;
			}
		    $orderDate = date('d-m-Y h:i:s', strtotime($row->addDate));
				$orderStatus = $row->orderStatus;
				$odStatus = $row->orderStatus;
				switch ($orderStatus) {
					case "CAN":
						$orderStatus = "Cancelled By User";
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
					case "RJT":
						$orderStatus = "Rejected";
						$chkRJT = 'checked';
						$orderDate = date('d-m-Y h:i A', strtotime($statusTime));
						break;
				}
				$paymentMode = "<span class='label label-sm label-success'>".$row->paymentMode."</span>";
				$actionHtml = '  <a class="btn btn-primary" href="' . url("order/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>';
				$data[] = array(
					$srno,
					$row->code.'<br>'.$paymentMode,
					$row->name,
					$row->entityName,
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
		$dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition,$joinType));
	}
	$output = array(
		"draw" => intval($_GET["draw"]),
		"recordsTotal" => $dataCount,
		"recordsFiltered" => $dataCount,
		"data" => $data
	);
	echo json_encode($output);
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
		$Records = $this->model->selectQuery($orderColumns, $tableName, $join,$condition, $orderBy, array(),'','', $extraCondition,$joinType);
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
									
										<li>'.$ad->subCategoryTitle.' - '.$ad->price.' Rs. </li>
									
								</ul>';
							}
						}
					}
				}
				$start = '<div class="d-flex align-items-center">';
				$end = ' <h4 class="m-b-0 font-16 font-medium"><b>' . $row->itemName . '</b></h4></div></div>';
				$itemPhotoCheck = $row->itemPhoto;
				if ($itemPhotoCheck != "") {
					$itemPhoto = env('IMG_URL').'uploads/restaurant/restaurantitemimage/' . $row->restaurantCode .'/'. $itemPhotoCheck;
					$photo = '<div class="m-r-10"><img src="' . $itemPhoto . '?' . time() . '" alt="user" class="circle" width="45"></div><div class="">';
					$itemName = $start . $photo . $end;
					
				}
				 $srno++;
				$lineData .= '<tr>
				<td colspan="2"><div align="center">' . $srno  . '</div></td>
				<td colspan="2"><div><h6><b>' . $row->itemName .'</b></h6><br>'.$addonText. '</div></td>
				<td colspan="2"><div align="center">' . $row->salePrice . '</div></td>
				<td colspan="2"><div align="center">' . $row->quantity . '</div></td>
				<td colspan="2"><div align="center">' . $row->priceWithQuantity . '</div></td>
			   </tr>';
			  
			}
		  }
		}
        $data['lineData'] = $lineData;
	    $data['query'] = $Records;		
		$data['company'] = $this->model->selectAllDataFromTable('companyinfo');
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
		return view('admin.order.invoice',$data); 
	}
	
	public function tracking($orderCode){
		$query = $this->model->selectDataByCode('restaurantordermaster',$orderCode);
		$data['query'] = $query;
		$deliveryBoyCode = $query->deliveryBoyCode;
		$clientCode = $query->clientCode;
		$restaurantCode = $query->restaurantCode;
		$dlbquery = $this->model->selectDataByCode('usermaster',$deliveryBoyCode);
		$data['dlbName'] = $dlbquery->name;
		$data['dlbMobile'] = $dlbquery->mobile;
		$profilePhoto = $dlbquery->profilePhoto;
		$data['dlbPic'] = url('uploads/profilePhoto.jpg');
		if($profilePhoto!='' && $profilePhoto!=NULL){
			$data['dlbPic'] = url('uploads/profile/'.$profilePhoto);
		}
		$clientMaster = $this->model->selectQuery(array('clientmaster.name','clientmaster.mobile'),'clientmaster',array(),array('clientmaster.code'=>array('=',$clientCode),'clientmaster.isActive'=>array('=',1)));
		$data['clientName'] = $clientMaster[0]->name;
		$data['clientMobile'] = $clientMaster[0]->mobile;
		$clientData = $this->model->selectQuery(array('clientprofile.latitude','clientprofile.longitude'),'clientprofile',array(),array('clientprofile.clientCode'=>array('=',$clientCode),'clientprofile.isActive'=>array('=',1),'clientprofile.isSelected'=>array('=',1)));
		$data['clLatitude'] = $clientData[0]->latitude;
		$data['clLongitude'] = $clientData[0]->longitude;
		$restaurant = $this->model->selectQuery(array('restaurant.entityName','restaurant.ownerContact','restaurant.latitude','restaurant.longitude'),'restaurant',array(),array('restaurant.code'=>array('=',$restaurantCode),'restaurant.isActive'=>array('=',1)));
		$data['ResLatitude'] = $restaurant[0]->latitude;
		$data['ResLongitude'] = $restaurant[0]->longitude;
		$data['ResName'] = $restaurant[0]->entityName;
		$data['ResMobile'] = $restaurant[0]->ownerContact;
		$data['latitude']='';
		$data['longitude']='';
		$filename = 'assets/order_tracking/'. $orderCode.'.json';
		if(file_exists($filename)){
			$jsonString = file_get_contents('assets/order_tracking/'. $orderCode.'.json');
			$fileData = json_decode($jsonString, true);
			if(!empty($fileData[0])){
				$data['latitude']=$fileData[0]['latitude'];
				$data['longitude']=$fileData[0]['longitude'];
			}
		}
		return view('admin.order.tracking',$data);
	}
}
