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
use Mail;

class AddressController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
		$data['district'] = DB::table('customaddressmaster')->distinct()->select('district')->where('isDelete','!=',1)->get();
        $data['state'] = DB::table('customaddressmaster')->distinct()->select('state')->where('isDelete','!=',1)->get();
		$data['taluka'] = DB::table('customaddressmaster')->distinct()->select('taluka')->where('isDelete','!=',1)->get();
		$data['place'] = DB::table('customaddressmaster')->distinct()->select('place')->where('isDelete','!=',1)->get();
		$data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.address.list',$data);
    }

    public function getAddressList(Request $req)
    {
        $search = $req->input('search.value');
		$state = $req->state;
		$city = $req->city;
        $district= $req->district;
		$taluka=$req->taluka;
		$place=$req->place;
        $tableName = "customaddressmaster";
        $orderColumns = array("customaddressmaster.*","citymaster.cityName");
        $condition = array('customaddressmaster.isDelete' => array('!=', 1),"customaddressmaster.cityCode"=>array("=",$city),"customaddressmaster.state"=>array("=",$state),"customaddressmaster.district"=>array("=",$district),"customaddressmaster.taluka"=>array("=",$taluka),"customaddressmaster.place"=>array("=",$place));
        $orderBy = array('customaddressmaster.id' => 'DESC');
         $join = array('citymaster' => array('citymaster.code', 'customaddressmaster.cityCode'));
        $like = array('customaddressmaster.place' => $search);
		$like = array('customaddressmaster.code' => $search, 'customaddressmaster.place' => $search, 'customaddressmaster.state' => $search, 'customaddressmaster.district' => $search, 'customaddressmaster.pincode' => $search);
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
				if ($row->isService == "1") {
                    $service = " <span class='badge badge-success'>Service Available</span>";
                } else {
                    $service = " <span class='badge badge-danger'>Service Unavailable</span>";
                }
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("address/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("address/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
                $data[] = array(
                   $srno, 
				   $row->code,
				   $row->cityName,
				   $row->state, 
				   $row->district, 
				   $row->place, 
				   $row->pincode, 
				   $service, 
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
        return view('admin.address.add',compact('city'));
    }

    public function store(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'cityCode' => 'required',
            'state' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'district' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'taluka' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'pincode' => 'required|regex:/^[0-9\s]+$/|min:3|max:80',
            'place' => 'required|regex:/^[a-zA-Z0-9\s]+$/|min:3|max:255',
			'latitude'=>'regex:/^[0-9\s]+$/',
			'longitude'=>'regex:/^[0-9\s]+$/',
			'radius'=>'regex:/^[0-9\s]+$/'
        );
        $messages = array(
            'cityCode.required' => 'City is required',
            'state.required' => 'State is required',
            'state.regex' => 'Invalid characters like number, special characters are not allowed',
            'state.min' => 'Minimum of 3 characters are required.',
            'state.max' => 'Max characters exceeded.',
			'district.required' => 'District is required',
            'district.regex' => 'Invalid characters like number, special characters are not allowed',
            'district.min' => 'Minimum of 3 characters are required.',
            'district.max' => 'Max characters exceeded.',
			'taluka.required' => 'Taluka is required',
            'taluka.regex' => 'Invalid characters like number, special characters are not allowed',
            'taluka.min' => 'Minimum of 3 characters are required.',
            'taluka.max' => 'Max characters exceeded.',
			'pincode.required' => 'Pincode is required',
            'pincode.regex' => 'Only numbers are allowed',
            'pincode.min' => 'Minimum of 3 characters are required.',
            'pincode.max' => 'Max characters exceeded.',
			'place.required' => 'Place is required',
            'place.regex' => 'Special characters are not allowed',
            'place.min' => 'Minimum of 3 characters are required.',
            'place.max' => 'Max characters exceeded.',
            'latitude.regex' => 'Only numbers are allowed',
            'longitude.regex' => 'Only numbers are allowed',
            'radius.regex' => 'Only numbers are allowed'
        );
        $data = [
			'cityCode' => trim($r->cityCode), 
			'state' => trim($r->state), 
			'district' => trim($r->district), 
			'taluka' => trim($r->taluka), 
			'pincode' => trim($r->pincode), 
			'place' => ucwords(strtolower(trim($r->place))), 
			'isService' => trim($r->isService), 
			'longitude' => trim($r->longitude), 
			'latitude' => trim($r->latitude), 
			'radius' => trim($r->radius), 
            'isActive' => $r->isActive == "" ? '0' : 1,
			'isService' => $r->isService == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
        ];
        $currentId = $this->model->addNew($data, 'customaddressmaster', 'ADDR');
        if ($currentId) {
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Address ".$currentId." is added";
			$this->model->activity_log($data); 
			//activity log end
			
            return redirect('address/list')->with('success', 'Address added successfully');
        }
        return back()->with('error', 'Failed to add the address');
    }

    public function edit($code)
    {
        $data['address'] = $this->model->selectDataByCode('customaddressmaster', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.address.edit', $data);
    }

    public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'cityCode' => 'required',
            'state' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'district' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'taluka' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'pincode' => 'required|regex:/^[0-9\s]+$/|min:3|max:80',
            'place' => 'required|regex:/^[a-zA-Z0-9\s]+$/|min:3|max:255',
			'latitude'=>'regex:/^[0-9\s]+$/',
			'longitude'=>'regex:/^[0-9\s]+$/',
			'radius'=>'regex:/^[0-9\s]+$/',
        );
        $messages = array(
            'cityCode.required' => 'City is required',
            'state.required' => 'State is required',
            'state.regex' => 'Invalid characters like number, special characters are not allowed',
            'state.min' => 'Minimum of 3 characters are required.',
            'state.max' => 'Max characters exceeded.',
			'district.required' => 'District is required',
            'district.regex' => 'Invalid characters like number, special characters are not allowed',
            'district.min' => 'Minimum of 3 characters are required.',
            'district.max' => 'Max characters exceeded.',
			'taluka.required' => 'Taluka is required',
            'taluka.regex' => 'Invalid characters like number, special characters are not allowed',
            'taluka.min' => 'Minimum of 3 characters are required.',
            'taluka.max' => 'Max characters exceeded.',
			'pincode.required' => 'Pincode is required',
            'pincode.regex' => 'Only numbers are allowed',
            'pincode.min' => 'Minimum of 3 characters are required.',
            'pincode.max' => 'Max characters exceeded.',
			'place.required' => 'Place is required',
            'place.regex' => 'Special characters are not allowed',
            'place.min' => 'Minimum of 3 characters are required.',
            'place.max' => 'Max characters exceeded.',
            'latitude.regex' => 'Only numbers are allowed',
            'longitude.regex' => 'Only numbers are allowed',
            'radius.regex' => 'Only numbers are allowed'
        );
        $data = [
			'cityCode' => trim($r->cityCode), 
			'state' => trim($r->state), 
			'district' => trim($r->district), 
			'taluka' => trim($r->taluka), 
			'pincode' => trim($r->pincode), 
			'place' => ucwords(strtolower(trim($r->place))), 
			'isService' => trim($r->isService), 
			'longitude' => trim($r->longitude), 
			'latitude' => trim($r->latitude), 
			'radius' => trim($r->radius), 
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isService' => $r->isService == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
        ];
        $result = $this->model->doEdit($data, 'customaddressmaster', $r->code);
        if ($result) {
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Address ".$r->code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
			
            return redirect('address/list')->with('success', 'Address updated successfully');
        }
        return back()->with('error', 'Failed to update the address');
    }

    public function view($code)
    {
        $data['address'] = $this->model->selectDataByCode('customaddressmaster', $code);
		 $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.address.view', $data);
    }

    public function delete(Request $r)
    {
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];        
        //activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Address ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end       
	    echo $this->model->doEditWithField($data, 'customaddressmaster','code',$code);
    }
	
	public function basic_email() {
		 \Mail::to('shraddharhatole14997@gmail.com')->send(new \App\Mail\Test(array('message'=>'HIII')));

   }
}
