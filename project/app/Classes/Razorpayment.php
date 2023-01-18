<?php 
namespace App\Classes;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\Config;
class Razorpayment {
	
	public function createRazorpayOrderid(int $amount,string $name,string $email,string $contact,string $address){
		  $razorKeyId = Config::get('constants.RAZOR_KEY_ID');
		  $razorKeySecret = Config::get('constants.RAZOR_KEY_SECRET');
		  $receiptId = rand(111, 9999);
		  $api = new Api($razorKeyId, $razorKeySecret);
		  $order=$api->order->create(array('receipt' =>$receiptId, 'amount' =>$amount, 'currency' => "INR"));
		  $response = [
			'orderId' => $order['id'],
			'amount' =>$amount,
			'name' => $name,
			'currency' =>'INR',
			'email' => $email,
			'contactNumber' =>$contact,
			'address' =>$address,
			'description' => 'Cutlet Order Payment',
		  ];
		  if($response){
		     return $response; 
		  }else{
			  return 1;
		  }
	 }
	
	  public function razorPayVerifySignature(string $orderid, string $razorpay_signature,string $razorpay_order_id,string $razorpay_payment_id){
		  $razorKeySecret = Config::get('constants.RAZOR_KEY_SECRET');
		  $generated_signature = hash_hmac("sha256",$orderid + "|" +$razorpay_payment_id, $razorKeySecret);
		  if ($generated_signature == $razorpay_signature) {
			  return true;
		  }else{
			  return false;
		  }
	  }
}