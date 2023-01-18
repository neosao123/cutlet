<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	
	public function index()
	{
		$tablecity = "citymaster";
        $city = $this->model->selectActiveDataFromTable($tablecity);
        $tabledesignationmaster = 'designationmaster';
        $designation = $this->model->selectActiveDataFromTable($tabledesignationmaster);
		$tableusermaster="usermaster";
		$users = $this->model->selectActiveDataFromTable($tableusermaster);
		return view('admin.user.list',compact('designation', 'city','users'));
	}
	
	public function add()
	{
		$tablecity = "citymaster";
        $city = $this->model->selectActiveDataFromTable($tablecity);
        $tabledesignationmaster = 'designationmaster';
        $designation = $this->model->selectActiveDataFromTable($tabledesignationmaster);
        return view('admin.user.add', compact('designation', 'city'));
	}
	
	public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		
		 $rules = array(
		    //'username'=>'required|regex:/^[a-zA-Z\s]+$/|min:3|max:10',
            'fullname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'email' => 'required|email',
            'mobilenumber' => 'required|min:10|max:10',
            'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg|max:200|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            'password' => 'required|min:6|confirmed',
			'password_confirmation' => 'required|min:6',
			'designation'=>'required', 
			'city'=>'required',
			'role'=>'required'
        );
        $messages = array(
		    /*'username.required' => 'User name is required',
            'username.regex' => 'Invalid characters like number, special characters are not allowed',
            'username.min' => 'Minimum of 3 characters are required.',
            'username.max' => 'Max characters exceeded.',*/
            'fullname.required' => 'Full name is required',
            'fullname.regex' => 'Invalid characters like number, special characters are not allowed',
            'fullname.min' => 'Minimum of 3 characters are required.',
            'fullname.max' => 'Max characters exceeded.',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email address',
            'mobilenumber.required' => 'Mobile Number is required',
            'mobilenumber.min' => 'Invalid mobile number (Min 10 digits)',
            'mobilenumber.max' => 'Invalid mobile number (Max 10 digits)',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be 6 characters long',
            'password.max' => 'Password max length reached',
			'password.confirmed' => 'Password does not match',
			'password_confirmation.required' => 'Confirm Password is required',
			'city.required'=>'City is required.',
			'designation.required'=>'Designation is required.'
        );
		
		$this->validate($r, $rules, $messages);
        $table = "usermaster";
		//$username =  $r->username;
		$fullName =  ucwords(strtolower($r->fullname));
        $emailId = $r->email;
        $mobile = $r->mobilenumber;
		$designation=$r->designation;
		$password=$r->password;
		$city=$r->city;
		$role=$r->role;

        $where[] = ["$table.userEmail", "=", $emailId];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where);
        if ($duplicate) {
            return back()->with('error', 'Duplicate email address exits');
        }
		
		/*$where1[] = ["$table.username", "=", $username];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where1);
        if ($duplicate) {
            return back()->with('error', 'Duplicate username exits');
        }*/

        $where2[] = ["$table.mobile", "=", $mobile];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where2);
        if ($duplicate) {
            return back()->with('error', 'Duplicate mobile number exits');
        }
		
		$data = [
		    //'username' =>$username,
            'name' => $fullName,
            'userEmail' => $emailId,
            'mobile' => $mobile,
			'role'=>$role,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'password' => Hash::make($r->password),
			'cityCode'=>$city,
			'designationCode'=>$designation,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
			'editIP'=>'',
			'editID'=>''
        ];
		
		 if ($r->hasFile('profilePhoto')) { 
            $files =  $r->file('profilePhoto');       
            $filename = 'photo-' . time() . '-' . $files->getClientOriginalExtension();          
            $files->move("uploads/profile", $filename);
            $data['profilePhoto'] = $filename;
        }
		
		$currentId = $this->model->addNew($data, $table, 'USR');
		if ($currentId) {
			if($role=='DBOY'){
				$dataDbActive['deliveryBoyCode'] = $currentId;
				$dataDbActive['orderCount'] = 0;
				$dataDbActive['loginStatus'] = 0;
				$dataDbActive['isActive'] = 1;
				$resultDbActive = $this->model->addNew($dataDbActive, 'deliveryBoyActiveOrder', 'DBA');
			}
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."User ".$currentId." is added";
				$this->model->activity_log($data); 
				//activity log end
            return redirect('users/list')->with('success', 'Record added successfully');
        }
        return back()->with('error', 'Failed to add the record');

	}
	
	public function getUserList(Request $req)
    {
		$username = $req->username;
        $designation = $req->designation;
		$sessionRole=Auth::guard('admin')->user()->role;
		$city = $req->city;
        $role = $req->role;
        $search = $req->input('search.value');
        $tableName = "usermaster";
        $orderColumns = array("usermaster.*", "designationmaster.designation as designationName", "citymaster.cityName as cityName");
        $condition = array('usermaster.isDelete' => array('=', 0),'usermaster.code' => array('=', $username),'usermaster.designationCode' => array('=', $designation),'usermaster.cityCode' => array('=', $city),'usermaster.role' => array('=', $role));
        $orderBy = array('usermaster' . '.id' => 'DESC');
        $join = array('designationmaster' => array('designationmaster.code', 'usermaster.designationCode'), 'citymaster' => array('citymaster.code', 'usermaster.cityCode'));
        $like = array('usermaster.name' => $search,'usermaster.username' => $search,'usermaster.mobile' => $search, 'usermaster.userEmail' => $search,'citymaster.cityName'=>$search,'designationmaster.designation'=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
                $role='';
				$status = '<span class="badge badge-danger"> InActive </span>';
				if ($row->isActive == 1) {
					$status = '<span class="badge badge-success">Active</span>';
				}
				if ($row->role == 'USER') {
					$role = '<span class="badge badge-warning">USER</span>';
				}else if($row->role == 'DBOY'){
					$role = '<span class="badge badge-info">Delivery Boy</span>';
				}else if($row->role == 'ADM'){
				  $role = '<span class="badge badge-success">Admin</span>';
				}
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("users/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("users/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>';
								if($sessionRole =='ADM' && $row->role=='USER' || $row->role=='ADM'){
									$actions .= '<a class="dropdown-item" href="' . url("users/userAccessEditList/" . $row->code) . '"><i class="ti-pencil-alt"></i> User Rights</a>';
								}
							$actions .= '</div>
						</div>';
                $data[] = array(
                    $srno,
                    $row->name,
                    $row->designationName,
                    $row->cityName,
                    $row->mobile, 
                    $row->userEmail,
					$role,
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
	
	public function delete(Request $request)
	{
		$id = $request->id;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$today = date('Y-m-d');
		$table = 'usermaster';
		$data = ['isActive' => 0,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		$deleteQuery = $this->model->doEditWithID($data, $table, $id);
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."User ".$id." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->updateOnDeleteRecordWithId($id, $table); 
	}
	
	public function edit(Request $request, $code)
	{
		$data['users'] = $this->model->selectDataByCode('usermaster', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['designation'] = $this->model->selectActiveDataFromTable('designationmaster');
        return view('admin.user.edit', $data);
	}
	
	
	public function update(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];  
        $currentdate = Carbon::now();
        if (!empty($r->password) || !empty($r->password_confirmation)) {
            $rules = array(
		    //'username'=>'required|regex:/^[a-zA-Z\s]+$/|min:3|max:10',
            'fullname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'userEmail' => 'required',
            'mobile' => 'required',
            'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg|max:200|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            'password' => 'min:6|confirmed',
			'password_confirmation' => 'min:6',
			'designation'=>'required', 
			'city'=>'required',
			'role'=>'required'
           );
        } else {
            $rules = array(
		    //'username'=>'required|regex:/^[a-zA-Z\s]+$/|min:3|max:10',
            'fullname' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:80',
            'userEmail' => 'required',
            'mobile' => 'required',
            'profilePhoto' => 'nullable|image|mimes:jpg,png,jpeg|max:200|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
			'designation'=>'required', 
			'city'=>'required',
			'role'=>'required'
           );
        }
		
		  $messages = array(
		    /*'username.required' => 'User name is required',
            'username.regex' => 'Invalid characters like number, special characters are not allowed',
            'username.min' => 'Minimum of 3 characters are required.',
            'username.max' => 'Max characters exceeded.',*/
            'fullname.required' => 'Full name is required',
            'fullname.regex' => 'Invalid characters like number, special characters are not allowed',
            'fullname.min' => 'Minimum of 3 characters are required.',
            'fullname.max' => 'Max characters exceeded.',
            'userEmail.required' => 'Email is required',
            'userEmail.email' => 'Invalid email address',
            'mobile.required' => 'Mobile Number is required',
            'mobile.min' => 'Invalid mobile number (Min 10 digits)',
            'mobile.max' => 'Invalid mobile number (Max 10 digits)',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be 6 characters long',
            'password.max' => 'Password max length reached',
			'password.confirmed' => 'Password does not match',
			'password_confirmation.required' => 'Confirm Password is required',
			'city.required'=>'City is required.',
			'designation.required'=>'Designation is required.'
        );
		
		$code = $r->code;
        $table = 'usermaster';
	    $where[] = ["$table.userEmail", "=", $r->userEmail];
		$where[] = ["$table.code", "!=", $code];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where);
        if ($duplicate) {
            return back()->with('error', 'Duplicate email address exits');
        }
		
		/*$where1[] = ["$table.username", "=", $r->username];
		$where1[] = ["$table.code", "!=", $code];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where1);
        if ($duplicate) {
            return back()->with('error', 'Duplicate username exits');
        }*/

        $where2[] = ["$table.mobile", "=", $r->mobile];
		$where2[] = ["$table.code", "!=", $code];
        $duplicate = $this->model->checkForDuplicate($table, $table, $where2);
        if ($duplicate) {
            return back()->with('error', 'Duplicate mobile number exits');
        }
		 if (!empty($r->input('password')) || !empty($r->input('password_confirmation'))) {
            $data = [
		    //'username' =>$r->username,
            'name' => $r->fullname,
            'userEmail' => $r->userEmail,
            'mobile' => $r->mobile,
			'role'=>$r->role,
            'isActive' => $r->isActive == "" ? '0' : 1,
            'password' => Hash::make($r->password),
			'cityCode'=>$r->city,
			'designationCode'=>$r->designation,
            'isDelete' => 0,
			'editIP'=>$ip,
			'editDate' => $currentdate->toDateTimeString(),
			'editID'=> Auth::guard('admin')->user()->code,
           ];
        } else {
           $data = [
		    //'username' =>$r->username,
            'name' => $r->fullname,
            'userEmail' => $r->userEmail,
            'mobile' => $r->mobile,
			'role'=>$r->role,
            'isActive' => $r->isActive == "" ? '0' : 1,
			'cityCode'=>$r->city,
			'designationCode'=>$r->designation,
            'isDelete' => 0,
			'editIP'=>$ip,
			'editDate' => $currentdate->toDateTimeString(),
			'editID'=> Auth::guard('admin')->user()->code,
           ];
        }
        $result = $this->model->doEdit($data, $table, $code);
		if ($filenew = $r->file('profilephoto')) {
            $imagename = $filenew->getClientOriginalName();
            $filenew->move('uploads/profile', $imagename);
            $image_data = ['profilePhoto' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }
		
		$exists = $this->model->selectQuery(array("deliveryBoyActiveOrder.*"),"deliveryBoyActiveOrder",array(),array("deliveryBoyActiveOrder.deliveryBoyCode"=>array("=",$code)));
        if($exists && count($exists)>0){}else{
            $dataDbActive['deliveryBoyCode'] = $code;
            $dataDbActive['orderCount'] = 0;
            $dataDbActive['loginStatus'] = 0;
            $dataDbActive['isActive'] = 1;
            $resultDbActive = $this->model->addNew($dataDbActive, 'deliveryBoyActiveOrder', 'DBA');
        }
		if ($result == true || $image_update == true) {
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."User ".$code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect()->back()->with('status', ['message' => "User Updated Successfully"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong"]);
        }
		
	}
	
	public function view(Request $request, $code)
	{
		$data['users'] = $this->model->selectDataByCode('usermaster', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['designation'] = $this->model->selectActiveDataFromTable('designationmaster');
        return view('admin.user.view', $data);
	}

	
	public function userAccessEditList($userCode=null){
        $data['userCode']=$userCode;
        $checkUserRole = $this->model->selectQuery(array("usermaster.role"),"usermaster",array(),array("usermaster.code"=>array("=",$userCode)));
		if($checkUserRole){
			$userRole = $checkUserRole[0]->role;
			if($userRole=='USER' || $userRole=='ADM'){
				 return view('admin.user.accessRights', $data);
			}
		}   
    }
	public function getUserAccessList(){
        $modelHtml='<table id="datatableUserAccess" class="table table-sm table-stripped table-bordered " style="width:100%;">
			<thead>
				<tr class="text-center">
					<th><input type="checkbox" value="1" id="checkAll_module" name="checkAll_module"> Module</th>
					<th><input type="checkbox" value="1" id="checkAll_submodule" name="checkAll_submodule"> Sub Module</th>
				</tr>
			</thead>'; 
		$recordsModule=$this->model->selectUserAccessActiveDataSequence('u_modulemaster');
        foreach($recordsModule as $moduleRow){
				$modelHtml.='<tr>';
				$code=$moduleRow->code;
				$modelHtml.='<td><input type="checkbox" class="mainmodules" value="1" id="chkModule'.$code.'" name="chkModule'.$code.'"> &nbsp'.$moduleRow->moduleName.'</td>';
				$recordsSubModule=$this->model->selectUserAccessActiveDataByFieldSequence('moduleCode',$code,'u_submodulemaster');
				$modelHtml.='<td>
					<table id="" class="table table-bordered ">'; 
				foreach($recordsSubModule as $subModuleRow){
					$subModuleCode=$subModuleRow->code;
					$modelHtml.='<tr class=""><td><input type="checkbox" class="submodules" value="1" id="chkSubModule'.$subModuleCode.'" name="chkSubModule'.$subModuleCode.'"> &nbsp'.$subModuleRow->subModuleName.'</td></tr>';
				}		
				$modelHtml.='</table></td></tr>';				
        }
        $modelHtml.='</table>';
        print_r($modelHtml);  
    }

	public function getAllPrivileges(){
        echo $this->model->getAllModules();
    }
	
    public function saveRights(Request $r){
        $userCode=$r->userCode;
        $userRights=$r->userrights;
		$filename= 'assets/init_site/rights/'.$userCode.'.json'; 
		$insert=0;
		$resultArr = json_decode($userRights,true);
		if(empty($resultArr['ModulesData'])){
			if(file_exists($filename)){
				unlink($filename);
			}
			$insert=1;
		}else{
			$json = json_encode($userRights);
			if(file_put_contents($filename, $json)){  	
				$insert=1;
			}
		}
		if($insert==1){
            $response['status']=true;
            $response['message']= "User Rights Successfully Added";
        }else{
            $response['status']=false;
            $response['message']="Failed to add user rights";
        }
        echo json_encode($response);
    }

   public function getUserAccessEditList(Request $r){
        $userCode=$r->userCode;
		$filename= 'assets/init_site/rights/'.$userCode.'.json'; 
		if(file_exists($filename)){
			$json = file_get_contents($filename);
			if($json!=""){
				print_r(json_decode($json,true));
			}else{
				return false;
			}
        }else{
            return false;
        }
    }
	
}
