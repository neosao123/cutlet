<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use App\Models\CustomAddressMaster;
use App\Models\Restaurants;
use App\Models\RestaurantHours;

class RatingController extends Controller
{
    public function __construct(GlobalModel $model)  
    {
        $this->model = $model;
    }
	
	public function index()
	{
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		return view('admin.rating.list',$data);
	}
	
	public function getRatingList(Request $req)
	{
		$search = $req->input('search.value');
        $restname = $req->restname;
        $tableName = "rating";
        $orderColumns = array("rating.*", "clientmaster.name as clientName", "restaurant.entityName as restaurantName");
        $condition = array('restaurant.isDelete' => array('!=', 1),'restaurant.code'=>array('=',$restname));
        $orderBy = array('rating' . '.id' => 'DESC');
        $join = array('clientmaster' => array('clientmaster.code', 'rating.clientCode'), 'restaurant' => array('restaurant.code', 'rating.restaurantCode'));
        $like = array('restaurant.entityName' => $search, 'clientmaster.name' => $search,"rating.orderCode"=>$search,"rating.rating"=>$search,"rating.review"=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			$status = '';
			if ($row->isAccept == 1) {
				$status = '<span class="badge badge-success">Accept</span>';
			}else if($row->isAccept == 2){
				$status = '<span class="badge badge-danger">Reject</span>';
			}else{
				$status = '<span class="badge badge-info">Pending</span>';
			}
		    $actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">';
							
			$actionHtml .= '<a class="dropdown-item accept" style="cursor: pointer;" data-type="1" data-id="' . $row->id . '" id="' . $row->id . '"><i class="ti-pencil-alt"></i>Accept</a>
								<a class="dropdown-item reject" style="cursor: pointer;" data-type="0" data-id="' . $row->id . '" id="' . $row->id . '"><i class="ti-pencil-alt"></i>Reject</a>
							</div>
						</div>';
			$data[] = array(
				$srno,
				$row->orderCode,
				$row->clientName,
				$row->restaurantName,
				$row->rating,
				$row->review,
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
	
	public function changestatus(Request $request)
	{
		$id = $request->id;
		$type = $request->type;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$today = date('Y-m-d');
		$table = 'rating';
		if($type==1)
		{
			$data = ['isAccept' => 1]; 
			echo $this->model->doEditWithID($data, $table, $id);
		}else{
		   $data = ['isAccept' => 2]; 
		   echo $this->model->doEditWithID($data, $table, $id);
		} 
	}
	
}
