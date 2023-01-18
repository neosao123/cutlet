<?php 
namespace App\Classes;
use Illuminate\Support\Facades\Config;

class Notificationlibv_3
{
	var $CI = null;
	public function __construct()
	{
		//$this->CI = &get_instance();
	}

	public function sendNotification($data, $notification,$ringType="normal")
	{
		$url = 'https://fcm.googleapis.com/fcm/send';
		$type = "";
		if ($data['type'] != "") {
			$type = $data['type'];
		}

        if(isset($data['image'])){
    		if ($data['image'] != "" || $data['image'] != null) $image = $data['image'];
    		else $image = url('notify.png');
        }
        else
        {
            $image =url('notify.png');
        }
		$message = array(
			'title' => $data['title'],
			//'type' =>$type,
			'type' => "",
			'message' => $data['message'],
			"image" => $image,
			//'productCode' => $data['product_id'],
			'productCode' => "",
			'random_id' => $data['random_id'],
			'priority' => 1,
			'vibrate' => 1,
			//'sound' => 1,
			'sound' => 'ringing.mp3' 
		);
		
		$fields = array(
			'registration_ids' => $data['device_id'],
			'data' => $message
		);

	if($ringType!="ringing"){
		$notifyData = array(
			'title' => $notification['title'],
			'body' => $notification['message'],
			//'type' => $type,
			'type' => "",
			"image" => $image,
			//'productCode' => $notification['product_id'],
			'productCode' => "",
			'random_id' => $notification['random_id'],
			'priority' => 1,
			'vibrate' => 1,
			//'sound' => 1,
			'sound' => 'ringing.mp3' 

		);
		$fields['notification'] = $notifyData;
	}
	$firebaseAccesskey = Config::get('constants.FIREBASE_ACCESS_KEY');
		$headers = array(
			'Authorization:key=' . $firebaseAccesskey, 'Content-Type:application/json'
		);

		

		$ch = curl_init();
		if ($url) {
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}

			// Execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}

			// Close connection
			curl_close($ch);

			return $result;
		}
	}
	
	public function sendDeliveryNotification($data, $notification,$ringType="normal")
	{
	    $firebaseAccesskey = Config::get('constants.FIREBASE_ACCESS_KEY');
	    
		$url = 'https://fcm.googleapis.com/fcm/send';
		$type = "";
		if ($data['type'] != "") {
			$type = $data['type'];
		}

        if(isset($data['image'])){
    		if ($data['image'] != "" || $data['image'] != null) $image = $data['image'];
    		else $image = base_url('notify.png');
        }
        else
        {
            $image = base_url('notify.png');
        }
		$message = array(
			'title' => $data['title'],
			'type' => $ringType,
			'message' => $data['message'],
			"image" => $image,
			'status' => "",
			'random_id' => $data['random_id'],
			'priority' => 1,
			'vibrate' => 1,
			'sound' => 'ringing.mp3' 
		);
		
		$fields = array(
			'registration_ids' => $data['device_id'],
			'data' => $message
		);

	if($ringType!="ringing"){
		$notifyData = array(
			'title' => $notification['title'],
			'body' => $notification['message'],
			'type' => $ringType,
			"image" => $image,
			'productCode' => "",
			'random_id' => $notification['random_id'],
			'priority' => 1,
			'vibrate' => 1,
			'sound' => 'ringing.mp3'
		);
		$fields['notification'] = $notifyData;
	}
		$headers = array(
			'Authorization:key=' . $firebaseAccesskey, 'Content-Type:application/json'
		);

		$ch = curl_init();
		if ($url) {
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}

			// Execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}

			// Close connection
			curl_close($ch);

			return $result;
		}
	}
	

	public function pushNotification($data, $notification)
	{
	     $firebaseAccesskey = Config::get('constants.FIREBASE_ACCESS_KEY');
		$url = 'https://fcm.googleapis.com/fcm/send';
		$type = "";
		if ($data['type'] != "") {
			$type = $data['type'];
		}
		$message = array(
			'title' => $data['title'],
			'type' => $type,
			'message' => $data['message'],
			'orderCode' => $data['order_id'],
			'random_id' => $data['random_id']
		);

		$notifyData = array(
			'title' => $notification['title'],
			'type' => $type,
			'body' => $notification['message'],
			'orderCode' => $notification['order_id'],
			'random_id' => $notification['random_id']
		);

		$headers = array(
			'Authorization:key=' . $firebaseAccesskey, 'Content-Type:application/json'
		);

		$fields = array(
			'registration_ids' => $data['device_id'],
			'data' => $message,
			'notification' => $notifyData
		);



		$ch = curl_init();
		if ($url) {
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}

			// Execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				die('Curl failed: ' . curl_error($ch));
			}

			// Close connection
			curl_close($ch);

			return $result;
		}
	}
}