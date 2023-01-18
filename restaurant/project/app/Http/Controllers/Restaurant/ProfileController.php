<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Restaurants;
use DB;

class ProfileController extends Controller
{
     public function __construct(GlobalModel $model) 
    {
        $this->model = $model;
    }
	public function view(Request $req,$code)
	{
		$data['restaurant'] = $this->model->selectDataByCode('restaurant', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['address'] = $this->model->selectActiveDataFromTable('customaddressmaster');
		$data['entitycategory'] = $this->model->selectActiveDataFromTable('entitycategory');
		$data['cuisines'] = $this->model->selectActiveDataFromTable('cuisinemaster');
		$data['cuisines_entry'] = DB::table("restaurantcuisinelineentries")
		                         ->select(DB::raw("(GROUP_CONCAT(restaurantcuisinelineentries.cuisineCode SEPARATOR ',')) as `cuisineCode`"))
								 ->where('restaurantcuisinelineentries.vendorCode', $code)
								 ->get();
		return view('restaurant.profile.view', $data);
	}
	
	public function viewProfile(Request $req,$code)
	{
		$data['restaurant'] = $this->model->selectDataByCode('restaurant', $code);
        $data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		$data['address'] = $this->model->selectActiveDataFromTable('customaddressmaster');
		$data['entitycategory'] = $this->model->selectActiveDataFromTable('entitycategory');
		$data['cuisines'] = $this->model->selectActiveDataFromTable('cuisinemaster');
		$data['cuisines_entry'] = DB::table("restaurantcuisinelineentries")
		                         ->select(DB::raw("(GROUP_CONCAT(restaurantcuisinelineentries.cuisineCode SEPARATOR ',')) as `cuisineCode`"))
								 ->where('restaurantcuisinelineentries.vendorCode', $code)
								 ->get();
		return view('restaurant.profile.edit', $data);
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
	
	public function update(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$table="restaurant";
		$code=Auth::guard('restaurant')->user()->code;
		$rules = array(
			'ownerContact' => 'required|digits:10|regex:^[123456789]\d{9}$^',
			'password' => 'min:6|confirmed',
			'password_confirmation' => 'required',
        );
		$messages = array(
		    'ownerContact.required' => 'Owner Contact is required',
			'password.required' =>'Password is required',
			'password_confirmation.required' => 'required', 
			'password.confirmed' => 'Password does not match',
	    );
		$this->validate($r, $rules, $messages);
		
		$data = [
                'ownerContact' => $r->ownerContact,
                'password' =>Hash::make($r->password),
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' =>  Auth::guard('restaurant')->user()->code,
            ];
			
		$result = $this->model->doEdit($data, $table, $code);
		if ($result == true) {
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Profile".$code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect()->back()->with('status', ['message' => "Profile Updated Successfully","status"=>"true"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong","status"=>"false"]);
        }
	}  
	
	public function configUpdate(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$table="restaurant";
		$code = Auth::guard('restaurant')->user()->code;
		$packagingType = trim($r->packagingType);
		$cartPackagingPrice = trim($r->cartPackagingPrice);
        $rules = array(
			'packagingType' => 'required',
        );
		$messages = array(
		    'packagingType.required' => 'PackagingType is required',
	    );
		if($packagingType=="CART"){
			 $rules = array(
			'cartPackagingPrice' => 'required',
			);
			$messages = array(
				'cartPackagingPrice.required' => 'Cart Packaging Price is required',
			);
		}
		$this->validate($r, $rules, $messages);	
        $data = array( 
				'cartPackagingPrice' => $cartPackagingPrice,
				'packagingType' => $packagingType,					 
				'editID'=>$code,
				'editIP'=>$ip, 
		); 					 
        $result = $this->model->doEdit($data, $table, $code);
		if ($result == true) {
            return redirect()->back()->with('status', ['message' => "Packaging Charges Updated Successfully","status"=>"true"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong","status"=>"false"]);
        }  		
		 
	}
}
