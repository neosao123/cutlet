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

class TermsController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.terms.index');
	}
    
	public function getTermsList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "termsAndConditions";
		$orderColumns = array("termsAndConditions.*");
		$condition = array('termsAndConditions.isDelete' => array('=', 0));
		$orderBy = array('termsAndConditions.id' => 'DESC');
		$join = array();
		$like = array('termsAndConditions.type' => $search,'termsAndConditions.text' => $search);
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
					ucfirst($row->type),
					$row->text,
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
		$termsDetails = $this->model->selectDataByCode('termsAndConditions',$code);
		if ($termsDetails) {
			$data['code'] = $termsDetails->code;
			$data['type'] = $termsDetails->type;
			$data['text'] = $termsDetails->text;
			$data['isActive'] = $termsDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "termsAndConditions";
		$rules = array(
			'termsType' => [
				'required'
			],
			'termsText' => [
				'required',
				'min:2',
				'max:255'
			]
		);
		$messages = array(
			'termsType.required' => 'Type is required',
			'termsText.required' => 'Terms and Conditions are required',
			'termsText.min' => 'Minimum of 3 characters are required.',
			'termsText.max' => 'Max characters exceeded.',
		);
		$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 200);
		} else {
			$termsType =  $r->termsType;
			$termsText =  $r->termsText;
			if ($r->has('code') && trim($r->code) != "") {
				$code = $r->code;
				$where[] = ["termsAndConditions.text", "=", $termsText];
				$where[] = ["termsAndConditions.code", "!=", $code];
				$duplicate = $this->model->checkForDuplicate($table, "termsAndConditions", $where);
				if (!$duplicate) {
					$data = [
						'text' => $termsText,
						'type' => $termsType,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete'=>0,
						'editIP'=>$ip,
						'editDate'=>$currentdate->toDateTimeString(),
						'editID' => Auth::guard('admin')->user()->code,
					];
					$result = $this->model->doEdit($data, $table, $r->code); 
					if ($result != false) {
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	".$code." Terms is updated under ".$termsType." type";
						$this->model->activity_log($data); 
						$res['status'] = 200;
						$res['msg'] = "Terms And Condition updated successfully ";
					} else {
						$res['status'] = 300;
						$res['msg'] = "No changes were made";
					}
				} else {
					$res['status'] = 400;
					$res['msg'] = "Duplicate records are not allowed";
				}
			} else {
				$where[] = ["termsAndConditions.text", "=", $termsText];
				$duplicate = $this->model->checkForDuplicate($table, "termsAndConditions", $where);
				if (!$duplicate) {
					$data = [
						'text' => $termsText,
						'type' => $termsType,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete' => 0,
						'addIP'=>$ip,
						'addDate'=>$currentdate->toDateTimeString(),
						'addID' =>Auth::guard('admin')->user()->code,
					];
					$result = $this->model->addNew($data, 'termsAndConditions', 'TAC');
					if ($result) {
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."New terms and condition ".$result." is added under ".$termsType." type";
						$this->model->activity_log($data); 
						$res['status'] = 200;
						$res['msg'] = "Terms And Condition saved successfully";
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
		$table = 'termsAndConditions';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	".$code." terms and condition is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->doEditWithField($data, 'termsAndConditions','code',$code);
	}

}
