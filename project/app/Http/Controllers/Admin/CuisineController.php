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

class CuisineController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.cuisine.index');
	}
    
	public function getCuisineList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "cuisinemaster";
		$orderColumns = array("cuisinemaster.*");
		$condition = array('cuisinemaster.isDelete' => array('=', 0));
		$orderBy = array('cuisinemaster' . '.id' => 'DESC');
		$join = array();
		$like = array('cuisinemaster.cuisineName' => $search);
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
				$path='<b>No Image</b>';
				if($row->cuisinePhoto!="" && $row->cuisinePhoto!=NULL){
					//$path = 'uploads/restaurant/cuisine/'.$row->cuisinePhoto;
					//if(file_exists($path)){
						//$path = base_url($path);
						$path = '<img style="width:50px;height:50px" src="' . url("uploads/restaurant/cuisine/" .$row->cuisinePhoto) . '">';
					//}
				}
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit" style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" data-name="' . $row->id . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
					$row->cuisineName,
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
		$cuisineDetails = $this->model->selectDataByCode('cuisinemaster',$code);
		if ($cuisineDetails) {
			$data['code'] = $cuisineDetails->code;
			$data['cuisineName'] = $cuisineDetails->cuisineName;
			$data['cuisinePhoto'] = $cuisineDetails->cuisinePhoto;
			$data['isActive'] = $cuisineDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "cuisinemaster";
		$rules = array(
			'id' => 'nullable',
			'cuisineName' => [
				'required',
				'regex:/^[a-zA-Z\s]+$/',
				'min:3',
				'max:80'
			],
		);
		$messages = array(
			'cuisineName.required' => 'Cuisine name is required',
			'cuisineName.regex' => 'Invalid characters like number, special characters are not allowed',
			'cuisineName.min' => 'Minimum of 3 characters are required.',
			'cuisineName.max' => 'Max characters exceeded.',
		);
		$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 200);
		} else {
			$cuisineName =  ucwords($r->cuisineName);
			if ($r->has('code') && trim($r->code) != "") {
				$code = $r->code;
				$where[] = ["cuisinemaster.cuisineName", "=", $cuisineName];
				$where[] = ["cuisinemaster.code", "!=", $code];
				$duplicate = $this->model->checkForDuplicate($table, "cuisinemaster", $where);
				if (!$duplicate) {
					$data = [
						'cuisineName' => $cuisineName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete'=>0,
						'editIP'=>$ip,
						'editDate'=>$currentdate->toDateTimeString(),
						'editID' => Auth::guard('admin')->user()->code,
					];
					$result = $this->model->doEdit($data, $table, $r->code); 
					if ($result != false) {
						if ($r->hasFile('cuisineImage')) { 
							$fileCuisineImage =  $r->file('cuisineImage');       
							$filenameCuisineImage = $result.'-' . time() .'.'. $fileCuisineImage->getClientOriginalExtension();          
							$fileCuisineImage->move("uploads/restaurant/cuisine",  $filenameCuisineImage);
							$cuisineImageData = ['cuisinePhoto' =>$filenameCuisineImage];
							$image_update = $this->model->doEdit($cuisineImageData, $table, $result);
						}
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Cuisine ".$code." is updated.";
						$this->model->activity_log($data); 
						//activity log end
						
						$res['status'] = 200;
						$res['msg'] = "Cuisine updated successfully ";
					} else {
						$res['status'] = 300;
						$res['msg'] = "No changes were made";
					}
				} else {
					$res['status'] = 400;
					$res['msg'] = "Duplicate records are not allowed";
				}
			} else {
				$where[] = ["cuisinemaster.cuisineName", "=", $cuisineName];
				$duplicate = $this->model->checkForDuplicate($table, "cuisinemaster", $where);
				if (!$duplicate) {
					$data = [
						'cuisineName' => $cuisineName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete' => 0,
						'addIP'=>$ip,
						'addDate'=>$currentdate->toDateTimeString(),
						'addID' =>Auth::guard('admin')->user()->code,
					];
					$result = $this->model->addNew($data, 'cuisinemaster', 'CUIS');
					if ($result) {
						if ($r->hasFile('cuisineImage')) { 
							$fileCuisineImage =  $r->file('cuisineImage');       
							$filenameCuisineImage = $result.'-' . time() .'.'. $fileCuisineImage->getClientOriginalExtension();          
							$fileCuisineImage->move("uploads/restaurant/cuisine",  $filenameCuisineImage);
							$cuisineImageData = ['cuisinePhoto' =>$filenameCuisineImage];
							$image_update = $this->model->doEdit($cuisineImageData, $table, $result);
						}
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Cuisine ".$result." is added";
						$this->model->activity_log($data); 
						//activity log end
						
						$res['status'] = 200;
						$res['msg'] = "Cuisine saved successfully";
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
		$table = 'cuisinemaster';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Cuisine ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		
		echo $this->model->doEditWithField($data, 'cuisinemaster','code',$code);
	}

}
