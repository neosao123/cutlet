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

class RewardSlotController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.rewardslot.index');
	}
    
	public function getRewardSlotList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "rewardpercentageslots";
		$orderColumns = array("rewardpercentageslots.*");
		$condition = array('rewardpercentageslots.isDelete' => array('=', 0));
		$orderBy = array('rewardpercentageslots.id' => 'DESC');
		$join = array();
		$like = array('rewardpercentageslots.from' => $search,'rewardpercentageslots.to' => $search,'rewardpercentageslots.minusValue' => $search);
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
				$minusValue = $row->minusValue;
				$from = $row->from;
				$to=$row->to;
				if($row->isMinus==1) $minusValue = 'RESTO COMM - '.$row->minusValue;
				//if($row->includingFrom==1) $from = '>='.$row->from;
				//if($row->includingTo==1) $to = '<='.$row->to;
				
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit" style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" data-name="' . $row->id . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
					$from,
					$to,
					$minusValue,
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
		$slotDetails = $this->model->selectDataByCode('rewardpercentageslots',$code);
		if ($slotDetails) {
			$data['code'] = $slotDetails->code;
			$data['from'] = $slotDetails->from;
			$data['to'] = $slotDetails->to;
			$data['minusValue'] = $slotDetails->minusValue;
			//$data['includingFrom'] = $slotDetails->includingFrom;
			//$data['includingTo'] = $slotDetails->includingTo;
			$data['isMinus'] = $slotDetails->isMinus;
			$data['isActive'] = $slotDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "rewardpercentageslots";
		$rules = array(
			'from' => ['required','regex:/^[0-9\s]+$/'],
			'to' => ['nullable','regex:/^[0-9\s]+$/'],
			'minusValue' => ['required','regex:/^[0-9\s]+$/'],
		);
		$messages = array(
			'from.required' => 'From Value is required',
			'from.regex' => 'Only Numbers are allowed',
			'to.regex' => 'Only Numbers are allowed',
			'minusValue.required' => 'Percentage/Minus Value is required',
			'minusValue.regex' => 'Only Numbers are allowed',
		);
		$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 200);
		} else {
			$from =  $r->from;
			$to =  $r->to;
			$minusValue =  $r->minusValue;
			//$includingFrom =  $r->includingFrom;
			//$includingTo =  $r->includingTo;
			$isMinus =  $r->isMinus;
			if ($r->has('code') && trim($r->code) != "") {
				$extraCondition = ' rewardpercentageslots.from!="" and rewardpercentageslots.to!="" and rewardpercentageslots.from IS NOT NULL and rewardpercentageslots.to IS NOT NULL and ';
				$extraCondition1 = ' ('.$r->from.' between rewardpercentageslots.from and rewardpercentageslots.to)';
				if($r->to!='' && $r->to!=NULL){
					$extraCondition1 .= ' or ('.$r->to.' between rewardpercentageslots.from and rewardpercentageslots.to) ';
				}
				$extraCondition .= '('.$extraCondition1.')';
				$checkSlot = $this->model->selectQuery(array('rewardpercentageslots.id'),'rewardpercentageslots',array(),array('rewardpercentageslots.code'=>array('!=',$r->code)),array(),array(),"","",$extraCondition);
				if($checkSlot && count($checkSlot)>0){
					$res['status'] = 300;
					$res['msg'] = "Overlapping Slots not allowed";
				}else{
					$data = [
						'from' => $from,
						'to' => $to,
						//'includingFrom' => $includingFrom,
						//'includingTo' => $includingTo,
						'minusValue' => $minusValue,
						'isMinus' => $isMinus,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete'=>0,
						'editIP'=>$ip,
						'editDate'=>$currentdate->toDateTimeString(),
						'editID' => Auth::guard('admin')->user()->code,
					];
					$result = $this->model->doEdit($data, $table, $r->code); 
					if ($result != false) {
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	".$r->code." reward slot is updated.";
						$this->model->activity_log($data); 
						$res['status'] = 200;
						$res['msg'] = "Reward Slot updated successfully ";
					} else {
						$res['status'] = 300;
						$res['msg'] = "No changes were made";
					}
				}
			} else {
				$extraCondition = ' rewardpercentageslots.from!="" and rewardpercentageslots.to!="" and rewardpercentageslots.from IS NOT NULL and rewardpercentageslots.to IS NOT NULL and ';
				$extraCondition1 = '('.$r->from.' between rewardpercentageslots.from and rewardpercentageslots.to) ';
				if($r->to!='' && $r->to!=NULL){
					$extraCondition1 .= ' or ('.$r->to.' between rewardpercentageslots.from and rewardpercentageslots.to)';
				}
				$extraCondition .= '('.$extraCondition1.')';
				$checkSlot = $this->model->selectQuery(array('rewardpercentageslots.id'),'rewardpercentageslots',array(),array(),array(),array(),"","",$extraCondition);
				if($checkSlot && count($checkSlot)>0){
					$res['status'] = 300;
					$res['msg'] = "Overlapping Slots not allowed";
				}else{
					$data = [
						'from' => $from,
						'to' => $to,
						//'includingFrom' => $includingFrom,
						//'includingTo' => $includingTo,
						'minusValue' => $minusValue,
						'isMinus' => $isMinus,
						'isActive' => $r->isActive == "" ? '0' : 1,
						'isDelete' => 0,
						'addIP'=>$ip,
						'addDate'=>$currentdate->toDateTimeString(),
						'addID' =>Auth::guard('admin')->user()->code,
					];
					$result = $this->model->addNew($data, 'rewardpercentageslots', 'RPS');
					if ($result) {
						$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	".$result." reward slot is added";
						$this->model->activity_log($data); 
						$res['status'] = 200;
						$res['msg'] = "Reward slot saved successfully";
					} else {
						$res['status'] = 300;
						$res['msg'] = "Failed to add designation";
					}
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
		$table = 'rewardpercentageslots';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	".$code." reward slot is deleted";
		$this->model->activity_log($data1); 
		echo $this->model->doEditWithField($data, 'rewardpercentageslots','code',$code);
	}

}
