<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;
use App\Models\RestaurantOffer;

class RestaurantCouponController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        $code= Auth::guard('restaurant')->user()->code;
		$data['restaurantcoupon'] = RestaurantOffer::where('restaurantCode', $code)->where("isDelete","!=",1)->get();
        return view('restaurant.restaurantcoupon.list',$data);
    }

    public function getCouponList(Request $req)
    {
        $search = $req->input('search.value');
        $code= Auth::guard('restaurant')->user()->code;
		$offerType = $req->offerType;
		$couponCode = $req->couponCode;
        $tableName = "restaurantoffer";
        $orderColumns = array("restaurantoffer.*","restaurant.entityName");
        $condition = array('restaurantoffer.isDelete' => array('!=', 1),"restaurantoffer.restaurantCode"=>array('=',$code),'restaurantoffer.offerType'=>array('=',$offerType),'restaurantoffer.couponCode'=>array('=',$couponCode));
        $orderBy = array('restaurantoffer.id' => 'DESC');
        $join = array('restaurant' => array('restaurant.code', 'restaurantoffer.restaurantCode'));
		$like = array('restaurant.entityName' => $search, 'restaurantoffer.couponCode' => $search, 'restaurantoffer.offerType' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition);
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
				if ($row->isAdminApproved == 1) {
					$approved = "<span class='badge badge-success'>Yes</span>";
				} else {
					$approved = "<span class='badge badge-danger'>No</span>";
				}
				if ($row->offerType == 'cap') {
                    $offerType = 'Cap';
                } else {
                    $offerType = $row->offerType;
                }
				if ($row->offerType == 'flat') {
                    $discount = $row->flatAmount . ' ₹';
                } else {
                    $discount = $row->discount . ' %';
                }
				$actions = '<div class="btn-group">
							<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="ti-settings"></i>
							</button>
							<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
								<a class="dropdown-item" href="' . url("restaurantoffer/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
								<a class="dropdown-item" href="' . url("restaurantoffer/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
							</div>
						</div>';
                $data[] = array(
                   $srno, 
				    $row->code,
					$row->couponCode,
					ucfirst($offerType),
					$discount,
					$row->minimumAmount,
					$row->applyOn,
					$approved,
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
		$rescode= Auth::guard('restaurant')->user()->code;
		$condition = array('restaurantoffer.code' => array('=', $code));
		$join = array('restaurant' => array('restaurant.code', 'restaurantoffer.restaurantCode'));
        $data['restaurantcoupons'] = $this->model->selectQuery(array('restaurantoffer.*','restaurant.entityName'),'restaurantoffer', $join,$condition);
        $data['restaurants'] = $this->model->selectActiveDataFromTable('restaurant');
        $data['items'] = $this->model->selectQuery(array('restaurantitemmaster.code','restaurantitemmaster.itemName'),'restaurantitemmaster', array(),array('restaurantitemmaster.isActive'=>array("=",1),"restaurantitemmaster.restaurantCode"=>array("=",$rescode)));
		$data['itemEntries'] = DB::table("offerincludeditems")
		                         ->select(DB::raw("(GROUP_CONCAT(offerincludeditems.itemCode SEPARATOR ',')) as `itemsCodes`"))
								 ->where('offerincludeditems.offerCode', $code)
								 ->get();
		return view('restaurant.restaurantcoupon.edit', $data); 
    }

    public function update(Request $r)
    {
		//$fromDateFormatS = str_replace('/', '-', $r->startDate);
		$startDate = date('Y-m-d', strtotime($r->startDate));
		//$fromDateFormat = str_replace('/', '-', $r->endDate);
		$endDate = date('Y-m-d', strtotime($r->endDate));

		$code = trim($r->code);
		$couponCode = trim($r->couponCode);
		$offerType = trim($r->offerType);
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'couponCode' => 'required',
            'offerType' => 'required',
            'minimumAmount' => 'required',
            'perUserLimit' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        );
		if ($offerType == "cap") {
			$rules['discount'] = 'required';
			$rules['capLimit'] = 'required';
        }
        if ($offerType == "flat") {
            $rules['flatAmount'] = 'required';
        }
        $messages = array(
            'couponCode.required' => 'Coupon Code is required',
            'offerType.required' => 'Offer Type is required',
            'minimumAmount.required' => 'Minimum Amount is required',
            'perUserLimit.required' => 'Per User Limit is required',
            'startDate.required' => 'Start Date is required',
            'endDate.required' => 'End Date is required',
        );
		if ($offerType == "cap") {
			$messages['discount.required'] = 'Discount is required';
			$messages['capLimit.required'] = 'Cap Limit is required';
        }
        if ($offerType == "flat") {
            $messages['flatAmount.required'] = 'Flat Amount is required';
        }
		
		$this->validate($r, $rules, $messages);
        $data = [
			'couponCode' => $couponCode,
			'offerType' => $offerType,
			'minimumAmount' => trim($r->minimumAmount),
			'perUserLimit' => trim($r->perUserLimit),
			'startDate' => $startDate,
			'endDate' => $endDate,
			'termsAndConditions' => trim($r->termsAndConditions),
            'isActive' => $r->isActive == "" ? '0' : 1,
			'applyOn'=>$r->applyOn,
			'isAdminApproved'=>0,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('restaurant')->user()->code,
        ];
		if ($offerType == 'cap') {
			$data['capLimit'] = trim($r->capLimit);
			$data['discount'] = trim($r->discount);
			$data['flatAmount'] = 0;
		}
		if ($offerType == 'flat') {
			$data['capLimit'] = 0;
			$data['discount'] = 0;
			$data['flatAmount'] = trim($r->flatAmount);
		}
        $result = $this->model->doEdit($data, 'restaurantoffer', $code);
        if ($result) {
			$this->model->deleteForeverFromField('offerCode',$code,'offerincludeditems');
			
			$applyOn = $r->applyOn;
			$offerItems = $r->offerItems;
			if($applyOn=='item'){
				$addressData = array();
				for ($i = 0; $i < sizeof($offerItems); $i++) {
					$offerData = array('offerCode' => $code, 'itemCode' => $offerItems[$i], 'isActive' => 1,'addIP' => $ip,'addDate' => $currentdate->toDateTimeString(),'addID' => Auth::guard('restaurant')->user()->code);
					$offerResult = $this->model->addNew($offerData, 'offerincludeditems', 'CII');
				}
			}
			
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant offer".$code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('restaurantoffer/list')->with('success', 'Restaurant coupon updated successfully');
        }
        return back()->with('error', 'Failed to update restaurant coupon');
    }

    public function view($code)
    {
		$rescode= Auth::guard('restaurant')->user()->code;
        $condition = array('restaurantoffer.code' => array('=', $code));
		$join = array('restaurant' => array('restaurant.code', 'restaurantoffer.restaurantCode'));
        $data['restaurantcoupons'] = $this->model->selectQuery(array('restaurantoffer.*','restaurant.entityName'),'restaurantoffer', $join,$condition);
        $data['restaurants'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['items'] = $this->model->selectQuery(array('restaurantitemmaster.code','restaurantitemmaster.itemName'),'restaurantitemmaster', array(),array('restaurantitemmaster.isActive'=>array("=",1),"restaurantitemmaster.restaurantCode"=>array("=",$rescode)));
		$data['itemEntries'] = DB::table("offerincludeditems")
		                         ->select(DB::raw("(GROUP_CONCAT(offerincludeditems.itemCode SEPARATOR ',')) as `itemsCodes`"))
								 ->where('offerincludeditems.offerCode', $code)
								 ->get();
        return view('restaurant.restaurantcoupon.view', $data);
    }
   
    public function add()
    {
		$rescode= Auth::guard('restaurant')->user()->code;
		$data['items'] = $this->model->selectQuery(array('restaurantitemmaster.code','restaurantitemmaster.itemName'),'restaurantitemmaster', array(),array('restaurantitemmaster.isActive'=>array("=",1),"restaurantitemmaster.restaurantCode"=>array("=",$rescode)));
        return view('restaurant.restaurantcoupon.add',$data);
    }

    public function store(Request $r)
    {
        //$fromDateFormatS = //str_replace('/', '-', $r->startDate);
		$startDate = date('Y-m-d', strtotime($r->startDate));
		//$fromDateFormat = str_replace('/', '-', $r->endDate);
		$endDate = date('Y-m-d', strtotime($r->endDate));

		$code = trim($r->code);
		$couponCode = trim($r->couponCode);
		$offerType = trim($r->offerType);
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $rules = array(
			'couponCode' => 'required',
            'offerType' => 'required',
            'minimumAmount' => 'required',
            'perUserLimit' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        );
		if ($offerType == "cap") {
			$rules['discount'] = 'required';
			$rules['capLimit'] = 'required';
        }
        if ($offerType == "flat") {
            $rules['flatAmount'] = 'required';
        }
        $messages = array(
            'couponCode.required' => 'Coupon Code is required',
            'offerType.required' => 'Offer Type is required',
            'minimumAmount.required' => 'Minimum Amount is required',
            'perUserLimit.required' => 'Per User Limit is required',
            'startDate.required' => 'Start Date is required',
            'endDate.required' => 'End Date is required',
        );
		if ($offerType == "cap") {
			$messages['discount.required'] = 'Discount is required';
			$messages['capLimit.required'] = 'Cap Limit is required';
        }
        if ($offerType == "flat") {
            $messages['flatAmount.required'] = 'Flat Amount is required';
        }
		$this->validate($r, $rules, $messages);
        $data = [
			'couponCode' => $couponCode,
            'restaurantCode'=>Auth::guard('restaurant')->user()->code,
			'offerType' => $offerType,
			'minimumAmount' => trim($r->minimumAmount),
			'perUserLimit' => trim($r->perUserLimit),
			'startDate' => $startDate,
			'endDate' => $endDate,
			'applyOn'=>$r->applyOn,
			'termsAndConditions' => trim($r->termsAndConditions),
            'isActive' => $r->isActive == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('restaurant')->user()->code,
        ];
		if ($offerType == 'cap') {
			$data['capLimit'] = trim($r->capLimit);
			$data['discount'] = trim($r->discount);
			$data['flatAmount'] = 0;
		}
		if ($offerType == 'flat') {
			$data['capLimit'] = 0;
			$data['discount'] = 0;
			$data['flatAmount'] = trim($r->flatAmount);
		}
        $result = $this->model->addNew($data, 'restaurantoffer', 'ROFF');
        if ($result) {
			
			$applyOn = $r->applyOn;
			$offerItems = $r->offerItems;
			if($applyOn=='item'){
				$addressData = array();
				for ($i = 0; $i < sizeof($offerItems); $i++) {
					$offerData = array('offerCode' => $result, 'itemCode' => $offerItems[$i], 'isActive' => 1,'addIP' => $ip,'addDate' => $currentdate->toDateTimeString(),'addID' => Auth::guard('restaurant')->user()->code);
					$offerResult = $this->model->addNew($offerData, 'offerincludeditems', 'CII');
				}
			}
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant offer".$result." is added.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('restaurantoffer/list')->with('success', 'Restaurant coupon added successfully');
        }
        return back()->with('error', 'Failed to update restaurant coupon');
    }
}
