<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use DB;

class HomeSliderController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		$data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		return view('admin.homeslider.index',$data);
	}
    
	public function getHomeSliderList(Request $req)
	{
		//DB::enableQueryLog();
		$search = $req->input('search.value');
		$tableName = "homeslider";
		$orderColumns = array("homeslider.*","citymaster.cityName","restaurant.entityName");
		$condition = array('homeslider.isDelete' => array('=', 0));
		$orderBy = array('homeslider' . '.id' => 'DESC');
		$join = array('citymaster'=>array('citymaster.code','homeslider.cityCode'),'restaurant'=>array('restaurant.code','homeslider.restaurantCode'));
		$joinType=array('citymaster'=>'left','restaurant'=>'left');
		$like = array('homeslider.code' => $search);
		$limit = $req->length;
		$offset = $req->start;
		$extraCondition = "";
		$result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition,$joinType);
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
				$path='<b>No Image</b>';
				if($row->imagePath!="" && $row->imagePath!=NULL){
					$path = '<img style="width:50px;height:50px" src="' . url("uploads/homeslider/" .$row->imagePath) . '">';
				}
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit" style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" data-name="' . $row->id . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
					$row->cityName,
					$row->entityName,
					$path,
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
	
	public function edit(Request $r)
	{
		$code = $r->code;
		$homesliderDetails = $this->model->selectDataByCode('homeslider',$code);
		if ($homesliderDetails) {
			$data['code'] = $homesliderDetails->code;
			$data['cityCode'] = $homesliderDetails->cityCode;
			$data['restaurantCode'] = $homesliderDetails->restaurantCode;
			$data['imagePath'] = $homesliderDetails->imagePath;
			$data['isActive'] = $homesliderDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "homeslider";
		if ($r->has('code') && trim($r->code) != "") {
			$code = $r->code;
			$data = [
				'cityCode'=> $r->cityCode,
				'restaurantCode'=> $r->restaurantCode,
				'isActive' => $r->isActive == "" ? 0 : 1,
				'isDelete'=>0,
				'editIP'=>$ip,
				'editDate'=>$currentdate->toDateTimeString(),
				'editID' => Auth::guard('admin')->user()->code,
			];
			$result = $this->model->doEdit($data, $table, $r->code); 
			if ($result != false) {
				if ($r->hasFile('imagePath')) { 
					$fileImage =  $r->file('imagePath');       
					$filenameImage = $r->code.'-' . time() .'.'. $fileImage->getClientOriginalExtension();          
					$fileImage->move("uploads/homeslider",  $filenameImage);
					$imageData = ['imagePath' =>$filenameImage];
					$image_update = $this->model->doEdit($imageData, $table, $r->code);
				}
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Home Slider ".$code." is updated.";
				$this->model->activity_log($data); 
				//activity log end
				$res['status'] = 200;
				$res['msg'] = "Home slider updated successfully ";
			} else {
				$res['status'] = 300;
				$res['msg'] = "No changes were made";
			}
		} else {
			$data = [
				'cityCode'=> $r->cityCode,
				'restaurantCode'=> $r->restaurantCode,
				'isActive' => $r->isActive == "" ? 0 : 1,
				'isDelete' => 0,
				'addIP'=>$ip,
				'addDate'=>$currentdate->toDateTimeString(),
				'addID' =>Auth::guard('admin')->user()->code,
			];
			$result = $this->model->addNew($data, 'homeslider', 'HMSLD');
			if ($result) {
				if ($r->hasFile('imagePath')) { 
					$fileImage =  $r->file('imagePath');       
					$filenameImage = $result.'-' . time() .'.'. $fileImage->getClientOriginalExtension();          
					$fileImage->move("uploads/homeslider",  $filenameImage);
					$imageData = ['imagePath' =>$filenameImage];
					$image_update = $this->model->doEdit($imageData, $table, $result);
				}
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Home Slider ".$result." is added";
				$this->model->activity_log($data); 
				//activity log end
						
				$res['status'] = 200;
				$res['msg'] = "Home slider saved successfully";
			} else {
				$res['status'] = 300;
				$res['msg'] = "Failed to add home slider";
			}
		}
		return response()->json($res, 200);
	}		
	
	public function delete(Request $request)
	{
		$code = $request->code;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$today = date('Y-m-d');
		$table = 'homeslider';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Home Slider ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->doEditWithField($data, 'homeslider','code',$code);
	}
	
	public function getRestDetails(Request $request)
	{
		$cityCode = $request->cityCode;;
		$dbResCode = $request->dbResCode;
		$getRestDetails = $this->model->selectQuery(array('restaurant.code','restaurant.entityName'),'restaurant',array(),array('restaurant.isActive' => array('=', 1),'restaurant.cityCode' => array('=', $cityCode)));
		$html = "";
		if($getRestDetails){
			$html = '<option value="">Select </option>';
			foreach($getRestDetails as $sub){
				if($sub->code==$dbResCode){
					$html .= '<option selected value="' . $sub->code . '">' . $sub->entityName . '</option>';
				}else{
					$html .= '<option value="' . $sub->code . '">' . $sub->entityName . '</option>';
				}
			}
		}
		echo $html; 
	}

}

