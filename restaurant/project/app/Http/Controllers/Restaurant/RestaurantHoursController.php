<?php

namespace App\Http\Controllers\Restaurant;

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

class RestaurantHoursController extends Controller
{
    public function __construct(GlobalModel $model) 
    {
        $this->model = $model;
    }
	
	public function getRestaurantHours(Request $r)
	{
		$code=$r->code;
		$data['restaurant'] = Restaurants::where('code', $code)->first();
		$data['restauranthours'] = RestaurantHours::where('restaurantCode', $code)->get();
		return view('restaurant.restaurant.restauranthours',$data); 
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
				  'editID' => Auth::guard('restaurant')->user()->code,  
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
		$currentdate = Carbon::now();
		if ($updateTo=="to"){
			$totime=$time;
			$toTime = date('H:i:s',strtotime($totime));
			/*$checkTime=RestaurantHours::where('fromTime','=',$toTime)->orWhere('totime','=', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();
			if($checkTime){
				$invalid=1;
			}else{*/
				$checkTime=RestaurantHours::where('fromTime','<',$toTime)->where('totime','>', $toTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();
				if($checkTime){
					$invalid=1;
				}
			//}
				$data = array(   
					'toTime' => $toTime,
					'editID' => Auth::guard('restaurant')->user()->code,
					'editIP' => $ip,
					'isActive' => 1,
				);
					
		}else{
			$fromtime=$time;
            $fromTime = date('H:i:s',strtotime($fromtime));	
			$checkTime=RestaurantHours::where('fromTime','=',$fromTime)->orWhere('totime','=', $fromTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();
			/*if($checkTime){
				$invalid=1;
			}else{*/
				$checkTime=RestaurantHours::where('fromTime','<',$fromTime)->where('totime','>', $fromTime)->where('weekday',$weekDay)->where('restaurantCode',$restCode)->where('code','!=',$code)->first();	
				if($checkTime){
					$invalid=1;
				}
			//}
				$data = array(  
					'fromTime' => $fromTime, 
					'editID' => Auth::guard('restaurant')->user()->code,
					'editIP' => $ip,
					'isActive' => 1,
				);
					
		}
		
		if($invalid==1){
			$response['status']  = false;
			$response['message'] = 'From time and to time should not be overlapped';	
		}else{
			$code = $this->model->doEdit($data, 'restauranthours', $code);
			//echo $code;
			if ($code != false) { 

              //activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Hours ".$code." is updated";
				$this->model->activity_log($data); 
				//activity log end
									
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
				  'addID' => Auth::guard('restaurant')->user()->code,
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
		$currentdate = Carbon::now();
		$query=$this->model->deletePermanent($code,'restauranthours');	
		if($query){
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restauranthours".$code." is deleted";
			$this->model->activity_log($data); 
			//activity log end
			$res['status'] = true;
		} else {
			$res['status'] = false;
		}
	    echo json_encode($res);
		
	}
}
