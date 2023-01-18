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

class MenuSubcategoryController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		$menuCategory = $this->model->selectActiveDataFromTable('menucategory');
		return view('admin.menusubcategory.index',compact('menuCategory'));
	}
    
	public function getSubcategoryList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "menusubcategory";
		$orderColumns = array("menusubcategory.*","menucategory.menuCategoryName");
		$condition = array('menusubcategory.isDelete' => array('=', 0));
		$orderBy = array('menusubcategory' . '.id' => 'DESC');
		$join = array('menucategory' => array('menucategory.code', 'menusubcategory.menuCategoryCode'));
		$like = array('menusubcategory.menuSubCategoryName' => $search);
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
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit" style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" data-name="' . $row->id . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
					$row->menuCategoryName,
					$row->menuSubCategoryName,
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
		$subcategoryList = $this->model->selectDataByCode('menusubcategory',$code);
		if ($subcategoryList) {
			$data['code'] = $subcategoryList->code;
			$data['menuCategoryCode'] = $subcategoryList->menuCategoryCode;
			$data['menuSubCategoryName'] = $subcategoryList->menuSubCategoryName;
			$data['isActive'] = $subcategoryList->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "menusubcategory";
		$rules = array(
			'id' => 'nullable',
			'menuCategoryCode' => [
				'required'
				],
			'menuSubCategoryName' => [
				'required',
				'regex:/^[a-zA-Z\s]+$/',
				'min:3',
				'max:80'
			],
		);
		$messages = array(
			'menuCategoryCode.required' => 'Menu Category is required',
			'menuSubCategoryName.required' => 'Menu Subcategory name is required',
			'menuSubCategoryName.regex' => 'Invalid characters like number, special characters are not allowed',
			'menuSubCategoryName.min' => 'Minimum of 3 characters are required.',
			'menuSubCategoryName.max' => 'Max characters exceeded.',
		);
		$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 200);
		} else {
			$menuSubCategoryName =  ucwords($r->menuSubCategoryName);
			if ($r->has('code') && trim($r->code) != "") {
				$code = $r->code;
				$where[] = ["menusubcategory.menuSubCategoryName", "=", $menuSubCategoryName];
				$where[] = ["menusubcategory.code", "!=", $code];
				$duplicate = $this->model->checkForDuplicate($table, "menusubcategory", $where);
				if (!$duplicate) {
					$data = [
						'menuCategoryCode' => $r->menuCategoryCode,
						'menuSubCategoryName' => $menuSubCategoryName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete'=>0,
						'editIP'=>$ip,
						'editDate'=>$currentdate->toDateTimeString(),
						'editID' => Auth::guard('admin')->user()->code,
					];
					$result = $this->model->doEdit($data, $table, $r->code); 
					if ($result != false) {
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."MenuSubcategory ".$code." is updated.";
						$this->model->activity_log($data); 
						//activity log end
						$res['status'] = 200;
						$res['msg'] = "Menu subcategory updated successfully ";
					} else {
						$res['status'] = 300;
						$res['msg'] = "No changes were made";
					}
				} else {
					$res['status'] = 400;
					$res['msg'] = "Duplicate records are not allowed";
				}
			} else {
				$where[] = ["menusubcategory.menuSubCategoryName", "=", $menuSubCategoryName];
				$duplicate = $this->model->checkForDuplicate($table, "menusubcategory", $where);
				if (!$duplicate) {
					$data = [
						'menuCategoryCode' => $r->menuCategoryCode,
						'menuSubCategoryName' => $menuSubCategoryName,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete' => 0,
						'addIP'=>$ip,
						'addDate'=>$currentdate->toDateTimeString(),
						'addID' =>Auth::guard('admin')->user()->code,
					];
					$result = $this->model->addNew($data, 'menusubcategory', 'SBCAT');
					if ($result) {
						//activity log start
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."MenuSubcategory ".$result." is added";
						$this->model->activity_log($data); 
						//activity log end
						
						$res['status'] = 200;
						$res['msg'] = "Menu subcategory saved successfully";
					} else {
						$res['status'] = 300;
						$res['msg'] = "Failed to add menu category";
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
		$table = 'menusubcategory';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."MenuSubcategory ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		
		echo $this->model->doEditWithField($data, 'menusubcategory','code',$code);
	}

}
