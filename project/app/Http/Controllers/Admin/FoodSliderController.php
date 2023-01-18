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

class FoodSliderController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.foodslider.index');
	}
    
	public function getSliderList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "foodslider";
		$orderColumns = array("foodslider.*");
		$condition = array('foodslider.isDelete' => array('=', 0));
		$orderBy = array('foodslider' . '.id' => 'DESC');
		$join = array();
		$like = array('foodslider.code' => $search);
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
				if($row->sliderPhoto!=""){
					//$path = 'uploads/restaurant/sliderimage/'.$row->sliderPhoto;
					//if(file_exists($path)){
						///$path = url($path);
						$path = '<img style="width:50px;height:50px" src="' . url("uploads/restaurant/sliderimage/" .$row->sliderPhoto) . '">';
					//}
				}
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-info btn-sm icons_padding edit" data-id="' . $row->code . '" title="Edit"><i class="fas fa-edit" style="font-size:18px;"></i></a>
						<a class="delete_id btn btn-outline-danger btn-sm icons_padding" id="' . $row->code . '" data-name="' . $row->id . '" title="Delete"><i class="fa fa-trash" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
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
		$sliderDetails = $this->model->selectDataByCode('foodslider',$code);
		if ($sliderDetails) {
			$data['code'] = $sliderDetails->code;
			$data['sliderPhoto'] = $sliderDetails->sliderPhoto;
			$data['isActive'] = $sliderDetails->isActive;
			return response()->json(["status" => 200, "msg" => "Data found", "data" => $data], 200);
		}
		return response()->json(["msg" => "Data Not Found"], 400);
	}

    public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$table = "foodslider";
		
			if ($r->has('code') && trim($r->code) != "") {
				$code = $r->code;
				$data = [
					'isActive' => $r->isActive == "" ? 0 : 1,
					'isDelete'=>0,
					'editIP'=>$ip,
					'editDate'=>$currentdate->toDateTimeString(),
					'editID' => Auth::guard('admin')->user()->code,
				];
				$result = $this->model->doEdit($data, $table, $r->code); 
				if ($result != false) {
					if ($r->hasFile('sliderImage')) { 
						$fileSliderImage =  $r->file('sliderImage');       
						$filenameSliderImage =  $r->code.'-' . time() .'.'. $fileSliderImage->getClientOriginalExtension();          
						$fileSliderImage->move("uploads/restaurant/sliderimage",  $filenameSliderImage);
						$sliderImageData = ['sliderPhoto' =>$filenameSliderImage];
						$image_update = $this->model->doEdit($sliderImageData, $table, $r->code);
					}
					//activity log start
					$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Food Slider ".$code." is updated.";
					$this->model->activity_log($data); 
					//activity log end
					$res['status'] = 200;
					$res['msg'] = "Food slider image updated successfully ";
				} else {
					$res['status'] = 300;
					$res['msg'] = "No changes were made";
				}
			} else {
				$data = [
					'isActive' => $r->isActive == "" ? 0 : 1,
					'isDelete' => 0,
					'addIP'=>$ip,
					'addDate'=>$currentdate->toDateTimeString(),
					'addID' =>Auth::guard('admin')->user()->code,
				];
				$result = $this->model->addNew($data, 'foodslider', 'FSLI');
				if ($result) {
					if ($r->hasFile('sliderImage')) { 
						$fileSliderImage =  $r->file('sliderImage');       
						$filenameSliderImage = $result.'-' . time() .'.'.$fileSliderImage->getClientOriginalExtension();          
						$fileSliderImage->move("uploads/restaurant/sliderimage",  $filenameSliderImage);
						$sliderImageData = ['sliderPhoto' =>$filenameSliderImage];
						$image_update = $this->model->doEdit($sliderImageData, $table, $result);
					}
					//activity log start
					$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Food Slider ".$result." is added";
					$this->model->activity_log($data); 
					//activity log end
					$res['status'] = 200;
					$res['msg'] = "Food slider image saved successfully";
				} else {
					$res['status'] = 300;
					$res['msg'] = "Failed to add Food slider image";
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
		$table = 'foodslider';
		$data = ['isActive' => 0,'isDelete'=>1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Food Slider".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->doEditWithField($data, 'foodslider','code',$code);
	}

}
