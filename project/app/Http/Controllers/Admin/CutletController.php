<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use Carbon\Carbon;
use DB;


class CutletController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function index(){
		$data['clientmaster'] = $this->model->selectActiveDataFromTable('clientmaster');
        return view('admin.cutlet.list',$data);
    }

    public function getCutletList(Request $req)
    {
        $search = $req->input('search.value');
		$clientCode= $req->clientCode;
		$status= $req->status;
		$tableName = "wallettransactions";
        $orderColumns = array("restaurant.entityName","wallettransactions.clientCode","clientmaster.name","wallettransactions.orderCode","wallettransactions.point","wallettransactions.message","wallettransactions.status");
        $condition = array('clientmaster.code'=>array("=",$clientCode),'wallettransactions.status'=>array('=',$status),'clientmaster.isActive'=>array("=",1),"restaurantordermaster.isACtive"=>array('=',1));
        $orderBy = array('wallettransactions.id' => 'DESC');
        $joinType = array('restaurantordermaster'=>'inner','restaurant'=>'inner','wallettransactions'=>'inner');
        $join = array('restaurantordermaster'=>array('restaurantordermaster.code','wallettransactions.orderCode'),'restaurant'=>array('restaurant.code','restaurantordermaster.restaurantCode'),'clientmaster'=> array('clientmaster.code','wallettransactions.clientCode'));
        $groupByColumn = array();
        $like = array("restaurant.entityName","clientmaster.name" => $search,"clientmaster.mobile" => $search ,"clientmaster.code" => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
		if($status==''){
			$extraCondition .= " (wallettransactions.status IS NULL or wallettransactions.status='')";
		}
		
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
			
            foreach ($result as $row) {
				$status='';
                if ($row->status == 'pending') {
                    $status = '<span class="badge badge-danger">Pending</span>';
                }elseif ($row->status == 'success') {
					$status = '<span class="badge badge-success">Success</span>';
				}else{
					$status = '<span class="badge badge-warning">Used</span>';
				}
                $data[] = array(
                   $srno, 
				   $row->clientCode, 
				   $row->name,
				   $row->entityName, 
				   $row->orderCode, 
				   $row->point, 
				   $row->message, 
				   $status
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
}
