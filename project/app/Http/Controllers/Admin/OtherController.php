<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use App\Models\Users;
use App\Models\ResStatusLineEntry;
use Carbon\Carbon;
use DB;

class OtherController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function deliveryBoyCommissionList()
	{
		$data['user'] = $this->model->selectQuery(array('usermaster.code','usermaster.name','usermaster.mobile'),'usermaster',array(),array('usermaster.role'=>array('=','DBOY'),'usermaster.isActive'=>array("=",1)));
		return view('admin.other.deliveryBoyCommissionList',$data);
	}
	
	public function restaurantCommissionList()
	{
		$data['restaurant'] = $this->model->selectQuery(array('restaurant.code','restaurant.entityName'),'restaurant',array(),array('restaurant.isActive'=>array("=",1)));
		return view('admin.other.restaurantCommissionList',$data);
	}
    
	public function getDeliveryBoyCommissionList(Request $req)
	{
		$userCode = $req->deliveryboyCode; 
		$dateSearch = $req->date; 
		$tableName = 'deliveryboyearncommission';
		$orderColumns = array(DB::raw("sum(deliveryboyearncommission.commissionAmount) as totalCommissionAmount"),"deliveryboyearncommission.orderType",DB::raw("sum(deliveryboyearncommission.orderAmount) as totalOrderAmount"),"deliveryboyearncommission.orderCode", "usermaster.name","deliveryboyearncommission.deliveryBoyCode");		
		$condition = array('usermaster.isActive'=>array("=",1),'deliveryboyearncommission.deliveryBoyCode' => array("=",$userCode),'deliveryboyearncommission.commissionType'=>array("!=",'penalty'));
		$orderBy = array('usermaster.id' => 'DESC');
		$joinType = array('usermaster' => 'inner'); 
		$join = array('usermaster' => array('deliveryboyearncommission.deliveryBoyCode','usermaster.code'));
		$groupByColumn = array("deliveryboyearncommission.deliveryBoyCode");
		$limit = $req->length;
		$offset = $req->start;
		$extraCondition='';
		if($dateSearch!=""){
			$date = date('Y-m-d',strtotime($dateSearch));
			$extraCondition = " date(deliveryboyearncommission.addDate)= '".$date."'";       
		}
		$like = array();
		$Records = $this->model->selectQuery($orderColumns, $tableName, $join,$condition, $orderBy,  $like, $limit, $offset, $extraCondition,$joinType,$groupByColumn);
		 //$query_1 = DB::getQueryLog();
        //print_r($query_1);
		$srno = $_GET['start'] + 1;
		$data = array();
		if (!empty($Records) && count($Records)>0) {
			foreach ($Records as $row) { 
				$recAmount = $row->totalOrderAmount - $row->totalCommissionAmount;
                 $actionHtml='<a class="btn btn-primary blue" data-toggle="modal" data-target="#responsive-modal" data-ordertype="'.$row->orderType.'" data-order="'.$row->orderCode.'"  data-seq="'.$row->deliveryBoyCode.'" href><i class="ti-eye"></i> Open</a>';				
				$data[] = array($srno,$row->name,$row->totalOrderAmount,$recAmount,$row->totalCommissionAmount,$actionHtml); 
				$srno++;
			}
			$dataCount =  sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition));
		} else {
			$dataCount = 0;
		}		
		$output = array(
			"draw" => intval($_GET["draw"]),
			"recordsTotal" => $dataCount,
			"recordsFiltered" => $dataCount,
			"data" => $data
		);
		echo json_encode($output);
	}
	
	public function viewCurrentHistory(Request $req){
		$dbcode = $req->code;
		$dateSearch = $req->date;
		//DB::enableQueryLog();
		$date = date('Y-m-d',strtotime($dateSearch));
		$orderColumns = array('deliveryboyearncommission.*','restaurantordermaster.grandTotal as totalPrice');
		$orderBy = array('restaurantordermaster' . '.id' => 'DESC');
		$joinType = array('restaurantordermaster' => 'inner',);  
		$join = array('restaurantordermaster' => array('deliveryboyearncommission.orderCode','restaurantordermaster.code'));
		$extraCondition = " date(deliveryboyearncommission.addDate)='".$date."'";  
		$groupByColumn = array();
		$like = array();
		$array = array('deliveryboyearncommission.deliveryBoyCode' => array("=",$dbcode),'deliveryboyearncommission.isActive' => array("=",1));
		$Records = $this->model->selectQuery($orderColumns,'deliveryboyearncommission', $join,$array, $orderBy, $like, '', '',$extraCondition,$joinType);
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
		$data['commissionData'] = $Records;
		$data['userCode'] = $dbcode;
		$data['dateSearch'] = $date;
		return view('admin.other.unpaidDbCommission', $data);
	}
	
	public function getRestaurantCommissionList(Request $req)
	{
		$restaurantCode = $req->restaurantCode; 
		$fromDate = $req->fromDate; 
		$toDate = $req->toDate; 
		$tableName = 'restaurantordercommission';
		$orderColumns = array(DB::raw("sum(restaurantordercommission.grandTotal) as grandTotal"),DB::raw("sum(restaurantordercommission.subTotal) as subTotal"),DB::raw("sum(restaurantordercommission.comissionPercentage) as comissionPercentage"),DB::raw("sum(restaurantordercommission.comissionAmount) as comissionAmount"),DB::raw("sum(restaurantordercommission.restaurantAmount) as totalRestaurantAmount"),"restaurantordercommission.restaurantCode","restaurant.firstName","restaurant.entityName","restaurant.lastName");		
		$condition = array('restaurant.isActive'=>array("=",1),'restaurantordercommission.restaurantCode' => array("=",$restaurantCode),'restaurantordercommission.commissionType'=>array("!=",'penalty'));
		$orderBy = array('restaurant.id' => 'DESC');
		$joinType = array('restaurant' => 'inner'); 
		$join = array('restaurant' => array('restaurantordercommission.restaurantCode','restaurant.code'));
		$groupByColumn = array("restaurantordercommission.restaurantCode");
		$limit = $req->length;
		$offset = $req->start;
		$extraCondition='';
		if($fromDate!="" && $toDate!=''){
			$startDate = date('Y-m-d',strtotime($fromDate));
			$endDate = date('Y-m-d',strtotime($toDate));
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
			$extraCondition = " date(restaurantordercommission.addDate) between '".$startDate."' And '".$endDate."'";       
		}
		$like = array();
		$Records = $this->model->selectQuery($orderColumns, $tableName, $join,$condition, $orderBy,  $like, $limit, $offset, $extraCondition,$joinType,$groupByColumn);
		$srno = $_GET['start'] + 1;
		$data = array();
		$totalRestaurantAmount=0;
		if (!empty($Records) && count($Records)>0) {
			foreach ($Records as $row) { 
				 $actionHtml='<a class="btn btn-primary blue" data-toggle="modal" data-target="#responsive-modal" data-vendor="'.$row->restaurantCode.'" href><i class="ti-eye"></i> Open</a>';				
				$totalRestaurantAmount+=$row->totalRestaurantAmount;	
				$data[] = array($srno,$row->entityName,$row->grandTotal,$row->subTotal,$row->comissionAmount,$row->totalRestaurantAmount,$actionHtml);
				$srno++;
			}
			$dataCount =  sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', '',$extraCondition));
		} else {
			$dataCount = 0;
		}		
		$output = array(
			"draw" => intval($_GET["draw"]),
			"recordsTotal" => $dataCount,
			"recordsFiltered" => $dataCount,
			"data" => $data,
			"totalRestaurantAmount"=>$totalRestaurantAmount,
		);
		echo json_encode($output);
	}
	
	public function viewResCurrentHistory(Request $req)
	{
		$restaurantCode = $req->restaurantCode;
		$fromDate = $req->fromDate;
		$toDate = $req->toDate;
		if($fromDate!="" && $toDate!=''){
			$startDate = date('Y-m-d',strtotime($fromDate));
			$endDate = date('Y-m-d',strtotime($toDate));
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		}
		$condition = array('restaurantordercommission.restaurantCode' => array("=",$restaurantCode), 'restaurantordercommission.isActive' => array("=",1));
		$orderBy = array('restaurantordercommission.id' => 'DESC');
		$extraCondition = " restaurantordercommission.addDate between '".$startDate."' And '".$endDate."'";       
		$Records = $this->model->selectQuery('restaurantordercommission.*', 'restaurantordercommission',array(), $condition,$orderBy, array(),"","", $extraCondition);
		$data['commissionData'] = $Records;
		$data['restaurantCode'] = $restaurantCode;
		$data['fromDate'] = $fromDate;
		$data['toDate'] = $toDate;
		return view('admin.other.unpaidResCommission', $data);
	}

	public function saveRes(Request $req)
	{
		$restaurantCode = $req->restaurantCode;
		$fromDate = $req->fromDate;
		$toDate = $req->toDate;
		if($fromDate!="" && $toDate!=''){
			$startDate = date('Y-m-d',strtotime($fromDate));
			$endDate = date('Y-m-d',strtotime($toDate));
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		}
		$condition = array('restaurantordercommission.restaurantCode' => array("=",$restaurantCode),'restaurantordercommission.isActive' => array("=",1));
		$orderBy = array('restaurantordercommission.id' => 'DESC');
		$extraCondition = " (`restaurantordercommission`.`isPaid` =0 or `restaurantordercommission`.`isPaid` IS NULL) and restaurantordercommission.addDate between '".$startDate."' And '".$endDate."'";       
		$Records = $this->model->selectQuery(array('restaurantordercommission.code'), 'restaurantordercommission',array(), $condition,$orderBy, array(),"","", $extraCondition);
		if(!empty($Records) && count($Records)>0){
			foreach($Records as $res){
				$code=$res->code;
				$updateData['restaurantordercommission.isPaid']=1;
				$this->model->doEdit($updateData,"restaurantordercommission",$code);
			}
			echo 'true';
		}else {
	     	echo 'false';
		}		
	}
	
	public function save(Request $r)
	{
		$dbcode = $r->code;
		$dateSearch = $r->dateSearch;
		$date = date('Y-m-d',strtotime(str_replace('/','-',$dateSearch)));
		$condition = array('deliveryboyearncommission.deliveryBoyCode' => array("=",$dbcode),'deliveryboyearncommission.isActive' => array("=",1));
		$orderBy = array('deliveryboyearncommission.id' => 'DESC');
		$extraCondition = " (`deliveryboyearncommission`.`isPaid` =0 or `deliveryboyearncommission`.`isPaid` IS NULL) and date(deliveryboyearncommission.addDate) ='".$date."'";       
		$Records = $this->model->selectQuery(array('deliveryboyearncommission.code'), 'deliveryboyearncommission',array(), $condition,$orderBy, array(),"","", $extraCondition);
		if(!empty($Records) && count($Records)>0){
			foreach($Records as $res){
				$code=$res->code;
				$updateData['deliveryboyearncommission.isPaid']=1;
				$this->model->doEdit($updateData,"deliveryboyearncommission",$code);
			}
			echo 'true';
		}else {
	     	echo 'false';
		}		
	}
	
	public function orderReport()
	{
	    $data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
	    $data['statusmaster'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['ordermaster'] =$this->model->selectActiveDataFromTable('restaurantordermaster');
		$data['customer'] = $this->model->selectActiveDataFromTable('clientmaster');
		return view('admin.other.orderReportList',$data);
	}
	
	public function getOrderReportList(Request $req)
	{
		$restaurant = $req->restaurant;
		$orderStatus = $req->statusCode;
		$ordercode = $req->ordercode;
		$fromDate = $req->fromdate;
		$toDate = $req->todate;
		$customer = $req->customer;
		$datw="";
		if ($fromDate != '') {
			//$startDate = Carbon::createFromFormat('d/m/Y', $fromDate)->format('Y-m-d');
			///$endDate = Carbon::createFromFormat('d/m/Y', $toDate)->format('Y-m-d');
			$startDate = date('Y-m-d', strtotime($fromDate));
			$endDate = date('Y-m-d', strtotime($toDate));
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		 $datw=" and ( restaurantordermaster.addDate between '".$startDate."' And '".$endDate."')";
		}
		$search = $req->input('search.value');
	    $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","restaurant.entityName","clientmaster.name","clientmaster.mobile");
        $condition = array('restaurantordermaster.restaurantCode'=>array('=',$restaurant),'restaurantordermaster.code'=>array('=',$ordercode),'restaurantordermaster.paymentStatus'=>array('=','PID'),'restaurantordermaster.clientCode' => array('=', $customer));
        $orderBy = array('restaurantordermaster' . '.id' => 'DESC');
        $join = array('restaurant'=>array('restaurant.code','restaurantordermaster.restaurantCode'),'clientmaster' => array('clientmaster.code', 'restaurantordermaster.clientCode'));
        $like = array('clientmaster.name' => $search, 'restaurantordermaster.restaurantCode' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "(restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null)".$datw;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition); 
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
        if($result && $result->count() > 0) {
			 foreach ($result as $row) {
				$sTime = ResStatusLineEntry::select("bookorderstatuslineentries.*")
									->where("bookorderstatuslineentries.orderCode",$row->code)
									->first();
				if(!empty($sTime)){
					$statusTime = $sTime->statusTime;
				}
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
								<a class="dropdown-item" href="' .url("other/viewOrder/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' .url("order/invoice/" . $row->code) . '"><i class="ti-notepad"></i> Invoice</a>
							</div>
						</div>';
							$data[] = array(
								$srno,
								$row->entityName,
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
	   
	 public function viewOrder($code)
	{
		$data['discountInPercent'] = 0;
		$data['orderData'] = false;
		$tableName = "restaurantordermaster";
		$orderColumns = array("restaurantordermaster.*","clientmaster.name as clientName","clientmaster.mobile","usermaster.name as DeliveryBoyName","clientmaster.cityCode");
		$condition = array('restaurantordermaster.code' => array("=",$code));
		$orderBy = array('restaurantordermaster.id' => 'DESC');
		$joinType = array('clientmaster' => 'inner','usermaster'=>'left');
		$join = array('clientmaster' => array('clientmaster.code','restaurantordermaster.clientCode'),'usermaster' => array('usermaster.code','restaurantordermaster.deliveryBoyCode'));
		$extraCondition = " (restaurantordermaster.isDelete=0 OR restaurantordermaster.isDelete IS NULL)";
		$like = array();
		$Records = $this->model->selectQuery($orderColumns, $tableName, $join,$condition, $orderBy, array(),'','', $extraCondition,$joinType);
		$data['query'] = false;
		if ($Records) {
			$data['query'] = $Records;
			$couponCode = $Records[0]->couponCode;
			$restaurantCode = $Records[0]->restaurantCode;
			$dataCC = $this->model->selectQuery(array('restaurantoffer.discount'), 'restaurantoffer',array(), array('restaurantoffer.couponCode' => array("=",$couponCode), 'restaurantoffer.restaurantCode' => array("=",$restaurantCode)));
			if ($dataCC) {
				$data['discountInPercent'] = $dataCC[0]->discount;
			}
		} 
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
        return view('admin.other.orderReportView', $data);  
	}

}
