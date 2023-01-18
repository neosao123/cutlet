<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;

class ApiModel extends Model
{
	public function generateOTPMaster($contactNumber){
		//$otp = $this->randomOTP(6);
		$otp='123456';
		try {
            $result = DB::table('registerOTP')->where('contactNumber','=',$contactNumber)->get();
			if ($result && $result->count() > 0) {
				DB::table('registerOTP')->where('contactNumber', $contactNumber)->update(array('otp' => $otp));
				return $otp;
			}else{
				DB::table('registerOTP')->insertGetId(['contactNumber'=>$contactNumber,"otp"=>$otp]);
				return $otp;
			}
        } catch (Exception $e) {
            return false;
        }
	}  //generateOTPMaster
	
	//generate random otp
	public function randomOTP($n) 
	{ 
		$characters = '0123456789'; 
		$randomString = ''; 
	  
		for ($i = 0; $i < $n; $i++) { 
			$index = rand(0, strlen($characters) - 1); 
			$randomString .= $characters[$index]; 
		} 
	  
		return $randomString; 
	}
    //check otp exists
	public function checkRegisterOTP($otp,$contactNumber)
	{
		$result = $this->db->query("select * from registerOTP where otp='".$otp."' AND contactNumber='".$contactNumber."'")->num_rows();
		if($result>0){			 
			$this->db->query("Delete from registerOTP where contactNumber='".$contactNumber."'");
			return true;
		} else {
			return false;
		}	
	}
	
	public function read_user_information($condition) 
    {  
		try {
            $result = DB::table('registerOTP')->where('contactNumber','=',$contactNumber)->get/all(['code', 'name', 'emailId','cityCode' ,'mobile','comCode', 'status', 'forgot', 'cartCode', 'isActive', 'cityName' , 'isCodEnabled']);
			if ($result && $result->count() > 0) {
				return $otp;
			}else{
				DB::table('registerOTP')->insertGetId(['contactNumber'=>$contactNumber,"otp"=>$otp]);
				return $otp;
			}
        } catch (Exception $e) {
            return false;
        }
        $this->db->select('`clientmaster.code`, `clientmaster.name`, `clientmaster.emailId`,`clientmaster.cityCode` ,`clientmaster.mobile`,`clientmaster.comCode`, `clientmaster.status`, `clientmaster.forgot`, `clientmaster.cartCode`, `clientmaster.isActive`, `citymaster.cityName` , `clientmaster.isCodEnabled`');
        $this->db->from('clientmaster');
		$this->db->join('citymaster','clientmaster.cityCode=citymaster.code');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
		//echo $this->db->last_query();
        if ($query->num_rows()>0) 
        {
            return $query->result();
        } 
        else 
        {
            return false;
        }
    }
}
