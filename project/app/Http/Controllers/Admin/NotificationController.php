<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Classes\Notificationlibv_3;
use DB;

class NotificationController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	
	public function createNotification()
	{
		$data['city'] = $this->model->selectActiveDataFromTable('citymaster');
		return view('admin.pushNotification.notificationCreate',$data);
	}
	
	public function processNotification(Request $r){
		$data['listLength'] = 0;
		$rules = array(
		    'title'=>'required',
		    'msg'=>'required',
		    'cityCode2'=>'required',
        );
        $messages = array(
		    'title.required' => 'Title is required',
		    'msg.required' => 'Message is required',
		    'cityCode2.required' => 'City is required',
        );
		$this->validate($r, $rules, $messages); 
		$data['listLength'] = 0;
		$title = $r->title;
		$msg = $r->msg;
		$imgName = $r->img;
		$cityCode = $r->cityCode2;
		$clientcode = $r->client;
		$place_id = $r->place;
		$city = "";
		if (isset($cityCode) && is_array($cityCode)){
			foreach ($cityCode as $ad) {
				$city != "" && $city .= ",";
				$city .= "'" . $ad . "'";
			}
		}else{
			$city = "'" . $cityCode . "'";
		}
		$uploadRootDir = 'uploads';
		$uploadDir = 'uploads/notificationimg/';
		if ($r->hasFile('catimg')) { 
            $filecatImage =  $r->file('catimg');       
            $filenamecatImage = $place_id. '.'.$filecatImage->getClientOriginalExtension();          
            $filecatImage->move("uploads/notificationimg", $filenamecatImage);
			if (file_exists($filenamecatImage)) {
				$imgName = url('uploads/notificationimg/' .$filenamecatImage);
			}
        }
		$data['cityCodes'] = $city;
		if ($clientcode != "") {
			$result = DB::table('clientdevicedetails')
					->select('firebaseId')
					->where('clientCode',$clientcode)
					->where('firebaseId','!=','')
					->whereNotNull('firebaseId')
					->get();
		} else {
			$join = array('clientmaster'=>array('clientmaster.code','clientdevicedetails.clientCode'));
			$condition = array('clientmaster.isActive'=>array("=",1));
			$extraCondition = " clientmaster.cityCode in (" . $city . ")  and  clientdevicedetails.firebaseId IS NOT NULL and clientdevicedetails.firebaseId <> ''";
			$result = $this->model->selectQuery(array("clientdevicedetails.firebaseId"),"clientdevicedetails",$join,$condition,array(),array(),"","",$extraCondition);
		}
		if ($result && count($result)>0) {
			$data['firebaseIds'] = $result;
			$data['listLength'] = sizeof($result);
		}	
		$data['notificationData'] = array("title" => $title, "message" => $msg, "image" => $imgName, "product_id" => $place_id, 'type' => 'productOffer', 'clientCode' => $clientcode);
		return view('admin.pushNotification.notification',$data);
	}
	
	public function sendCommonNotification(Request $r)
	{
		$cityCodes = $r->cityCodes;
		$title = $r->title;
		$message = $r->message;
		$image = $r->image;
		$product_id = $r->product_id;
		$clientCode = $r->clientCode;
		$type = $r->type;
		$firebaseIds = $r->firebaseIdsArray;
		//echo $image;
		$random = rand(1, 999);
		if ($firebaseIds != "") {
			$DeviceIdsArr = array();
			foreach ($firebaseIds as $rowData) {
				$DeviceIdsArr[] = $rowData;
			}
			$dataArr = array();
			$dataArr['device_id'] = $DeviceIdsArr;
			$dataArr['message'] = $message; //Message which you want to send
			$dataArr['title'] = $title;
			$dataArr['image'] = $image;
			$dataArr['product_id'] = $product_id;
			$dataArr['random_id'] = $random;
			$dataArr['type'] = $type;

			$notification['device_id'] = $DeviceIdsArr;
			$notification['message'] = $message; //Message which you want to send
			$notification['title'] = $title;
			$notification['image'] = $image;
			$notification['product_id'] = $product_id;
			$notification['random_id'] = $random;
			$notification['type'] = $type;
			$noti = new Notificationlibv_3;
			$notify = $noti->sendNotification($dataArr, $notification);
			print_r($notify);
		}
	}
	
	public function getCustomersListByCity(Request $r)
	{
		$cityCode = $r->cityCode;
		$city = "";
		if (isset($cityCode)) {
			foreach ($cityCode as $ad) {
				$city != "" && $city .= ",";
				$city .= "'" . $ad . "'";
			}
		}
		$html = "<option value=''>Select Customer</option>";
		if ($cityCode != "") {
			$orderBy = array("clientmaster.id" => 'desc');
			$extraCondition = " clientmaster.cityCode in (" . $city . ")";
			$Result = $this->model->selectQuery(array("clientmaster.code","clientmaster.name"), "clientmaster",array(),array(), $orderBy,array(),'','', $extraCondition);
			if ($Result) {
				foreach ($Result as $r) {
					$html .= '<option value="' . $r->code . '" id="' . $r->code . '">' . $r->name . '</option>';
				}
			}
		}
		echo $html;
	}
}
