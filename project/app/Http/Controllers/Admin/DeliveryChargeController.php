<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;

class DeliveryChargeController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
		$city = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.deliveryChargeSlots.index',compact('city'));
    }

    public function getSlotsList(Request $req)
    {
        $search = $req->input('search.value');
        $tableName = "deliverychargesslot";
		$orderColumns = array("deliverychargesslot.*","citymaster.cityName");
		$condition = array('deliverychargesslot.isDelete' => array('=', 0));
		$orderBy = array('deliverychargesslot' . '.id' => 'DESC');
		$join = array('citymaster' => array('citymaster.code', 'deliverychargesslot.cityCode'));
		$like = array('citymaster.cityName' => $search);
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
                $actions = '
					<div class="text-center"> 
                        <a class="btn btn-outline-success btn-sm icons_padding" href="' . url("deliveryCharges/view/" . $row->code) . '" title="View"><i class="fa fa-eye" style="font-size:18px;"></i></a>
					    <a class="btn btn-outline-primary btn-sm icons_padding" style="" href="' . url("deliveryCharges/edit/" . $row->code) . '" title="Edit"><i class="fa fa-edit" style="font-size:18px;"></i></a>
						<a class="btn btn-outline-danger btn-sm icons_padding delbtn" data-id="' . $row->code . '" title="Delete Record?"><i class="fa fa-trash" style="font-size:18px;"></i></a>						
					</div>';
                $data[] = array(
                   $srno, 
				   $row->code,
				   $row->cityName,
				   $row->fromKM,
				   $row->toKM,
				   $row->deliveryCharges,
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

    public function add()
    {
		$city = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.deliveryChargeSlots.add',compact('city'));
    }

    public function store(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'cityCode' => 'required',
            'fromKM' => 'required|regex:/^[0-9\s]+$/',
            'toKM' => 'required|regex:/^[0-9\s]+$/',
            'deliveryCharges' => 'required|regex:/^[0-9\s]+$/',
        );
        $messages = array(
            'cityCode.required' => 'City is required',
            'fromKM.required' => 'From Amount is required',
            'fromKM.regex' => 'Only numbers are allowed',
			'toKM.required' => 'From Amount is required',
            'toKM.regex' => 'Only numbers are allowed',
			'deliveryCharges.required' => 'Delivery Charges are required',
            'deliveryCharges.regex' => 'Only numbers are allowed',
        );
        $data = [
			'cityCode' => trim($r->cityCode), 
			'fromKM' => trim($r->fromKM), 
			'toKM' => trim($r->toKM), 
			'deliveryCharges' => trim($r->deliveryCharges), 
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
        ];
        $currentId = $this->model->addNew($data, 'deliverychargesslot', 'DCS');
        if ($currentId) {
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Delivery Charge ".$currentId." is added";
			$this->model->activity_log($data); 
			//activity log end

            return redirect('deliveryCharges/list')->with('success', 'Delivery Charges Slots added successfully');
        }
        return back()->with('error', 'Failed to add the slots');
    }

    public function edit($code)
    {
        $data['slot'] = $this->model->selectDataByCode('deliverychargesslot', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.deliveryChargeSlots.edit', $data);
    }

    public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'cityCode' => 'required',
            'fromKM' => 'required|regex:/^[0-9\s]+$/',
            'toKM' => 'required|regex:/^[0-9\s]+$/',
            'deliveryCharges' => 'required|regex:/^[0-9\s]+$/',
        );
        $messages = array(
            'cityCode.required' => 'City is required',
            'fromKM.required' => 'From Amount is required',
            'fromKM.regex' => 'Only numbers are allowed',
			'toKM.required' => 'From Amount is required',
            'toKM.regex' => 'Only numbers are allowed',
			'deliveryCharges.required' => 'Delivery Charges are required',
            'deliveryCharges.regex' => 'Only numbers are allowed',
        );
        $data = [
			'cityCode' => trim($r->cityCode), 
			'fromKM' => trim($r->fromKM), 
			'toKM' => trim($r->toKM), 
			'deliveryCharges' => trim($r->deliveryCharges), 
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
        ];
        $result = $this->model->doEdit($data, 'deliverychargesslot', $r->code);
        if ($result) {
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Delivery Charge ".$r->code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('deliveryCharges/list')->with('success', 'Delivery Charges Slots updated successfully');
        }
        return back()->with('error', 'Failed to update the slots');
    }

    public function view($code)
    {
        $data['slot'] = $this->model->selectDataByCode('deliverychargesslot', $code);
		 $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.deliveryChargeSlots.view', $data);
    }

    public function delete(Request $r)
    {
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];        
        //activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Delivery Charge ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		
		echo $this->model->doEditWithField($data, 'deliverychargesslot','code',$code);
    }
	
	public function checkOverlappingSlot(Request $r){
        $fromKM = $r->fromKM;
        $cityCode = $r->cityCode;
		//DB::enableQueryLog();
		$checkActivesub = DB::table('deliverychargesslot')
            ->select('deliverychargesslot.id')
            ->where('deliverychargesslot.cityCode', $cityCode)
            ->where('deliverychargesslot.isActive', 1)
            ->whereRaw("$fromKM between deliverychargesslot.fromKM and deliverychargesslot.toKM")
            ->where('deliverychargesslot.toKM', '=', $fromKM)
            ->orderBy('deliverychargesslot.id', 'DESC')->take(1)->first();
		if(!empty($checkActivesub)){
			echo 1;
		}
	}
	
}
