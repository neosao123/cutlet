<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\GlobalModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	/*public function index(){
		return view('admin.chat.index');
	}
	public function sendMessage(){
		$redis = LRedis::connection();
		$data = ['message' => Request::input('message'), 'user' => Auth::guard('admin')->user()->code];
		$redis->publish('message', json_encode($data));
		return response()->json([]);
	}*/
	public function sendMessage(Request $r){
        $dbCode = $r->deliveryBoyCode;
        $orderCode = $r->orderCode;
        $arr['latitude'] = $r->latitude;
        $arr['longitude'] = $r->longitude;
        $arr['addDate'] = date('Y-m-d H:i:s');
		$filename = 'assets/order_tracking/'. $orderCode.'.json';
		if (file_exists($filename)){
			$jsonString = file_get_contents($filename);
			$data = json_decode($jsonString, true);
			array_unshift($data,$arr);
			$newJsonString = json_encode($data);
			file_put_contents($filename, $newJsonString);
		}else{
			$content = json_encode(array($arr));
			file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
		}
		$response['latitude'] = $r->latitude;
		$response['longitude'] = $r->longitude;
		$response['addDate'] = date('d/m/Y h:i A');
		$response['success'] = true;
        echo json_encode($response);
    }
	public function trackOrder($type,$orderCode){
		$orderDetails = $this->model->selectQuery(array('restaurantordermaster.trackingPort','restaurantordermaster.deliveryBoyCode'),'restaurantordermaster',array(),array('restaurantordermaster.code'=>array('=',$orderCode),"restaurantordermaster.isActive"=>array('=',0)));
		if($orderDetails){
			if(count($orderDetails)>0){
				$result= $orderDetails[0];
				$data['port'] = $result->trackingPort;
				$data['deliveryBoyCode'] = $result->deliveryBoyCode;
				$data['orderCode'] = $orderCode;
				$data['type']=$type;
				$data['trackingDetails']=false;
				if (file_exists('assets/order_tracking/'. $orderCode.'.json')){
					$jsonString = file_get_contents('assets/order_tracking/'. $orderCode.'.json');
					$fileData = json_decode($jsonString, true);
					if(!empty($fileData[0])){
						$data['trackingDetails']=$fileData;
					}
				}
				return view('admin.chat.index',$data);
			}
		}
	}
}
