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

class CompanyInfoController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
		$data['companyinfo'] = $this->model->selectAllDataFromTable('companyinfo');
        return view('admin.companyinfo.list',$data);
    }
	
	 public function edit(){
		$data['companyinfo'] = $this->model->selectAllDataFromTable('companyinfo');
        return view('admin.companyinfo.edit',$data);
    }
    public function store(Request $r)
    {
		$ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$code=$r->code;
		$rules = array(
			'companyName' => 'required',
        );
        $messages = array(
            'companyName.required' => 'Company Name is required',
        );
		if($r->isBillingAddressSame==''){
			$xyz=0;
		}else{
			$xyz=$r->isBillingAddressSame;
		}
		$data=array(
			'companyName'=> trim($r->companyName),
			'companyRegNo'=> trim($r->companyRegNo),
			'contactNo'=> trim($r->contactNo),
			'alternateContactNo'=> trim($r->altContactNo),
			'email'=> trim($r->email),
			'shippingAddress'=> trim($r->shippingAddress),
			'shippingPinCode'=> trim($r->shippingPinCode),
			'shippingPlace'=> trim($r->shippingPlace),
			'shippingTaluka'=> trim($r->shippingTaluka),
			'shippingDistrict'=> trim($r->shippingDistrict),
			'shippingState'=> trim($r->shippingState),
			'shippingCountry'=> trim($r->shippingCountry),
			'xyz'=> trim($xyz),
			'billingAddress'=> trim($r->billingAddress),
			'billingPinCode'=> trim($r->billingPinCode),
			'billingPlace'=> trim($r->billingPlace),
			'billingTaluka'=> trim($r->billingTaluka),
			'billingDistrict'=> trim($r->billingDistrict),
			'billingState'=> trim($r->billingState),
			'billingCountry'=> trim($r->billingCountry),
			'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('admin')->user()->code,
		);
		$resultData=$this->model->doEdit($data,'companyinfo',$code);
		if($resultData){
				return redirect('companyinfo/list')->with('success', 'Company Info Updated successfully');
        }
        return back()->with('error', 'Failed to update company');
    }
}
