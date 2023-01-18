<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use DB;

class HomeController extends Controller
{
	public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
    public function welcome() {
        return view('admin.welcome');
    }

    public function dashboard() {
        return view('admin.dashboard');
    }
	
	public function getOrderCounts()
	{
		$today = date('Y-m-d');
		$totalRestaurants=$totalCustomers=$totalDeliveryBoys=$presentDeliveryBoys=$absentDeliveryBoys=0;
		$orderAssignedDeliveryBoys=$totalOrders=$todaysOrders=$pendingOrders = $placedOrders=$cancelledOrders1 =$cancelledOrders= $deliveredOrders=0;
		
		$restQuery = $this->model->countQuery("restaurant");
		if($restQuery) $totalRestaurants = $restQuery->cnt;
		
		$customerQuery = $this->model->countQuery("clientmaster");
		if($customerQuery) $totalCustomers = $customerQuery->cnt;
		
		$conditionTo['deliveryBoyActiveOrder.isActive']=1;
		$joinTo = array('deliveryBoyActiveOrder'=>array("deliveryBoyActiveOrder.deliveryBoyCode","usermaster.code"));
		$deliveryBoyQuery = $this->model->countQuery("usermaster",$conditionTo,$joinTo);
		if($deliveryBoyQuery) $totalDeliveryBoys = $deliveryBoyQuery->cnt;
		
		$condition['deliveryBoyActiveOrder.isActive']=1;
		$condition['deliveryBoyActiveOrder.loginStatus']='1';
		$join = array('deliveryBoyActiveOrder'=>array("deliveryBoyActiveOrder.deliveryBoyCode","usermaster.code"));
		$presentdeliveryBoyQuery = $this->model->countQuery("usermaster",$condition,$join);
		if($presentdeliveryBoyQuery) $presentDeliveryBoys = $presentdeliveryBoyQuery->cnt;
		
		$condition1['deliveryBoyActiveOrder.isActive']=1;
		$condition1['deliveryBoyActiveOrder.loginStatus']='0';
		$join1 = array('deliveryBoyActiveOrder'=>array("deliveryBoyActiveOrder.deliveryBoyCode","usermaster.code"));
		$absentdeliveryBoyQuery = $this->model->countQuery("usermaster",$condition1,$join1);
		if($absentdeliveryBoyQuery) $absentDeliveryBoys = $absentdeliveryBoyQuery->cnt;
		
		$condition2['deliveryBoyActiveOrder.isActive']=1;
		$condition2['deliveryBoyActiveOrder.loginStatus']='1';
		$condition2['deliveryBoyActiveOrder.orderCount']='1';
		$join2 = array('deliveryBoyActiveOrder'=>array("deliveryBoyActiveOrder.deliveryBoyCode","usermaster.code"));
		$orderAssignedDeliveryBoyQuery = $this->model->countQuery("usermaster",$condition2,$join2);
		if($orderAssignedDeliveryBoyQuery) $orderAssignedDeliveryBoys = $orderAssignedDeliveryBoyQuery->cnt;
		
		$con['restaurantordermaster.paymentStatus']="PID";
		$orderQuery = $this->model->countQuery("restaurantordermaster",$con);
		if($orderQuery) $totalOrders = $orderQuery->cnt;
		
		$con1['restaurantordermaster.paymentStatus']="PID";
		$extraCondition = " (restaurantordermaster.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$todaysorderQuery = $this->model->countQuery("restaurantordermaster",$con1,array(),$extraCondition);
		if($todaysorderQuery) $todaysOrders = $todaysorderQuery->cnt;
		
		$condition4['restaurantordermaster.orderStatus'] = 'PND';
		$condition4['restaurantordermaster.paymentStatus'] = 'PID';
		$pendingOrderQuery = $this->model->countQuery("restaurantordermaster",$condition4);
		if($pendingOrderQuery) $pendingOrders = $pendingOrderQuery->cnt;
		
		$condition4['restaurantordermaster.orderStatus'] = 'PLC';
		$condition4['restaurantordermaster.paymentStatus'] = 'PID';
		$placedOrderQuery = $this->model->countQuery("restaurantordermaster",$condition4);
		if($placedOrderQuery) $placedOrders = $placedOrderQuery->cnt;
		
		$condition5['bookorderstatuslineentries.statusLine'] = 'DEL';
		$extraCondition1 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$deliveredOrderQuery = $this->model->countQuery("bookorderstatuslineentries",$condition5,array(),$extraCondition1);
		if ($deliveredOrderQuery) {
			$deliveredOrders = $deliveredOrderQuery->cnt;
		}
		
		$condition6['bookorderstatuslineentries.statusLine'] = 'CAN';
		$extraCondition2 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery = $this->model->countQuery("bookorderstatuslineentries",$condition6,array(),$extraCondition2);
		if ($cancelledOrderQuery) {
			$cancelledOrders = $cancelledOrderQuery->cnt;
		}
		$condition7['bookorderstatuslineentries.statusLine'] = 'RJT';
		$extraCondition3 = " (bookorderstatuslineentries.addDate between '" . $today . " 00:00:00' and '" . $today . " 23:59:59')";
		$cancelledOrderQuery1 = $this->model->countQuery("bookorderstatuslineentries",$condition7,array(),$extraCondition3);
		if ($cancelledOrderQuery1) {
			$cancelledOrders1 = $cancelledOrderQuery1->cnt;
		}
		$cancelledOrders = $cancelledOrders+$cancelledOrders1;
		$res['totalRestaurants'] = $totalRestaurants;
		$res['totalCustomers'] = $totalCustomers;
		$res['totalDeliveryBoys'] = $totalDeliveryBoys;
		$res['presentDeliveryBoys'] = $presentDeliveryBoys;
		$res['absentDeliveryBoys'] = $absentDeliveryBoys;
		$res['orderAssignedDeliveryBoys'] = $orderAssignedDeliveryBoys;
		$res['totalOrders'] = $totalOrders;
		$res['todaysOrders'] = $todaysOrders; 
		$res['pendingOrders'] = $pendingOrders;
		$res['cancelledOrders'] = $cancelledOrders;
		$res['confirmedOrders'] = $placedOrders;
		$res['deliveredOrders'] = $deliveredOrders;
		echo json_encode($res);
	}
	
	public function getOrdersGraphData()
	{
		DB::enableQueryLog();
		$resDelOrders = DB::table("restaurantordermaster")
		    ->join("restaurant", "restaurant.code", "=", "restaurantordermaster.restaurantCode")
            ->select(DB::raw('count(restaurantordermaster.id) as countDeliverOrder'),"restaurant.entityName")
            ->where('restaurantordermaster.isActive', 1)
			->where('restaurantordermaster.orderStatus', 'DEL')
			//->where('restaurantordermaster.paymentStatus','PID')
			->whereRaw('restaurantordermaster.isDelete=0 or restaurantordermaster.isDelete IS Null') 
			->groupBy('restaurantordermaster.restaurantCode')
            ->get();
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
		$data=[];
		foreach($resDelOrders as $item){
			$data['label'][] = $item->entityName;
			$data['data'][] = $item->countDeliverOrder;
            $data['color'][]= "#".substr(md5(rand()), 0, 6);
		}
		$result_array = [
          'data'  => $data
          ];
		  
         echo json_encode($result_array);
		
	}
	
	public function getBarData(){
		DB::enableQueryLog();
		$data=[];
		
		$resDelOrders = DB::table("restaurantordermaster")
		    ->join("clientmaster", "clientmaster.code", "=", "restaurantordermaster.clientCode")
            ->select(DB::raw('count(restaurantordermaster.clientCode) as countClient'),DB::raw('monthname(restaurantordermaster.editDate) as month'))
		    ->where('clientmaster.isACtive',1)
			->where('restaurantordermaster.isActive',1)
			->where('restaurantordermaster.orderStatus','DEL')
			->groupBy(DB::raw('monthname(restaurantordermaster.editDate)'))
			->orderByRaw('month(restaurantordermaster.editDate) desc')
			->limit(6)
			->get();
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
        foreach($resDelOrders as $item){
			$data['xValues'][] = $item->month;
			$data['yValues'][] = $item->countClient;
            //$data['color'][]= "#".substr(md5(rand()), 0, 6);
		}
		$result_array = [
          'data'  => $data
          ];
		  
         echo json_encode($result_array);		
		
	}
	 public function getRestaurant(Request $req)
    {
        $search = $req->input('search.value');
         $tableName = "restaurant";
        $orderColumns = array("restaurant.*", "citymaster.cityName as cityName", "entitycategory.entityCategoryName as entityCategoryName");
        $condition = array('restaurant.isDelete' => array('!=', 1));
        $orderBy = array('restaurant' . '.id' => 'DESC');
        $join = array('citymaster' => array('citymaster.code', 'restaurant.cityCode'), 'entitycategory' => array('entitycategory.code', 'restaurant.entitycategoryCode'));
        $like = array('restaurant.entityName' => $search, 'citymaster.cityName' => $search,"restaurant.ownerContact"=>$search,"restaurant.firstName"=>$search,"restaurant.lastName"=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			
			$status = '<span class="badge badge-danger"> InActive </span>';
			if ($row->isActive == 1) {
				$status = '<span class="badge badge-success">Active</span>';
			}
			
		    $actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("partner/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("partner/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item" href="' . url("partner/getRestaurantHours/" . $row->code) . '"><i class="ti-time"></i> Hours</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
				
			if($row->manualIsServiceable==1){
				$toggle = '<input type="checkbox" class="toggle" data-size="mini" id="' . $row->code . '" checked>';
			} else{
				$toggle = '<input type="checkbox" class="toggle" data-size="mini" id="' . $row->code . '">';
			}
			$data[] = array(
				$srno,
				$row->code,
				$row->firstName.' '.$row->middleName.' '.$row->lastName,
				$row->entityName,
				$row->ownerContact,
				$toggle,
				$status,
				$actionHtml
			);
			$srno++;
            }
            $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );
        echo json_encode($output);
    }
	
	public function getCustomer(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "clientmaster";
        $orderColumns = array("clientmaster.*","citymaster.cityName");
        $condition = array('clientmaster.isDelete' => array('!=', 1));
        $orderBy = array('clientmaster.id' => 'DESC');
        $joinType = array('citymaster'=>'left');
        $join = array('citymaster'=> array('clientmaster.cityCode','citymaster.code'));
        $groupByColumn = array('clientmaster.code');
        $like = array("clientmaster.name" => $search,"clientmaster.mobile" => $search ,"clientmaster.code" => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                if ($row->isActive == 1) {
                    $status = '<span class="badge badge-success">Active</span>';
                }else{
					$status = '<span class="badge badge-danger"> InActive </span>';
				}
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("customer/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("customer/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
              
                $data[] = array(
                   $srno, 
				   $row->code, 
				   $row->name, 
				   $row->cityName, 
				   $row->mobile, 
				   $row->emailId, 
				   $status,
				   $actions
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );
        echo json_encode($output);
    }
	
	public function getDeliveryBoys(Request $req)
    {
        $search = $req->input('search.value');
        $type = $req->type;
          $tableName = "usermaster";
        $orderColumns = array("usermaster.*", "designationmaster.designation as designationName", "citymaster.cityName as cityName");
        $condition = array('usermaster.isActive'=>array("=",1),"deliveryBoyActiveOrder.isActive"=>array("=",1));
        $orderBy = array('usermaster.id' => 'DESC');
        $join = array('deliveryBoyActiveOrder'=>array("deliveryBoyActiveOrder.deliveryBoyCode","usermaster.code"),'designationmaster' => array('designationmaster.code', 'usermaster.designationCode'), 'citymaster' => array('citymaster.code', 'usermaster.cityCode'));
        $like = array('usermaster.name' => $search,'usermaster.username' => $search,'usermaster.mobile' => $search, 'usermaster.userEmail' => $search,'citymaster.cityName'=>$search,'designationmaster.designation'=>$search);
		if($type==1 || $type==3){
			$condition['deliveryBoyActiveOrder.loginStatus']=array("=","1");
		}elseif($type==2){
			$condition['deliveryBoyActiveOrder.loginStatus']=array("=","0");
		}
		if($type==3){
			$condition['deliveryBoyActiveOrder.orderCount']=array("=","1");
		}
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role='';
				$status = '<span class="badge badge-danger"> InActive </span>';
				if ($row->isActive == 1) {
					$status = '<span class="badge badge-success">Active</span>';
				}
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("users/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("users/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
                $data[] = array(
                    $srno,
                    $row->name,
                    $row->designationName,
                    $row->cityName,
                    $row->mobile, 
                    $status,
					$actions
                );
                $srno++;
            }
            $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName,  $join, $condition, $orderBy, $like, '', ''));
        }
        $output = array(
            "draw" => intval($_GET["draw"]),
            "recordsTotal" => $dataCount,
            "recordsFiltered" => $dataCount,
            "data" => $data
        );
        echo json_encode($output);
    }
	
	public function getOrders(Request $req)
    {
        $search = $req->input('search.value');
        $type = $req->type;
        $tableName = "restaurantordermaster";
        $orderColumns = array("restaurantordermaster.*","usermaster.name as deliveryboy","clientmaster.name","clientmaster.mobile","restaurant.entityName","restaurantorderstatusmaster.statusSName","restaurantordermaster.code");
        $condition = array("restaurantordermaster.paymentStatus" => array("=","PID"),"restaurantordermaster.isActive"=>array("=",1));
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
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			$statusTime = $row->addDate;
			$recordsLineStatus = $this->model->selectQuery(array("bookorderstatuslineentries.addDate as orderaddDate","bookorderstatuslineentries.statusTime"), "bookorderstatuslineentries", array(),array("bookorderstatuslineentries.orderCode" => array("=",$row->code)), array(), array(), 1);
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
				$actionHtml = '  <a class="btn btn-primary" href="' . url("order/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>';
				$data[] = array(
					$srno,
					$row->code,
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
}

