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

class ActivityController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        return view('admin.activity.list');
    }
	
	public function getActivityList(Request $req)
	{  
		$srno=1;
		$date = $req->date;
		$filename=public_path("logfile/log-".date('d-m-Y',strtotime($date)).".txt");
		if(file_exists($filename)){
		$lines = file($filename); // gets file in array using new lines character
		if(!empty($lines)){
			krsort($lines);
			foreach($lines as $line){
				$temparr = explode('	',$line);
				$data[] = array(
						$srno,
						date('d/m/Y h:i A',strtotime($temparr[0])),
						$temparr[1],
						$temparr[2],
						$temparr[3]
					);
					$srno++;
			}
			$dataCount = sizeof($lines);
			$output = array("draw"=>0 ,"recordsTotal" =>$dataCount,"recordsFiltered" => $dataCount,"data" => $data);
			echo json_encode($output);
		} else {
			$dataCount = 0;
			$data = array();
			$output = array("draw"=> 0,"recordsTotal" =>$dataCount,"recordsFiltered" => $dataCount,"data" => $data);
			echo json_encode($output);
		}
		}else {
			$dataCount = 0;
			$data = array();
			$output = array("draw"=>0,"recordsTotal" =>$dataCount,"recordsFiltered" => $dataCount,"data" => $data);
			echo json_encode($output);
		}
		
	}
}