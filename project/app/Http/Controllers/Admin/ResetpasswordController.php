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
use Mail;

class ResetpasswordController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
		$data['city'] = $this->model->selectActiveDataFromTable('citymaster');
        return view('admin.resetpassword.deliveryBoyList',$data);
    }

    public function getDeliveryBoyList(Request $req)
    {
        $search = $req->input('search.value');
        $city = $req->city;
        $tableName = "resetpassword";
        $orderColumns = array("resetpassword.userCode","resetpassword.id","usermaster.*","citymaster.cityName");
        $condition = array('usermaster.cityCode' => array('=', $city),'usermaster.role' => array('=', 'DBOY'),'citymaster.isActive'=>array('=', 1),'usermaster.isDelete' => array('=', 0));
        $orderBy = array('usermaster' . '.id' => 'DESC');
        $join = array('usermaster' => array('usermaster.code','resetpassword.userCode'),'citymaster' => array('citymaster.code', 'usermaster.cityCode'));
        $like = array('usermaster.name' => $search,'usermaster.username' => $search,'usermaster.mobile' => $search, 'usermaster.userEmail' => $search,'citymaster.cityName'=>$search,'designationmaster.designation'=>$search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
				$actions = '<div class="hidden-sm hidden-xs action-buttons">
					<a class="btn btn-info mywarning dfd-top-right cd-popup-trigger resetBtn text-white" id="' . $row->code . '" title="Reset password">
					Reset <i class="ace-icon fa fa-refresh bigger-130 text-white"></i></a></div>';
                $data[] = array(
                   $srno, 
				   $row->code,
				    $row->cityName,
				    $row->name,
                    $row->username,
                    $row->mobile, 
                    $row->userEmail,
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

   public function resetDeliveryPassword(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$code = $r->code;
		$password =  md5('123456'); 
		$data = array('password' => $password);
		$res = $this->model->doEdit($data, 'usermaster', $code);
		//activity log start
		$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."Password Reset ".$code." is updated.";
		$this->model->activity_log($data); 
		//activity log end
		
		$random = rand(0, 999);
		$dataNoti = array("title" => 'Password Reset Successful', "message" => 'Your password has been rest successful.', "order_id" => "", "random_id" => $random, 'type' => '');
		$checkdevices = $this->model->selectQuery(array('usermaster.code','usermaster.firebase_id'),'usermaster',array(),array('usermaster.code'=>array('=',$code)));
		if($checkdevices && count($checkdevices)>0){
			foreach($checkdevices as $chk){
				if($chk->firebase_id!='' && $chk->firebase_id!=null){
					$DeviceIdsArr[] = $chk->firebase_id;
				}
			}
			if(!empty($DeviceIdsArr)){
				$dataArr = array();
				$dataArr['device_id'] = $DeviceIdsArr;
				$dataArr['message'] = $dataNoti['message']; //Message which you want to send
				$dataArr['title'] = $dataNoti['title'];
				$dataArr['order_id'] = $dataNoti['order_id'];
				$dataArr['random_id'] = $dataNoti['random_id'];
				$dataArr['type'] = $dataNoti['type'];
				$notification['device_id'] = $DeviceIdsArr;
				$notification['message'] = $dataNoti['message']; //Message which you want to send
				$notification['title'] = $dataNoti['title'];
				$notification['order_id'] = $dataNoti['order_id'];
				$notification['random_id'] = $dataNoti['random_id'];
				$notification['type'] = $dataNoti['type'];
				//$notify = $this->notificationlibv_3->pushNotification($dataArr, $notification);	
			}
			DB::table('resetpassword')->where('userCode', '=', $code)->delete();
		}
		echo $res;
	}
}
