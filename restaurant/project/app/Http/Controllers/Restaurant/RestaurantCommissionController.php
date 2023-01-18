<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use App\Models\RestaurantOffer;

class RestaurantCommissionController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	
	public function index()
    {
        return view('restaurant.restaurantcommission.list');
    }
	
	public function getCommissionList(Request $req){
		DB::enableQueryLog();
		$code=Auth::guard('restaurant')->user()->code;
		$fromDate = $req->fromdate;
		$toDate = $req->todate;
		$startDate = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');
		$endDate = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');
		$startDate = $startDate . " 00:00:00";
		$endDate = $endDate . " 23:59:59";
		$extraCondition ="( restaurantordercommission.addDate between '".$startDate."' And '".$endDate."')";
		$search = $req->input('search.value');
	    $tableName = "restaurantordercommission";
        $orderColumns = array("restaurantordercommission.*");
        $condition = array('restaurantordercommission.restaurantCode'=>array('=',$code));
        $orderBy = array('restaurantordercommission' . '.id' => 'DESC');
        $join = array();
        $like = array();
        $limit = $req->length;
        $offset = $req->start;
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition); 
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
		$restaurantAmount1=0;
		$html="";
        $data = array(); 
        //$query_1 = DB::getQueryLog();    
        //print_r($query_1); 
		 if($result && $result->count() > 0) {
			 foreach ($result as $row) {
				$restaurantAmount1+=$row->comissionAmount;
				if($row->isPaid==1){
					$html='<span class="label label-success">Paid</span>';	
				}
				else{
					$html='<span class="label label-warning">Not Paid</span>';		
				}
				$orderDate = date('d/m/Y h:i A', strtotime($row->addDate));
				$data[] = array($srno,$orderDate, $row->orderCode, $row->subTotal,$row->comissionPercentage.'(%)',$row->comissionAmount,$row->restaurantAmount,$html); 
				$srno++;
			 }
			 
			 $dataCount = sizeof($this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, array(), '', '',$extraCondition)); 
		 }
		 $output = array(
				"draw"			  =>     intval($_GET["draw"]),
				"recordsTotal"    =>     $dataCount,
				"recordsFiltered" =>     $dataCount,
				"restaurantAmount1"   => $restaurantAmount1,
				"data"            =>     $data
			);
			echo json_encode($output);	
		
	}

}