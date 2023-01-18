<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use App\Models\CustomAddressMaster;
use App\Models\Restaurants;
use App\Models\RestaurantHours;

class RestaurantController extends Controller
{
    public function __construct(GlobalModel $model) 
    {
        $this->model = $model;
    }
	
	public function index()
	{
		$data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		return view('admin.restaurant.list',$data);
	}
	
	public function add()
	{
		$tablecity = "citymaster";
		$tableentitycategory="entitycategory";
		$tablecusines="cuisinemaster";
		$tablebusinesstype="businesstypemaster";
        $city = $this->model->selectActiveDataFromTable($tablecity);
		$entitycategory = $this->model->selectActiveDataFromTable($tableentitycategory);
		$businesstype = $this->model->selectActiveDataFromTable($tablebusinesstype);
		$cuisines = $this->model->selectActiveDataFromTable($tablecusines);
		$tags = $this->model->selectActiveDataFromTable("tagmaster"); 
		return view('admin.restaurant.add',compact('city','entitycategory','cuisines','tags','businesstype'));
	}
	
	public function getAreaDetails(Request $request)
	{
		$code = $request->cityCode;
		$details = CustomAddressMaster::where('cityCode', $code)->where("isDelete","!=",1)->get();
		$html = "";
		if($details){
			$html = '<option value="">Select Area</option>';
			foreach($details as $customAddressItem)
			{
				$str='';
				if(old('addressCode') == $customAddressItem->code){
					$str = "selected";
				}
				$html .= '<option value="' . $customAddressItem->code . '"'.$str.'>' . $customAddressItem->place . '</option>';
			}
		}  
		echo $html; 
	}
	
	public function getRestaurantList(Request $req)
	{
		$search = $req->input('search.value');
		
		$ownername = $req->ownername;
		$firstName=$lastName='';
		if($ownername!="")
		{
			$ownerName=explode("_",$ownername);
			$firstName=$ownerName[0];
			$lastName=$ownerName[1];
		}
        $restname = $req->restname;
		$city = $req->city;
        $serviceable= $req->serviceable;
        $tableName = "restaurant";
        $orderColumns = array("restaurant.*", "citymaster.cityName as cityName", "entitycategory.entityCategoryName as entityCategoryName");
        $condition = array('restaurant.isDelete' => array('!=', 1),'restaurant.cityCode' => array('=', $city),'restaurant.manualIsServiceable' => array('=', $serviceable),'restaurant.code'=>array('=',$restname),'restaurant.firstName'=>array('=',$firstName),'restaurant.LastName'=>array('=',$lastName));
        $orderBy = array('restaurant' . '.id' => 'DESC');
        $join = array('citymaster' => array('citymaster.code', 'restaurant.cityCode'), 'entitycategory' => array('entitycategory.code', 'restaurant.entitycategoryCode'));
        $like = array('restaurant.entityName' => $search, 'citymaster.cityName' => $search,"restaurant.ownerContact"=>$search,"restaurant.firstName"=>$search,"restaurant.lastName"=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1; 
        $dataCount = 0;
        $data = array();
		if ($result && $result->count() > 0) {
		foreach ($result as $row) {
			
			$status = '<span class="badge badge-danger"> InActive </span>';
			if ($row->isActive == 1) {
				$status = '<span class="badge badge-success">Active</span>';
			}
			
		    $actionHtml = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("partner/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("partner/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
								<a class="dropdown-item" href="' . url("partner/getRestaurantHours/" . $row->code) . '"><i class="ti-time"></i> Hours</a>
								<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
							</div>
						</div>';
				
			if($row->manualIsServiceable==1){
				$toggle = '<input type="checkbox" class="toggle" data-size="mini" data-seq="' . $row->isServiceable . '" id="' . $row->code . '" checked>';
			} else{
				$toggle = '<input type="checkbox" class="toggle" data-size="mini" data-seq="' . $row->isServiceable . '" id="' . $row->code . '">';
			}
			$data[] = array(
				$srno,
				$row->code,
				$row->firstName.' '.$row->middleName.' '.$row->lastName,
				$row->entityName,
				$row->cityName,
				$row->ownerContact,
				$toggle,
				$status,
				$actionHtml
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
	
	public function changeServiceable(Request $r)
	{
		$code = $r->code;
		$flag = $r->flag;
		$resultData = Restaurants::where('code', $code)->update([
                     'manualIsServiceable' => $flag,'isServiceable' => $flag]);
		if ($resultData == true) echo true;
		else echo false;
	}
	
	public function checkDuplicateemail(Request $req)
    {
        $email = $req->email;
		$duplicateEmailCheck = Restaurants::where('email', $email)->first();
        if ($duplicateEmailCheck) {
            return response()->json(['status' => 'true', 'success' => 'The Email has already been taken']);
        } else {
            return response()->json(['status' => 'false']);
        }
    }
	
	public function checkDuplicatemobile(Request $req)
    {
        $mobile = $req->mobile;
		$duplicateMobileCheck = Restaurants::where('ownerContact', $mobile)->first();
        if ($duplicateMobileCheck) {
            return response()->json(['status' => 'true', 'success' => 'Mobile number has already been taken']);
        } else {
            return response()->json(['status' => 'false']);
        }
    }
	
	public function checkDuplicateemailOnUpdate(Request $req)
    {
        $email = $req->email;
		$code = $req->code;
		$duplicateEmailCheck = Restaurants::where('email', $email)->where('code','!=',$code)->first();
        if ($duplicateEmailCheck) {
            return response()->json(['status' => 'true', 'success' => 'The Email has already been taken']);
        } else {
            return response()->json(['status' => 'false']);
        }
    }
	
	public function checkDuplicatemobileOnUpdate(Request $req)
    {
        $mobile = $req->mobile;
		$code = $req->code;
		$duplicateMobileCheck = Restaurants::where('ownerContact', $mobile)->where('code','!=',$code)->first();
        if ($duplicateMobileCheck) {
            return response()->json(['status' => 'true', 'success' => 'Mobile number has already been taken']);
        } else {
            return response()->json(['status' => 'false']);
        }
    }
	
	public function deleteImage(Request $r)
	{
		$imgNm = $r->value;
		$code = $r->code;
		$type = $r->type;
		if ($type == 'entityImage') {
			$data = array(
				'entityImage' => '',
			);
			if (!empty($data)) {
			   unlink('uploads/restaurant/restaurantimage/'. $imgNm);
			   echo $resultData = $this->model->doEdit($data, 'restaurant', $code);
			} else {
				echo 'false';
			}
		} else if ($type == 'gstImage') {
			$data = array(
				'gstImage' => '',
			);
			if (!empty($data)) {
			   unlink('uploads/restaurant/gstimage/'. $imgNm);
			   echo $resultData = $this->model->doEdit($data, 'restaurant', $code);
			} else {
				echo 'false';
			}
		} else if ($type == 'fssaiImage') {
			$data = array(
				'fssaiImage' => '',
			);
			if (!empty($data)) {
			   unlink('uploads/restaurant/fssaiimage/'. $imgNm);
			   echo $resultData = $this->model->doEdit($data, 'restaurant', $code);
			} else {
				echo 'false';
			}
		}
	}
	public function store(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$rules = array(
		    'firstName'=>'required|regex:/^[a-zA-Z\s]+$/',
            'middleName' => 'nullable|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|regex:/^[a-zA-Z\s]+$/',
			'entitycategoryCode'=>'required',
			'entityEmail'=>'required',
			'businesstype'=>'required',
			'city'=>'required',
			'addressCode'=>'required',
			'entityName' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:200',
			'ownerContact' => 'required|digits:10|regex:^[123456789]\d{9}$^',
			'entityContact' => 'nullable|digits:10|regex:^[123456789]\d{9}$^',
			'address'=>'required|min:10|max:800',
			'fssaiNumber'=>'required',
			'latitude'=>'required',
			'longitude'=>'required',
			'entityImage' => 'nullable|image|mimes:jpg,png,jpeg',
			'fssaiImage' =>'nullable|image|mimes:jpg,png,jpeg',
			'gstImage' =>'nullable|image|mimes:jpg,png,jpeg',
			//'cartPackagingPrice'=>'nullable|gt:0',
        );
        $messages = array(
		    'firstName.required' => 'First name is required',
            'firstName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'firstName.min' => 'Minimum of 3 characters are required.',
            //'firstName.max' => 'Max characters exceeded.',
			'entityName.required' => 'Restaurant name is required',
            'entityName.regex' => 'Invalid characters like number, special characters are not allowed',
            'entityName.min' => 'Minimum of 3 characters are required.',
            'entityName.max' => 'Max characters exceeded.',
            'middleName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'middleName.min' => 'Minimum of 3 characters are required.',
            //'middleName.max' => 'Max characters exceeded.',
			'lastName.required' => 'Last name is required',
            'lastName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'lastName.min' => 'Minimum of 3 characters are required.',
            //'lastName.max' => 'Max characters exceeded.',
			'entitycategoryCode.required' =>'Restaurant Category is required.',
			'city.required'=>'City is required.',
			'addressCode.required' => 'Area is required.',
			'ownerContact.required' =>'Owner Contact is required.',
			'ownerContact.digits' => 'Required ten digit contact number',
            'ownerContact.regex' => 'Enter valid mobile number',
			'entityContact.required' =>'Owner Contact is required.',
			'entityContact.digits' => 'Required ten digit contact number',
            'entityContact.regex' => 'Enter valid mobile number',
			'address.required' =>'Address is required.',
			'businesstype.required' =>'Business Type is required.',
			'address.min' =>  'Minimum of 10 characters are required.',
			'address.max' =>'Max characters exceeded.',
			'fssaiNumber.required' =>'FSSAI Number is required.',
			'latitude.required'=>'Latitude is required.',
			'longitude.required'=>'Longitude is required',
			//'cartPackagingPrice.gt'=>'Cart Packaging Price is must be greater than 0.',
			'entityEmail.required' =>'Restaurant email is required.',
        );
		$this->validate($r, $rules, $messages); 
		$table = "restaurant";
		$firstName = ucwords($r->firstName);
		$middleName = ucwords($r->middleName);
		$lastName = ucwords($r->lastName);
		$entityName= ucwords($r->entityName);
		$city=$r->city;
		$addressCode=$r->addressCode;
		$ownerContact=$r->ownerContact;
		$entityContact=$r->entityContact;
		$address=$r->address;
		$fssaiNumber=$r->fssaiNumber;
		$latitude=$r->latitude;
		$longitude=$r->longitude;
		$entitycategoryCode= $r->entitycategoryCode;
		$packagingType=$r->packagingType;
		$cartPackagingPrice=$r->cartPackagingPrice;
		$entityEmail=$r->entityEmail;
		$businesstype=$r->businesstype;
		
		$bankDetails['beneficiaryName'] = ucwords($r->beneficiaryName);
		$bankDetails['bankName'] =  trim($r->bankName);
		$bankDetails['accountNumber'] =  trim($r->accountNumber);
		$bankDetails['ifscCode'] = trim($r->ifscCode);
		$bankDetails = json_encode($bankDetails);
		
		$gstApplicable = trim($r->gstApplicable);
		$gstPercent = trim($r->gstPercent);
		$gstNumber=trim($r->gstNumber);
		$commission=trim($r->commission);
		$panNumber=trim($r->panNumber);
		$data = [
		    'firstName' =>$firstName,
            'middleName' =>$middleName,
            'lastName' => $lastName,
			'entityName' => $entityName,
			'entitycategoryCode' =>$entitycategoryCode,
			'password' =>Hash::make('123456'),
			'cityCode'=>$city,
			'addressCode'=>$addressCode,
			'ownerContact'=>$ownerContact,
			'entityContact'=>$entityContact,
			'address'=>$address,
			'fssaiNumber'=>$fssaiNumber,
			'email'=>$entityEmail,
			'latitude'=>$latitude,
			'longitude' =>$longitude,
			'packagingType'=>$packagingType,
			'cartPackagingPrice'=>$cartPackagingPrice,
			'businessTypeCode'=>$businesstype,
			'bankDetails' =>$bankDetails,
			'gstNumber'=>$gstNumber,
			'commission'=>$commission =="" ? 0.00 : $commission,
			'panNumber'=>$panNumber,
			'gstPercent'=>$gstPercent =="" ? 0.00 : $gstPercent,
			'gstApplicable'=>$gstApplicable,
            'isActive' => $r->isActive == "" ? '0' : 1,
			'manualIsServiceable'=>$r->isServiceable == "" ? '0' : 1, 
			'isServiceable'=>$r->isServiceable == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
        ];
		/*if($r->tagCode ==1 ){
					$data['tagCode']=NULL;
		}else{
			$data['tagCode']=$r->tagCode;
		}*/
		
		$currentId = $this->model->addNew($data, $table, 'RES');
		
		if ($r->hasFile('entityImage')) { 
            $fileEntityImage =  $r->file('entityImage');       
            $filenameEntityImage = $currentId. '-' . time() . '.' . $fileEntityImage->getClientOriginalExtension();          
            $fileEntityImage->move("uploads/restaurant/restaurantimage",  $filenameEntityImage);
			$EntityImage = ['entityImage' =>$filenameEntityImage];
            $image_update = $this->model->doEdit($EntityImage, $table, $currentId);
        }
		if ($r->hasFile('fssaiImage')) { 
            $filefssaiImage =  $r->file('fssaiImage');       
            $filenamefssaiImage = $currentId. '-' . time() . '.' . $filefssaiImage->getClientOriginalExtension();          
            $filefssaiImage->move("uploads/restaurant/fssaiimage", $filenamefssaiImage);
			$fssaiImage=['fssaiImage' =>$filenamefssaiImage];
			$image_update = $this->model->doEdit($fssaiImage, $table, $currentId);
        }
		if ($r->hasFile('gstImage')) { 
            $filesgstImage =  $r->file('gstImage');       
            $filenamegstImage = $currentId. '-' . time() . '.' . $filesgstImage->getClientOriginalExtension();          
            $filesgstImage->move("uploads/restaurant/gstimage", $filenamegstImage);
			$gstImage=['gstImage' =>$filenamegstImage];
			$image_update = $this->model->doEdit($gstImage, $table, $currentId); 
        }
		
		if ($currentId) {
			$cuisines = $r->cuisineCode;
			$addressData = array();
			$this->model->deleteForeverFromField('vendorCode',$currentId,'restaurantcuisinelineentries');
			for ($i = 0; $i < sizeof($cuisines); $i++) {
				$addressData = array('vendorCode' => $currentId, 'cuisineCode' => $cuisines[$i], 'isActive' => $r->isActive == "" ? '0' : 1,'isDelete' => 0,'addIP' => $ip,'addDate' => $currentdate->toDateTimeString(),'addID' => Auth::guard('admin')->user()->code);
				$addLineDataResult = $this->model->addNew($addressData, 'restaurantcuisinelineentries', 'RCLE');
				if ($addLineDataResult == 'true') {
					$addResultFlag = true;
				}
			}
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Restaurant ".$currentId." is added";
			$this->model->activity_log($data); 
			//activity log end
			
            return redirect('partner/list')->with('success', 'Record added successfully');
        }
        return back()->with('error', 'Failed to add the record');    
	}
	
	public function delete(Request $request)
	{
		$id = $request->id;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$today = date('Y-m-d');
		$table = 'restaurant';
		$data = ['isActive' => 0,'isDelete' => 1,'deleteIP'=>$ip,'deleteID'=>Auth::guard('admin')->user()->code,'deleteDate'=>$currentdate]; 
		$deleteQuery = $this->model->doEdit($data, $table, $id);
		
		//activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Restaurant ".$id." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->updateOnDeleteRecordWithId($id, $table); 
	}
	
	public function edit(Request $request, $code)
	{
		$query = $this->model->selectDataByCode('restaurant', $code);
		$dayofweek = strtolower(date('l'));
        $currtime = date('H:i:s');
		
		$data['resFlag']=0;
		$resResult = RestaurantHours::select("restauranthours.restaurantCode")            
            ->where('restauranthours.weekDay', $dayofweek)
			->where('restauranthours.restaurantCode',$code)
            ->whereRaw("(CAST( ' " . $currtime . "' AS time ) BETWEEN `restauranthours`.`fromTime` AND `restauranthours`.`toTime` OR ( NOT CAST( ' " . $currtime . "' AS time ) BETWEEN `restauranthours`.`toTime` AND `restauranthours`.`fromTime` AND `restauranthours`.`fromTime` > `restauranthours`.`toTime`))")      
            ->first();
		if(!empty($resResult)){
			$data['resFlag']=1;
		}
		$cityCode = $query->cityCode; 
		$data['restaurant'] = $query;
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['businesstype']=$this->model->selectActiveDataFromTable('businesstypemaster');
		$data['address'] = $this->model->selectQuery(array('customaddressmaster.place','customaddressmaster.code'),'customaddressmaster',array(),array("customaddressmaster.isActive"=>array('=',1),"customaddressmaster.cityCode"=>array('=',$cityCode)));
		$data['entitycategory'] = $this->model->selectActiveDataFromTable('entitycategory');
		$data['cuisines'] = $this->model->selectActiveDataFromTable('cuisinemaster');
		$data['cuisines_entry'] = DB::table("restaurantcuisinelineentries")
		                         ->select(DB::raw("(GROUP_CONCAT(restaurantcuisinelineentries.cuisineCode SEPARATOR ',')) as `cuisineCode`"))
								 ->where('restaurantcuisinelineentries.vendorCode', $code)
								 ->get();
		$data['tags'] = $this->model->selectActiveDataFromTable("tagmaster"); 
        return view('admin.restaurant.edit', $data);  
	}
	
	public function view(Request $request, $code)
	{
		$data['businesstype']=$this->model->selectActiveDataFromTable('businesstypemaster');
		$data['restaurant'] = $this->model->selectDataByCode('restaurant', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['address'] = $this->model->selectActiveDataFromTable('customaddressmaster');
		$data['entitycategory'] = $this->model->selectActiveDataFromTable('entitycategory');
		$data['cuisines'] = $this->model->selectActiveDataFromTable('cuisinemaster');
		$data['cuisines_entry'] = DB::table("restaurantcuisinelineentries")
		                         ->select(DB::raw("(GROUP_CONCAT(restaurantcuisinelineentries.cuisineCode SEPARATOR ',')) as `cuisineCode`"))
								 ->where('restaurantcuisinelineentries.vendorCode', $code)
								 ->get();
		$data['tags'] = $this->model->selectActiveDataFromTable("tagmaster"); 
        return view('admin.restaurant.view', $data);  
	}
	
	public function update(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$code=$r->code;
        $currentdate = Carbon::now();
		$rules = array(
		    'firstName'=>'required|regex:/^[a-zA-Z\s]+$/',
            'middleName' => 'nullable|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|regex:/^[a-zA-Z\s]+$/', 
			'entitycategoryCode'=>'required',
			'entityEmail'=>'required',
			'city'=>'required',
			'addressCode'=>'required',
			'businesstype'=>'required',
			'entityName' => 'required|regex:/^[a-zA-Z\s]+$/|min:3|max:200',
			'ownerContact' => 'required|digits:10|regex:^[123456789]\d{9}$^',
			'entityContact' => 'nullable|digits:10|regex:^[123456789]\d{9}$^',
			'address'=>'required|min:10|max:800',
			'fssaiNumber'=>'required',
			'latitude'=>'required',
			'longitude'=>'required',
			'entityImage' => 'nullable|image|mimes:jpg,png,jpeg',
			'fssaiImage' =>'nullable|image|mimes:jpg,png,jpeg',
			'gstImage' =>'nullable|image|mimes:jpg,png,jpeg',
			//'cartPackagingPrice'=>'nullable|gt:0',
        );
        $messages = array(
		    'firstName.required' => 'First name is required',
            'firstName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'firstName.min' => 'Minimum of 3 characters are required.',
            //'firstName.max' => 'Max characters exceeded.',
			'entityName.required' => 'Restaurant name is required',
            'entityName.regex' => 'Invalid characters like number, special characters are not allowed',
            'entityName.min' => 'Minimum of 3 characters are required.',
            'entityName.max' => 'Max characters exceeded.',
            'middleName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'middleName.min' => 'Minimum of 3 characters are required.',
            //'middleName.max' => 'Max characters exceeded.',
			'lastName.required' => 'Last name is required',
            'lastName.regex' => 'Invalid characters like number, special characters are not allowed',
            //'lastName.min' => 'Minimum of 3 characters are required.',
            //'lastName.max' => 'Max characters exceeded.',
			'entitycategoryCode.required' =>'Restaurant Category is required.',
			'city.required'=>'City is required.',
			'addressCode.required' => 'Area is required.',
			'ownerContact.required' =>'Owner Contact is required.',
			'ownerContact.digits' => 'Required ten digit contact number',
            'ownerContact.regex' => 'Enter valid mobile number',
			'entityContact.required' =>'Owner Contact is required.',
			'entityContact.digits' => 'Required ten digit contact number',
            'entityContact.regex' => 'Enter valid mobile number',
			'address.required' =>'Address is required.',
			'address.min' =>  'Minimum of 10 characters are required.',
			'address.max' =>'Max characters exceeded.',
			'fssaiNumber.required' =>'FSSAI Number is required.',
			'latitude.required'=>'Latitude is required.',
			'longitude.required'=>'Longitude is required',
			'businesstype.required'=>'Business Type is required',
			//'cartPackagingPrice.gt'=>'Cart Packaging Price is must be greater than 0.',
			'entityEmail.required' =>'Restaurant email is required.',
        );
		$this->validate($r, $rules, $messages); 
		
		$table = "restaurant";
		$firstName = ucwords($r->firstName);
		$middleName = ucwords($r->middleName);
		$lastName = ucwords($r->lastName);
		$entityName= ucwords($r->entityName);
		$city=$r->city;
		$addressCode=$r->addressCode;
		$ownerContact=$r->ownerContact;
		$entityContact=$r->entityContact;
		$address=$r->address;
		$fssaiNumber=$r->fssaiNumber;
		$latitude=$r->latitude;
		$longitude=$r->longitude;
		$entitycategoryCode= $r->entitycategoryCode;
		$packagingType=$r->packagingType;
		$cartPackagingPrice=$r->cartPackagingPrice;
		$entityEmail=$r->entityEmail;
		$businesstype=$r->businesstype;
		$bankDetails['beneficiaryName'] = ucwords($r->beneficiaryName);
		$bankDetails['bankName'] =  trim($r->bankName);
		$bankDetails['accountNumber'] =  trim($r->accountNumber);
		$bankDetails['ifscCode'] = trim($r->ifscCode);
		$bankDetails = json_encode($bankDetails);
		
		$gstApplicable = trim($r->gstApplicable);
		$gstPercent = trim($r->gstPercent);
		$gstNumber=trim($r->gstNumber);
		$commission=trim($r->commission);
		$panNumber=trim($r->panNumber);
		$data = [
		    'firstName' =>$firstName,
            'middleName' =>$middleName,
            'lastName' => $lastName,
			'entityName' => $entityName,
			'entitycategoryCode' =>$entitycategoryCode,
			'password' =>Hash::make('123456'),
			'cityCode'=>$city,
			'addressCode'=>$addressCode,
			'ownerContact'=>$ownerContact,
			'entityContact'=>$entityContact,
			'address'=>$address,
			'fssaiNumber'=>$fssaiNumber,
			'email'=>$entityEmail,
			'latitude'=>$latitude,
			'longitude' =>$longitude,
			'packagingType'=>$packagingType,
			'cartPackagingPrice'=>$cartPackagingPrice,
			'bankDetails' =>$bankDetails,
			'businessTypeCode'=>$businesstype,
			'gstNumber'=>$gstNumber,
			'commission'=>$commission,
			'panNumber'=>$panNumber,
			'gstPercent'=>$gstPercent =="" ? 0.00 : $gstPercent,
			'gstApplicable'=>$gstApplicable,
            'isActive' => $r->isActive == "" ? '0' : 1,
			'manualIsServiceable'=>$r->isServiceable == "" ? '0' : 1, 
			'isServiceable'=>$r->isServiceable == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('admin')->user()->code, 
        ];
		/*if($r->tagCode ==1 ){
					$data['tagCode']=NULL;
		}else{
			$data['tagCode']=$r->tagCode;
		}
	*/
		$result = $this->model->doEdit($data, $table, $code);
		if ($r->hasFile('entityImage')) { 
            $fileEntityImage =  $r->file('entityImage');       
            $filenameEntityImage = $code. '-' . time() . '.' . $fileEntityImage->getClientOriginalExtension();          
            $fileEntityImage->move("uploads/restaurant/restaurantimage",  $filenameEntityImage);
			$EntityImage = ['entityImage' =>$filenameEntityImage];
            $enitity_image_update = $this->model->doEdit($EntityImage, $table, $code);
        }
		if ($r->hasFile('fssaiImage')) { 
            $filefssaiImage =  $r->file('fssaiImage');       
            $filenamefssaiImage = $code. '-' . time() . '.' . $filefssaiImage->getClientOriginalExtension();          
            $filefssaiImage->move("uploads/restaurant/fssaiimage", $filenamefssaiImage);
			$fssaiImage=['fssaiImage' =>$filenamefssaiImage];
			$fssai_image_update = $this->model->doEdit($fssaiImage, $table, $code);
        }
		if ($r->hasFile('gstImage')) { 
            $filesgstImage =  $r->file('gstImage');       
            $filenamegstImage = $code. '-' . time() . '.' . $filesgstImage->getClientOriginalExtension();          
            $filesgstImage->move("uploads/restaurant/gstimage", $filenamegstImage);
			$gstImage=['gstImage' =>$filenamegstImage];
			$gst_image_update = $this->model->doEdit($gstImage, $table, $code); 
        }
		
		if ($result == true || $enitity_image_update == true || $fssai_image_update == true|| $gst_image_update == true) {
			$this->model->deleteForeverFromField('vendorCode',$code,'restaurantcuisinelineentries');
			//$deleteCusineLineEntry=$this->model->deletePermanent($code,'restaurantcuisinelineentries');
			$cuisines = $r->cuisineCode;
			$addressData = array();
			for ($i = 0; $i < sizeof($cuisines); $i++) {
				$addressData = array('vendorCode' => $code, 'cuisineCode' => $cuisines[$i], 'isActive' => $r->isActive == "" ? '0' : 1,'isDelete' => 0,'addIP' => $ip,'addDate' => $currentdate->toDateTimeString(),'addID' => Auth::guard('admin')->user()->code);
				$addLineDataResult = $this->model->addNew($addressData, 'restaurantcuisinelineentries', 'RCLE');
				if ($addLineDataResult == 'true') {
					$addResultFlag = true;
				}
			}
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Restaurant ".$code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
			
            return redirect()->back()->with('status', ['message' => "Restaurant Updated Successfully"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong"]);
        }
		
	}
	
	public function getRestaurantHours(Request $r)
	{
		$code=$r->code;
		$data['restaurant'] = Restaurants::where('code', $code)->first();
		$data['restauranthours'] = RestaurantHours::where('restaurantCode', $code)->get();
		return view('admin.restaurant.restauranthours',$data); 
	}
	
	public function updateRestaurantHour(Request $r)
	{
		$restCode = trim($r->restCode);
		$weekDay = trim($r->weekDay);
		$fromTime =trim($r->fromTime);
		$toTime = trim($r->toTime);
		$fromTime = date('H:i:00',strtotime($fromTime));
		$toTime = date('H:i:00',strtotime($toTime));
		$ip = $_SERVER['REMOTE_ADDR'];
		$code=$r->lineCode;
		$checkTime = false;
		if($toTime < $fromTime){
			$response['status']  = false;
			$response['message'] = 'From time must be greater than to time.';
			
		}else{
		   DB::enableQueryLog();
		   $checkTime=RestaurantHours::whereRaw('((TIME("'.$fromTime.'") BETWEEN restauranthours.fromTime and restauranthours.toTime) or (TIME("'.$toTime.'") BETWEEN restauranthours.fromTime and restauranthours.toTime))')->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->get();
		   $query_1 = DB::getQueryLog();
           //print_r($query_1);
			if(count($checkTime)>0){
				$response['status']  = false;
				$response['message'] = 'Please select another time slots.';
			}else{
				$deleteQuery=RestaurantHours::where('fromTime','>',$fromTime)->where('toTime','<', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->delete();
				$data = array(
				  'fromTime' => $fromTime,
				  'toTime' => $toTime,
				  'editID' => Auth::guard('admin')->user()->code, 
				  'editIP' => $ip,
				  'isActive' => 1,
				);
				$result = $this->model->doEdit($data, 'restauranthours', $code);
				if ($result != false) {
				  $response['status']  = true;
				  $response['message'] = 'Restaurant Hours Added Successfully';
				} else {
				  $response['status']  = false;
				  $response['message'] = 'Failed to add Restaurant Hours';
				}
			}
			
			
		}
		echo json_encode($response);
	}
	
	public function updateRestaurantHourOld(Request $r)
	{
		$restCode=$r->restCode;
		$code=$r->lineCode;
		$time=$r->time;
		$updateTo=$r->updateTo;
		$weekDay=$r->weekDay;
		$invalid=0;
		$ip = $_SERVER['REMOTE_ADDR'];
		if ($updateTo=="to"){
			$totime=$time;
			$toTime = date('H:i:s',strtotime($totime));
				$checkTime=RestaurantHours::where('fromTime','<',$toTime)->where('totime','>', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();
				if($checkTime){
					$invalid=1;
				}
				$data = array(   
					'toTime' => $toTime,
					'editID' => Auth::guard('admin')->user()->code,
					'editIP' => $ip,
					'isActive' => 1,
				);
					
		}else{
			$fromtime=$time;
            $fromTime = date('H:i:s',strtotime($fromtime));	
				$checkTime=RestaurantHours::where('fromTime','<',$fromTime)->where('totime','>', $fromTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();	
				if($checkTime){
					$invalid=1;
				}
				$data = array(  
					'fromTime' => $fromTime, 
					'editID' => Auth::guard('admin')->user()->code,
					'editIP' => $ip,
					'isActive' => 1,
				);
					
		}
		
		if($invalid==1){
			$response['status']  = false;
			$response['message'] = 'Please select another time slots.';	
		}else{
			$code = $this->model->doEdit($data, 'restauranthours', $code);
			//echo $code;
			if ($code != false) {     
			  $response['status']  = true; 
			  $response['message'] = 'Restaurant Hour updated successfully';
			} else {
			  $response['status']  = false;
			  $response['message'] = 'Failed to update Restaurant Hour';
			} 
		}
		echo json_encode($response);    
	}
	
	public function saveHours(Request $r)
	{
		$restCode = trim($r->restCode);
		$weekDay = trim($r->weekDay);
		$fromTime =trim($r->fromTime);
		$toTime = trim($r->toTime);
		$fromTime = date('H:i:00',strtotime($fromTime));
		$toTime = date('H:i:00',strtotime($toTime));
		$ip = $_SERVER['REMOTE_ADDR'];
		$checkTime = false;
		if($toTime < $fromTime){
			$response['status']  = false;
			$response['message'] = 'From time must be greater than to time.';
			
		}else{ 
			/*$checkTime=RestaurantHours::where('fromTime','<',$fromTime)->where('toTime','>',$fromTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->first();
			if($checkTime){}else{
			   $checkTime=RestaurantHours::where('fromTime','<',$toTime)->where('toTime','>', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->first();
			}*/
		   DB::enableQueryLog();
		   $checkTime=RestaurantHours::whereRaw('((TIME("'.$fromTime.'") BETWEEN restauranthours.fromTime and restauranthours.toTime) or (TIME("'.$toTime.'") BETWEEN restauranthours.fromTime and restauranthours.toTime))')->where('weekday',$weekDay)->where('restaurantCode',$restCode)->get();
		   $query_1 = DB::getQueryLog();
           // print_r($query_1);
			if(count($checkTime)>0){
				$response['status']  = false;
				$response['message'] = 'Please select another time slots.';
			}else{
				$deleteQuery=RestaurantHours::where('fromTime','>',$fromTime)->where('toTime','<', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->delete();
				$data = array(
				  'restaurantCode' => $restCode,
				  'weekDay' => $weekDay,
				  'fromTime' => $fromTime,
				  'toTime' => $toTime,
				  'addID' => Auth::guard('admin')->user()->code,
				  'addIP' => $ip,
				  'isActive' => 1,
				);
				$code = $this->model->addNew($data, 'restauranthours', 'VHLE');
				if ($code != 'false') {
				  $response['status']  = true;
				  $response['lineCode']  = $code;
				  $response['message'] = 'Restaurant Hours Added Successfully';
				} else {
				  $response['status']  = false;
				  $response['message'] = 'Failed to add Restaurant Hours';
				}
			}
	  }
		echo json_encode($response);
	}
	
	public function deleteHourLine(Request $r)     
	{
		$code=$r->lineCode;
		$ip = $_SERVER['REMOTE_ADDR'];
		$query=$this->model->deletePermanent($code,'restauranthours');	
		if($query){
			$res['status'] = true;
		} else {
			$res['status'] = false;
		}
	    echo json_encode($res);
		
	}
	
	
}
