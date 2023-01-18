<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use Carbon\Carbon;
use DB;
use Mail;

class CustomerController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
		$data['citymaster'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['mobiles'] = Customer::distinct()->where("isActive","=",1)->get(['mobile']);
		$data['emails'] = Customer::distinct()->where('emailId',"!=",'')->where('isActive',"=",1)->get(['emailId']);
		$data['clientmaster'] = $this->model->selectActiveDataFromTable('clientmaster');
        return view('admin.customer.list',$data);
    }

    public function getCustomerList(Request $req)
    {
        $search = $req->input('search.value');
		$clientCode= $req->clientCode;
		$cityCode= $req->cityCode;
		$email= $req->email;
		$mobile= $req->mobile;
		 $tableName = "clientmaster";
        $orderColumns = array("clientmaster.*","citymaster.cityName");
        $condition = array('clientmaster.code'=>array("=",$clientCode),'clientmaster.cityCode'=>array("=",$cityCode),"clientmaster.emailId"=>array("=",$email),"clientmaster.mobile"=>array("=",$mobile),'clientmaster.isDelete' => array('!=', 1));
        $orderBy = array('clientmaster.id' => 'DESC');
        $joinType = array('citymaster'=>'left');
        $join = array('citymaster'=> array('clientmaster.cityCode','citymaster.code'));
        $groupByColumn = array('clientmaster.code');
        $like = array("clientmaster.name" => $search,"clientmaster.mobile" => $search ,"clientmaster.code" => $search);
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
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("customer/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("customer/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
              
                $data[] = array(
                   $srno, 
				   $row->code, 
				   $row->name, 
				   $row->cityName, 
				   $row->mobile, 
				   $row->emailId, 
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

    public function edit($code)
    {
        $data['customer'] = $this->model->selectDataByCode('clientmaster', $code);
        return view('admin.customer.edit', $data);
    }

    public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'name' => 'required'
        );
        $messages = array(
            'name.required' => 'Name is required',
        );
        $data = [
			'name' => trim($r->name),
            'editIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code,
        ];
        $result = $this->model->doEdit($data, 'clientmaster', $r->code);
        if ($result) {
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Customer ".$r->code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('customer/list')->with('success', 'Customer updated successfully');
        }
        return back()->with('error', 'Failed to update the customer');
    }

    public function view($code)
    {
        $data['customer'] = $this->model->selectDataByCode('clientmaster', $code);
        $data['clientprofile'] = $this->model->selectQuery('clientprofile.*', 'clientprofile',array(),array('clientprofile.clientCode' => array('=', $code)));
        return view('admin.customer.view', $data);
    }

    public function delete(Request $r)
    {
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
        $today = date('Y-m-d H:i:s');
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('admin')->user()->code, 'deleteDate' => $today];        
        
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Customer ".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		
		echo $this->model->doEditWithField($data, 'clientmaster','code',$code);
    }
}
