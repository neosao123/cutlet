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
use App\Models\ResStatusLineEntry;
use App\Models\Users;
use App\Models\Settings;
use DB;
use App\Classes\Notificationlibv_3;
use App\Models\ClientDevicesDetails;

class RestaurantOrderReportController extends Controller
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
		$data['customer'] = RestaurantOrder::select(DB::raw('DISTINCT restaurantordermaster.clientCode'),'clientmaster.name')
		                    ->join("clientmaster", "clientmaster.code", "=", "restaurantordermaster.clientCode")
		                    ->where('restaurantordermaster.isActive', 1)
							->where('restaurantCode',$code)->get();
		return view('restaurant.orderreport.list',$data);
	}
	
	public function getRestaurantOrderReportList(Request $req)
	{
		DB::enableQueryLog();
		$code=Auth::guard('restaurant')->user()->code;
		$orderStatus = $req->status;
		$ordercode = $req->ordercode;
		$fromDate = $req->fromdate;
		$toDate = $req->todate;
		$customer = $req->customer;
		$datw="";
		if ($fromDate != '') {
			$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
			$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
			$startDate = $startDate . " 00:00:00";
			$endDate = $endDate . " 23:59:59";
		 $datw="and ( restaurantordermaster.addDate between '".$startDate."' And '".$endDate."')";
		}
		$search = $req->input('search.value');
	    $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","clientmaster.name","clientmaster.mobile");
        $condition = array('restaurantordermaster.code'=>array('=',$ordercode),'restaurantordermaster.restaurantCode' => array('=', $code),'restaurantordermaster.clientCode' => array('=', $customer));
        $orderBy = array('restaurantordermaster' . '.id' => 'DESC');
        $join = array('clientmaster' => array('clientmaster.code', 'restaurantordermaster.clientCode'));
        $like = array('clientmaster.name' => $search, 'restaurantordermaster.restaurantCode' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "(restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null)".$datw;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition); 
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
        //$query_1 = DB::getQueryLog();    
        //print_r($query_1);
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
								<a class="dropdown-item" href="' .url("orderreport/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' .url("orderreport/invoice/" . $row->code) . '"><i class="ti-notepad"></i> Invoice</a>
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
			if ($dataCC) {
				$data['discountInPercent'] = $dataCC[0]->discount;
			}
		} 
		$data['orderStatus'] = $this->model->selectAllDataFromTable('restaurantorderstatusmaster');
		$data['paymentStatus'] = $this->model->selectAllDataFromTable('paymentstatusmaster');
        return view('restaurant.orderreport.view', $data);  
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
			if ($dataCC) {
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
					$row->addonsCode = rtrim($row->addonsCode,',');
					$savedaddonsCodes = explode(',',$row->addonsCode);
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
									<li><b>'.$ad->categoryTitle.' - '.ucfirst($ad->categoryType).
									'</b><ul>
										<li>'.$ad->subCategoryTitle.' - '.$ad->price.'</li>
									</ul>
							           </li>
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
				$lineData .= '<tr>
				<td colspan="2"><div align="center">' . $srno  . '</div></td>
				<td colspan="2"><div align="center">' . $row->itemName .'<br>'.$addonText. '</div></td>
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
					$row->addonsCode = rtrim($row->addonsCode,',');
					$savedaddonsCodes = explode(',',$row->addonsCode);
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
									<li><b>'.$ad->categoryTitle.' - '.ucfirst($ad->categoryType).
									'</b><ul>
										<li>'.$ad->subCategoryTitle.' - '.$ad->price.'</li>
									</ul>
							           </li>
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
	

	
}