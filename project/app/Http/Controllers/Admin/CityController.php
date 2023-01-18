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

class CityController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.city.index');
	}
    
	public function getCityList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "citymaster";
		$orderColumns = array("citymaster.*");
		$condition = array('citymaster.isDelete' => array('=', 0));
		$orderBy = array('citymaster' . '.id' => 'DESC');
		$join = array();
		$like = array('citymaster.cityName' => $search);
		$limit = $req->length;
		$offset = $req->start;
		$extraCondition = "";
		//DB::enableQueryLog();
		
		$result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
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
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit"  style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code. '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
					$row->cityName,
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
		$cityDetails = $this->model->selectDataByCode('citymaster',$code);
		if ($cityDetails) {
			$data['code'] = $cityDetails->code;
			$data['cityName'] = $cityDetails->cityName;
			$data['isActive'] = $cityDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "citymaster";
		$rules = array(
			'id' => 'nullable',
			'cityName' => [
				'required',
				'regex:/^[a-zA-Z\s]+$/',
				'min:3',
				'max:80'
			],
		);
		$messages = array(
			'cityName.required' => 'City name is required',
			'cityName.regex' => 'Invalid characters like number, special characters are not allowed',
			'cityName.min' => 'Minimum of 3 characters are required.',
			'cityName.max' => 'Max characters exceeded.',
		);
		$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 200);
		} else {
			$cityName =  ucwords($r->cityName);
			if ($r->has('code') && trim($r->code) != "") {
				$code = $r->code;
				$where[] = ["citymaster.cityName", "=", $cityName];
				$where[] = ["citymaster.code", "!=", $code];
				$duplicate = $this->model->checkForDuplicate($table, "citymaster", $where);
				if (!$duplicate) {
					$data = [
						'cityName' => $cityName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete'=>0,
						'editIP'=>$ip,
						'editDate'=>$currentdate->toDateTimeString(),
						'editID' => Auth::guard('admin')->user()->code,
					];
					$result = $this->model->doEdit($data, $table, $r->code); 
					if ($result != false) {
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."City ".$code." is updated.";
						$this->model->activity_log($data); 
						//activity log end
						
						$res['status'] = 200;
						$res['msg'] = "City updated successfully ";
					} else {
						$res['status'] = 300;
						$res['msg'] = "No changes were made";
					}
				} else {
					$res['status'] = 400;
					$res['msg'] = "Duplicate records are not allowed";
				}
			} else {
				$where[] = ["citymaster.cityName", "=", $cityName];
				$duplicate = $this->model->checkForDuplicate($table, "citymaster", $where);
				if (!$duplicate) {
					$data = [
						'cityName' => $cityName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete' => 0,
						'addIP'=>$ip,
						'addDate'=>$currentdate->toDateTimeString(),
						'addID' =>Auth::guard('admin')->user()->code,
					];
					$result = $this->model->addNew($data, 'citymaster', 'CTY');
					if ($result) {
						
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."City ".$result." is added";
						$this->model->activity_log($data); 
						//activity log end
						
						$slotdata = [
							'cityCode' => $result,
							'deliveryCharges'=>20,
							'isActive' => 1,
							'isDelete' => 0,
							'addIP'=>$ip,
							'addDate'=>$currentdate->toDateTimeString(),
							'addID' =>Auth::guard('admin')->user()->code,
						];
						$result = $this->model->addNew($slotdata, 'deliverychargesslot', 'DCS');
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Slot ".$result." is added";
						$this->model->activity_log($data); 
						//activity log end
						
						$res['status'] = 200;
						$res['msg'] = "City saved successfully";
					} else {
						$res['status'] = 300;
						$res['msg'] = "Failed to add designation";
					}
				} else {
					$res['status'] = 400;
					$res['msg'] = "Duplicate records are not allowed";
				}
			}
			return response()->json($res, 200);
		}
	}		
	
	public function delete(Request $request)
	{
		$code = $request->code;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$today = date('Y-m-d');
		$table = 'citymaster';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."City ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->doEditWithField($data, 'citymaster','code',$code);
	}

}
