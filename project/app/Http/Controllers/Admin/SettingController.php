<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use DB;

class SettingController extends Controller
{
    public function __construct(GlobalModel $model)
	{
		$this->model = $model;
	}
	
	public function index()
	{
		return view('admin.setting.list');
	}
    
	public function getSettingList(Request $req)
	{
		$search = $req->input('search.value');
		$tableName = "settings";
		$orderColumns = array("settings.*");
		$condition = array('settings.isDelete' => array('=', 0));
		$orderBy = array('settings.id' => 'ASC');
		$join = array();
		$like = array('settings.settingName' => $search);
		$limit = $req->length;
		$offset = $req->start;
		$extraCondition = "";
		$result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
		$srno = $_GET['start'] + 1;
		$dataCount = 0;
		$data = array();
		if ($result && $result->count() > 0) {
			foreach ($result as $row) {
				
				$actions = '<div class="text-center"> 
						<a class="btn btn-outline-success btn-sm icons_padding" href="' . url("setting/view/" . $row->code) . '" title="View"><i class="fa fa-eye" style="font-size:18px;"></i></a>
					    <a class="btn btn-outline-primary btn-sm icons_padding" style="" href="' . url("setting/edit/" . $row->code) . '" title="Edit"><i class="fa fa-edit" style="font-size:18px;"></i></a>
					</div>';
				$data[] = array(
				    $srno,
					$row->code,
                    $row->settingName,
                    $row->settingValue,
					$row->messageTitle,
					$row->messageDescription,
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
		$data['setting'] = $this->model->selectDataByCode('settings', $code);
        return view('admin.setting.edit', $data);
	}
	public function view($code)
    {
        $data['setting'] = $this->model->selectDataByCode('settings', $code);
        return view('admin.setting.view', $data);
    }


    public function update(Request $r)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate=Carbon::now();
		$settingName =  ucwords($r->settingName);
		if ($r->has('code') && trim($r->code) != "") {
			$code = $r->code;
			$data = [
				'settingValue' => trim($r->settingValue), 
				'messageTitle'=> $r->messageTitle,
				'messageDescription' => $r->messageDescription,
				'editIP'=>$ip,
				'editDate'=>$currentdate->toDateTimeString(),
				'editID' => Auth::guard('admin')->user()->code
			];
			$result = $this->model->doEdit($data, 'settings', $r->code); 
			if ($result != false) {
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('admin')->user()->code.	"	"."setting ".$code." is updated.";
				$this->model->activity_log($data); 
				//activity log end
				return redirect('setting/list')->with('success', 'Setting updated successfully');
        }
			return back()->with('error', 'Failed to update setting');
		}
	}
	
	 public function updateMaintenanceOn(Request $r)
    {
        $data['settingValue'] = $r->settingValue;
        $data['messageTitle'] = $r->messageTitle;
        $data['messageDescription'] = $r->messageDescription;
        $code = 'SET_1';
        $update = $this->model->doEdit($data, "settings", $code);
        if ($update) {
            echo true;
        } else {
            echo false;
        }
    }
    public function updateMaintenanceOff(Request $r)
    {
        $data['settingValue'] =  $r->settingValue;
        $code = 'SET_1';
        $update = $this->model->doEdit($data, "settings", $code);
        if ($update) {
            echo true;
        } else {
            echo false;
        }
    }
    public function getMaintenanceMode(Request $r)
    {
        $resultData = $this->model->selectQuery(array('settings.*'), 'settings',array(), array('settings.code' => array('=','SET_1')));
        $maintenance_mode['settingValue'] = $resultData[0]->settingValue;
        $maintenance_mode['messageTitle'] = $resultData[0]->messageTitle;
        $maintenance_mode['messageDescription'] = $resultData[0]->messageDescription;
        echo json_encode($maintenance_mode);
    }

		
}
