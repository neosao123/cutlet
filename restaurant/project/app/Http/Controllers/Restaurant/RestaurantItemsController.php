<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\RestaurantItemMaster;
use App\Models\MenuCategory;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class RestaurantItemsController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	
	public function index()
    {
        $code=Auth::guard('restaurant')->user()->code;
        $data['restaurantitemmaster']=RestaurantItemMaster::distinct()->where('restaurantCode', $code)->where("isDelete","!=",1)->get(['itemName']);
		$data['menuCategory'] = MenuCategory::where("isDelete","!=",1)->get();
        return view('restaurant.restaurantitem.list',$data);
    }
	
	public function getRestaurantItemList(Request $req)
    {
		$code=Auth::guard('restaurant')->user()->code;
        $restitem = $req->restitem;
		$menucategory = $req->menucategory;
        $approvedstatus = $req->approvedstatus;
        $search = $req->input('search.value');
        $tableName = "restaurantitemmaster";
        $orderColumns = array("restaurantitemmaster.*","restaurant.entityName","menucategory.menuCategoryName");
        $condition = array('restaurantitemmaster.isDelete' => array('!=', 1),"restaurantitemmaster.restaurantCode" =>array('=',$code),'restaurantitemmaster.itemName' => array('=', $restitem),'restaurantitemmaster.isAdminApproved' => array('=', $approvedstatus),'restaurantitemmaster.menuCategoryCode'=>array('=',$menucategory));
        $orderBy = array('restaurantitemmaster.id' => 'DESC');
         $join = array('restaurant' => array('restaurant.code', 'restaurantitemmaster.restaurantCode'),'menucategory' => array('menucategory.code', 'restaurantitemmaster.menuCategoryCode'));
        $like = array('restaurantitemmaster.itemName' => $search);
        $limit = $req->length;
        $offset = $req->start;
        $extraCondition = "";
        $result = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, $like, $limit, $offset,$extraCondition);
        $srno = $_GET['start'] + 1;
        $dataCount = 0;
        $data = array();
        if ($result && $result->count() > 0) {
            foreach ($result as $row) {
				if ($row->isActive == 1) {
					$status = "<span class='badge badge-success'>Active</span>";
				} else {
					$status = "<span class='badge badge-warning'>Inactive</span>";
				}
				if ($row->itemActiveStatus == 1) {
					$itemActiveStatus = "<span class='badge badge-success'>Active</span>";
				} else {
					$itemActiveStatus = "<span class='badge badge-danger'>Inactive</span>";
				}
				if ($row->isAdminApproved == 1) {
					$isAdminApproved = "<span class='badge badge-success'>Yes</span>";
				} else {
					$isAdminApproved = "<span class='badge badge-danger'>No</span>";
				}
                $actions = '<div class="btn-group">
					<button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="ti-settings"></i>
					</button>
					<div class="dropdown-menu animated slideInUp" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
						<a class="dropdown-item" href="' . url("restaurantItems/view/" . $row->code) . '"><i class="ti-eye"></i> Open</a>
						<a class="dropdown-item" href="' . url("restaurantItems/edit/" . $row->code) . '"><i class="ti-pencil-alt"></i> Edit</a>
						<a class="dropdown-item" href="' . url("restaurantItems/customizeaddon/" . $row->code) . '"><i class="ti-pencil"></i> Add Ons</a>
						<a class="dropdown-item  delbtn" data-id="' . $row->code . '" id="' . $row->code . '"><i class="ti-trash" href></i> Delete</a>
					</div>
				</div>';
                $data[] = array(
                   $srno, 
				    $row->code,
					$row->itemName,
					$row->menuCategoryName,
					$status,
					$itemActiveStatus,
					$isAdminApproved,
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
	
	public function add()
    {
		$data['menuCategory'] = $this->model->selectActiveDataFromTable('menucategory');
        return view('restaurant.restaurantitem.add',$data);
    }
	
	public function store(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$code=Auth::guard('restaurant')->user()->code;
		$table = "restaurantitemmaster";
        $rules = array(
			'itemName' => 'required|regex:/^[A-Za-z0-9 ]+$/|min:1',
           // 'restaurantCode' => 'required',
			'itemPackagingPrice' => 'required',
			'maxOrderQty' => 'required',
            'cuisineType' => 'required',
            'menuCategoryCode' => 'required',
            'salePrice' => 'required',
        );
        $messages = array(
            'itemName.required' => 'Item Name is required',
            'itemName.regex' => 'Special characters are not allowed',
            'itemName.min' => 'Minimum of 1 characters are required.',
			'restaurantCode.required' => 'Restaurant is required',
			'itemPackagingPrice.required' => 'Item Packaging Price is required',
			'maxOrderQty.required' => 'Max Order Quantity is required',
			'cuisineType.required' => 'Cuisine Type is required',
			'menuCategoryCode.required' => 'Menu category is required',
			'salePrice.required' => 'Sale Price are  required'
        );
		$this->validate($r, $rules, $messages);
        $data = [
			'itemName' => trim($r->itemName),
			'itemDescription' => trim($r->itemDescription),
			'restaurantCode' => Auth::guard('restaurant')->user()->code,
			'menuCategoryCode' => trim($r->menuCategoryCode),
			'menuSubCategoryCode' => trim($r->menuSubCategoryCode),
			'salePrice' => trim($r->salePrice),
			'cuisineType' => trim($r->cuisineType),
			'itemActiveStatus' => trim($r->itemActiveStatus) == "" ? '0' : 1 ,
			'itemPackagingPrice' => trim($r->itemPackagingPrice),
			'maxOrderQty' => $r->maxOrderQty == "" || $r->maxOrderQty == 0 ? 1 : $r->maxOrderQty,
            'isActive' => trim($r->isActive) == "" ? '0' : 1,
            'isDelete' => 0,
            'addIP' => $ip,
            'addDate' => $currentdate->toDateTimeString(),
            'addID' => Auth::guard('restaurant')->user()->code,
        ];
        $currentId = $this->model->addNewWithYear($data, 'restaurantitemmaster', 'RITM');
        if ($currentId) {
			if ($r->hasFile('itemImage')) {
				$path = '../uploads/restaurant/restaurantitemimage/'.$code;
				if (! File::exists($path)) {
					File::makeDirectory($path,0777,true);
				}
				$fileItemImage =  $r->file('itemImage');       
				$filenameItemImage = $currentId. '-' . time() . '.' . $fileItemImage->getClientOriginalExtension();          
				$fileItemImage->move($path,  $filenameItemImage);
				$itemImage = ['itemPhoto' =>$filenameItemImage]; 
				$image_update = $this->model->doEdit($itemImage, $table, $currentId);
			}
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Item ".$currentId." is added";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('restaurantItems/list')->with('success', 'Restaurant item added successfully');
        }
        return back()->with('error', 'Failed to add the restaurant item');
    }

    public function edit($code)
    {
		$query=$this->model->selectDataByCode('restaurantitemmaster', $code);
		$menuCategoryCode='';
		if($query){
			$menuCategoryCode = $query->menuCategoryCode;
		}
        $data['itemDetails'] = $query;
        $data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['menuCategory'] = $this->model->selectActiveDataFromTable('menucategory');
		$data['menuSubcategory'] = [];
		if($menuCategoryCode!='' && $menuCategoryCode!=null){
			$data['menuSubcategory'] = $this->model->selectQuery(array('menusubcategory.code','menusubcategory.menuSubCategoryName'),'menusubcategory',array(),array('menusubcategory.menuCategoryCode' => array('=', $menuCategoryCode),'menusubcategory.isDelete' => array('!=', 1)));
		}
        return view('restaurant.restaurantitem.edit', $data);
    }
	
	 public function update(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$code=$r->code;
        $rules = array(
			'itemName' => 'required|regex:/^[A-Za-z0-9 ]+$/|min:1',
            'cuisineType' => 'required',
            'menuCategoryCode' => 'required',
			'itemPackagingPrice' => 'required',
			'maxOrderQty' => 'required',
            'salePrice' => 'required',
        );
        $messages = array(
            'itemName.required' => 'Item Name is required',
            'itemName.regex' => 'Special characters are not allowed',
            'itemName.min' => 'Minimum of 1 characters are required.',
			'cuisineType.required' => 'Cuisine Type is required',
			'menuCategoryCode.required' => 'Menu category is required',
			'salePrice.required' => 'Sale Price are  required',
			'itemPackagingPrice.required' => 'Item Packaging Price is required',
			'maxOrderQty.required' => 'Max Order Quantity is required',
        );
		$this->validate($r, $rules, $messages);
        $data = [
			'itemName' => trim($r->itemName),
			'itemDescription' => trim($r->itemDescription),
			'restaurantCode' => trim(Auth::guard('restaurant')->user()->code),
			'menuCategoryCode' => trim($r->menuCategoryCode),
			'menuSubCategoryCode' => trim($r->menuSubCategoryCode),
			'salePrice' => trim($r->salePrice),
			'cuisineType' => trim($r->cuisineType),
			'itemActiveStatus' => trim($r->itemActiveStatus) == "" ? '0' : 1 ,
			'isAdminApproved' => trim($r->isAdminApproved) == "" ? '0' : 1,
			'itemPackagingPrice' => trim($r->itemPackagingPrice),
			'maxOrderQty' => $r->maxOrderQty == "" || $r->maxOrderQty == 0 ? 1 : $r->maxOrderQty,
            'isActive' => trim($r->isActive) == "" ? '0' : 1,
            'isDelete' => 0,
            'editIP' => $ip,
            'editDate' => $currentdate->toDateTimeString(),
            'editID' => Auth::guard('restaurant')->user()->code,
        ];
        $result = $this->model->doEdit($data, 'restaurantitemmaster', $code);
        if ($result) {
			if ($r->hasFile('itemImage')) { 
				$path = '../uploads/restaurant/restaurantitemimage/'.$code;
				if (! File::exists($path)) {
					File::makeDirectory($path,0777,true);
				}
				$fileItemImage =  $r->file('itemImage');       
				$filenameItemImage = $r->code. '-' . time() . '.' . $fileItemImage->getClientOriginalExtension();          
				$fileItemImage->move($path,  $filenameItemImage);
				$itemImage = ['itemPhoto' =>$filenameItemImage];
				$image_update = $this->model->doEdit($itemImage, 'restaurantitemmaster',$r->code);
			}
			//activity log start
			$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Item ".$r->code." is updated.";
			$this->model->activity_log($data); 
			//activity log end
            return redirect('restaurantItems/list')->with('success', 'Restaurant item updated successfully');
        }
        return back()->with('error', 'Failed to update the restaurant item');
    }

    public function view($code)
    {
        $data['itemDetails'] = $this->model->selectDataByCode('restaurantitemmaster', $code);
        $data['restaurant'] = $this->model->selectActiveDataFromTable('restaurant');
		$data['menuCategory'] = $this->model->selectActiveDataFromTable('menucategory');
		$data['menuSubcategory'] = $this->model->selectActiveDataFromTable('menusubcategory');
        return view('restaurant.restaurantitem.view', $data);
    }
	
	public function customizeaddon($code)
    {
		$data['itemDetails'] = $this->model->selectDataByCode('restaurantitemmaster', $code);
		//DB::enableQueryLog();
		$condition = array("customizedcategory.restaurantItemCode" => array('=', $code));
		$orderBy = array("customizedcategory.id" => "ASC");
        $data['categories'] = $this->model->selectQuery('customizedcategory.*', 'customizedcategory',array(), $condition, $orderBy);
		///$query_1 = DB::getQueryLog();
        //print_r($query_1);
		$orderColumns2 = "customizedcategorylineentries.*";
		$condition2 = array("customizedcategory.restaurantItemCode" => array('=', $code));
		$orderBy2 = array("customizedcategorylineentries.id" => "ASC");
		$join2 = array('customizedcategory' => array('customizedcategory.code', 'customizedcategorylineentries.customizedCategoryCode'));
		$data['categoriesline'] = $this->model->selectQuery('customizedcategorylineentries.*', 'customizedcategorylineentries',$join2, $condition2, $orderBy2);
        return view('restaurant.restaurantitem.addon', $data);
    }
	
	public function deleteAddonCategory(Request $r)
	{
		$code = $r->code;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate = Carbon::now();
		//activity log start
		$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Add on category".$code." is deleted";
		$this->model->activity_log($data); 
		//activity log end
		echo $resultData = $this->model->deletePermanent($code, 'customizedcategory');
	}
	public function deleteAddonLine(Request $r)
	{
		$code = $r->code;
		$ip = $_SERVER['REMOTE_ADDR'];
		$currentdate = Carbon::now();
		//activity log start
		$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Add on category line".$code." is deleted";
		$this->model->activity_log($data); 
		//activity log end
		echo $resultData = $this->model->deletePermanent($code, 'customizedcategorylineentries');
	}
	
	public function addAddonLine(Request $r){
		 $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$cateCode = trim($r->cateCode);
		$subTitle = trim($r->subTitle);
		$price = trim($r->price);
		$price = $price!="" ? $price : 0;
		
		$where = array(
		    "customizedCategoryCode"=>$cateCode,
			"subCategoryTitle"=>$subTitle
		);
		$where[] = ["customizedcategorylineentries.customizedCategoryCode", "=", $cateCode];
		$where[] = ["customizedcategorylineentries.subCategoryTitle", "=", $subTitle];
		$duplicate = $this->model->checkForDuplicate("customizedcategorylineentries", "customizedcategorylineentries.id", $where);
		if ($duplicate) {
			$response['status']  = 'exists';
			$response['message'] = 'Duplicate Sub Category Exists';
		} else {
			$data = array(
				'customizedCategoryCode' => $cateCode,
				'subCategoryTitle' => $subTitle,
				'price' => $price,
				'isEnabled' => $r->isCateEnabled,
				'isActive' => trim($r->isActive) == "" ? '0' : 1,
				'isDelete' => 0,
				'editIP' => $ip,
				'addDate' => $currentdate->toDateTimeString(),
				'editID' => Auth::guard('restaurant')->user()->code,
			);
			$code = $this->model->addNewWithYear($data, 'customizedcategorylineentries', 'CCLN');
			if ($code != 'false') {
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Customizedcategory line entry".$code." is added";
				$this->model->activity_log($data); 
				//activity log end
				$response['status']  = 'true';
				$response['code']  = $code;
				$response['message'] = 'Sub Category Added Successfully';
			} else {
				$response['status']  = 'failed';
				$response['message'] = 'Failed to add Sub Category';
			}
		}
		echo json_encode($response);
	}
	
	public function getAddonCategoryData(Request $r)
	{
		$code = $r->code;
		$data = $this->model->selectQuery('customizedcategory.*', 'customizedcategory',array(), array("customizedcategory.code" => array('=', $code)));
		if ($data) {
			foreach($data as $d){
				$dataRes = json_encode($d);
			}
		} else {
			$dataRes = "";
		}
		echo $dataRes;
	}
	
	public function addAddonCategory(Request $r)
	{
		 $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$vendorItemCode = trim($r->vendorItemCode);
		$categoryTitle = trim($r->categoryTitle);
		$categoryType = trim($r->categoryType);
		$isCateEnabled = trim($r->isCateEnabled);
		$where[] = ["customizedcategory.restaurantItemCode", "=", $vendorItemCode];
		$where[] = ["customizedcategory.categoryTitle", "=", $categoryTitle];
		//DB::enableQueryLog();
		
		$duplicate = $this->model->checkForDuplicate("customizedcategory", "customizedcategory", $where);
		//$query_1 = DB::getQueryLog();
        //print_r($query_1);
		if ($duplicate) {
			$response['status']  = 'exists';
			$response['message'] = 'Duplicate Category Exists';
		} else {
			$data = array(
				'restaurantItemCode' => $vendorItemCode,
				'categoryTitle' => $categoryTitle,
				'categoryType' => $categoryType,
				'isEnabled' => $isCateEnabled,
				'isActive' => trim($r->isActive) == "" ? '0' : 1,
				'isDelete' => 0,
				'addIP' => $ip,
				'addDate' => $currentdate->toDateTimeString(),
				'addID' => Auth::guard('restaurant')->user()->code,
			);
			$code = $this->model->addNewWithYear($data, 'customizedcategory', 'ITCC');
			if ($code != 'false') {
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Customizedcategory".$code." is added";
				$this->model->activity_log($data); 
				//activity log end
				$response['status']  = 'true';
				$response['code']  = $code;
				$response['message'] = 'Category Added Successfully';
			} else {
				$response['status']  = 'failed';
				$response['message'] = 'Failed to add category';
			}
		}
		echo json_encode($response);
	}
	
	public function updateAddonCategory(Request $r)
	{
		 $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
		$vendorItemCode = trim($r->vendorItemCode);
		$categoryTitle = trim($r->categoryTitle);
		$categoryType = trim($r->categoryType);
		$isCateEnabled = trim($r->isCateEnabled);
		$customizedCategoryCode = trim($r->customizedCategoryCode);
		$ip = $_SERVER['REMOTE_ADDR'];
		$where[] = ["customizedcategory.restaurantItemCode", "=", $vendorItemCode];
		$where[] = ["customizedcategory.categoryTitle", "=", $categoryTitle];
		$where[] = ["customizedcategory.code", "!=", $customizedCategoryCode];
		
		$duplicate = $this->model->checkForDuplicate("customizedcategory", "customizedcategory", $where);
		if ($duplicate) {
			$response['status']  = 'exists';
			$response['message'] = 'Duplicate Category Exists';
		} else {
			$data = array(
				'restaurantItemCode' => $vendorItemCode,
				'categoryTitle' => $categoryTitle,
				'categoryType' => $categoryType,
				'isEnabled' => $isCateEnabled,
				'isActive' => trim($r->isActive) == "" ? '0' : 1,
				'isDelete' => 0,
				'editIP' => $ip,
				'editDate' => $currentdate->toDateTimeString(),
				'editID' => Auth::guard('restaurant')->user()->code,
			);
			$resultUpdate = $this->model->doEdit($data, 'customizedcategory', $customizedCategoryCode);
			if ($resultUpdate) {
				//activity log start
				$data=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."CustomizedCategory ".$customizedCategoryCode." is updated.";
				$this->model->activity_log($data); 
				//activity log end
				$response['status']  = 'true';
				$response['code']  = $customizedCategoryCode;
				$response['updatedtitle']  = $categoryTitle;
				$response['message'] = 'Category updated successfully';
			} else {
				$response['status']  = 'failed';
				$response['code']  = $customizedCategoryCode;
				$response['message'] = 'Failed to update category';
			}
		}
		echo json_encode($response);
	}
	
	public function delete(Request $r)
    {
        $code = $r->code;
        $ip = $_SERVER['REMOTE_ADDR'];
		$currentdate = Carbon::now();
        $today = date('Y-m-d H:i:s');
        $data = ['isActive' => 0, 'isDelete' => 1, 'deleteIP' => $ip, 'deleteID' => Auth::guard('restaurant')->user()->code, 'deleteDate' => $today];        
         //activity log start
		$data1=$currentdate->toDateTimeString().	"	"	.$ip.	"	"	.Auth::guard('restaurant')->user()->code.	"	"."Restaurant Item".$code." is deleted";
		$this->model->activity_log($data1); 
		//activity log end
		echo $this->model->doEditWithField($data, 'restaurantitemmaster','code',$code);
    }
    
    
	public function getSubcategoryDetails(Request $request)
	{
		$menuCategoryCode = $request->menuCategoryCode;
		$subcategorydetails = $this->model->selectQuery(array('menusubcategory.code','menusubcategory.menuSubCategoryName'),'menusubcategory',array(),array('menusubcategory.menuCategoryCode' => array('=', $menuCategoryCode),'menusubcategory.isActive' => array('=', 1)));
		$html = "";
		if($subcategorydetails){
			$html = '<option value="">Select Menu Subcategory</option>';
			foreach($subcategorydetails as $sub){
				$html .= '<option value="' . $sub->code . '">' . $sub->menuSubCategoryName . '</option>';
			}
		}
		echo $html; 
	}
	
	public function uploadExcel(){
		return view('restaurant.restaurantitem.upload');
	}
	
	public function uploadData(Request $r){
		$currentdate = Carbon::now();
		$path = '../uploads/itemExcel/';
		if(file_exists($path.$r->file('uploadFile'))){
			unlink($path.$r->file('uploadFile'));
		}
		$fileItem =  $r->file('uploadFile');  
		$rowExcepts = $r->rowExcepts;
		$filenameItemImage = $fileItem->getClientOriginalName();          
		$fileItem->move($path,  $filenameItemImage);
		$inputFileName = $path . $filenameItemImage;
		$itemCodeArr=array();
		//$inputFileName = 'uploads/itemExcel/'; 
		try {
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
			$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true); 
			$objPHPExcel = $objReader->load($inputFileName);
			$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
			$allData = $objPHPExcel->getActiveSheet();
			//$total_line=count($allDataInSheet)-1;
			$total_line=0;
			$i=0;
			$cntr=0;
			unset($allDataInSheet[1]);
		    $rowExcepts =json_decode($rowExcepts,true);
			if(!empty($rowExcepts)){
				if(count($rowExcepts)>0){
					$total_line=$total_line+count($rowExcepts);
					for($j=0;$j<count($rowExcepts);$j++){
						unset($allDataInSheet[$rowExcepts[$j]+2]);
					}
				}
			}
			foreach ($allDataInSheet as $value) {
				if(count(array_filter($value)) == 0){
					
				}else{ 
				$total_line++;
				$inserdata[$i]['itemName'] = $value['A'];
				$restaurantCode = Auth::guard('restaurant')->user()->code;
				$inserdata[$i]['restaurantCode'] = $restaurantCode;
				$inserdata[$i]['salePrice'] = $value['B'];
				$inserdata[$i]['itemPackagingPrice'] = $value['C'];
				$inserdata[$i]['maxOrderQty'] = $value['D'];
				$inserdata[$i]['cuisineType'] = $value['E'];
				$inserdata[$i]['itemDescription'] = $value['G'];
				$menuCategoryCode = $this->getCodeFromName('menucategory',$value['H'],'menuCategoryName');
				$inserdata[$i]['menuCategoryCode'] = $menuCategoryCode;
				$menuSubcategoryCode = $this->getCodeFromName('menusubcategory',$value['I'],'menuSubCategoryName');
				$inserdata[$i]['menuSubCategoryCode'] = $menuSubcategoryCode;
				$itemActive=0;
				if(strtolower($value['J'])=='yes') $itemActive=1;
				$inserdata[$i]['itemActiveStatus'] = $itemActive;
				$inserdata[$i]['isActive'] = 1;
				$inserdata[$i]['isDelete'] = 0;
				$inserdata[$i]['addDate'] = $currentdate->toDateTimeString();
				$inserdata[$i]['addIP'] = $_SERVER['REMOTE_ADDR'];
				$inserdata[$i]['addID'] = Auth::guard('restaurant')->user()->code;
				$result = $this->model->addNewWithYear($inserdata[$i],'restaurantitemmaster','RITM');
				if($result==FALSE){ 
					$result=0;
				}else{
					$cntr++;
				}
				array_push($itemCodeArr, array("code"=>$result,"restaurantCode"=>$inserdata[$i]['restaurantCode']));
				$i++;
			  }
			}
			$incr=0;
			$uploadRootDir = '../uploads/';
			for($row=2;$row<=$allData->getHighestRow();$row++){
				foreach($allData -> getDrawingCollection() as $drawing) {
					if($incr<count($itemCodeArr)){
						$string = $drawing -> getCoordinates();
						$code = $itemCodeArr[$incr]['code'];
						$restaurantCode = $itemCodeArr[$incr]['restaurantCode'];
						$uploadDir = '../uploads/restaurant/restaurantitemimage/'.$restaurantCode;
						
						$coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($string);
						if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing) {
							$cellID = $drawing->getCoordinates();
							if($cellID==\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5).$row){
								$filename = $drawing -> getPath();
								if (! File::exists($uploadDir)) {
									File::makeDirectory($uploadDir,0777,true);
								}
								$entityImage='';
								$uploadedFileName = $code. '-' . time() . '.jpg';          
								copy($filename, $uploadDir.'/'.$uploadedFileName);
								if(file_exists($uploadDir.'/'.$uploadedFileName)){
									$entityImage=$uploadedFileName;
								}
								$subData = array(
									'itemPhoto' => $entityImage, 
								);
								$this->model->doEdit($subData,'restaurantitemmaster',$code);
							}
						}
					}
				}
				$incr++;
			}
			foreach($itemCodeArr as $itemCode){
				$condition["code"] = array("=",$itemCode['code']);
				$extraCondition = " (itemPhoto IS NULL OR itemPhoto='')";
				$check_empty_images_row = $this->model->selectQuery(array("restaurantitemmaster.code","restaurantitemmaster.restaurantCode"),"restaurantitemmaster",array(),$condition,array(),array(),"","",$extraCondition);
				if($check_empty_images_row && count($check_empty_images_row)){
					foreach($check_empty_images_row as $chk){
						$dummyFile='file_not_found.png';
						$filename=$uploadRootDir.$dummyFile;
						$uploadDir = '../uploads/restaurant/restaurantitemimage/'.$restaurantCode;
						if (! File::exists($uploadDir)) {
							File::makeDirectory($uploadDir,0777,true);
						}
						$entityImage='';
						$uploadedFileName = $chk->code. '-' . time() . '.png';          
						copy($filename, $uploadDir.'/'.$uploadedFileName);
						if(file_exists($uploadDir.'/'.$uploadedFileName)){
							$entityImage=$uploadedFileName;
						}
						$image = array(
							'itemPhoto' => $entityImage, 
						);
						$this->model->doEdit($image,'restaurantitemmaster',$chk->code);
					}
				}
			}
			if($total_line==$cntr){
				$response['status'] = true;
                    $response['text'] = 'Total : ' . $total_line . ' records. All records are saved';
                } else {
                    $response['status'] = false;
                    $response['text'] = 'Total Records: ' . $total_line . ' Successful: ' . $cntr . ' Unsuccessful: ' . ($total_line - $cntr);
                }
		} catch (Exception $e) {
			 $response['status'] = false;
            $response['text'] = 'Something went wrong';
		}
		echo json_encode($response);
	}
	
	public function generateExcelTemplate(){
		$filename = 'Restaurant Menu Item Excel ';
		$file = $filename . '.xlsx'; 
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$file.'"');
		header('Cache-Control: max-age=0');

		$spreadsheet = new Spreadsheet();
		$writer = new Xlsx($spreadsheet);
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle($filename);
		$sheet->setCellValue('A1', 'Item Name');
		$sheet->setCellValue('B1', 'Sale Price');
		$sheet->setCellValue('C1', 'Packaging Charges');
		$sheet->setCellValue('D1', 'Max Order Qty');
		$sheet->setCellValue('E1', 'Cuisine Type');
		$sheet->setCellValue('F1', 'Item Image');
		$sheet->setCellValue('G1', 'Item Description');
		$sheet->setCellValue('H1', 'Menu Category');
		$sheet->setCellValue('I1', 'Menu Subcategory');
		$sheet->setCellValue('J1', 'Item Status');
		$sheet->getStyle('A1:J1')->getFont()->setSize(16);
		$sheet->getStyle('A1:J1')->getFont()->setBold(true);
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
	}
	
	public function validateExcelFile(Request $r){
		$rowArray = $r->convertedIntoArray;
        $cuisineLineStr = 'Invalid Cuisine Type: ';
        $menuCateLineStr = 'Invalid Menu Category: ';
        $menuSubcLineStr = 'Invalid Menu Subcategory: ';
        $itemStatusLineStr = 'Invalid Item Status: ';
        $str =  $cuisineStr = $menuCateStr=$menuSubcStr=$itemStatusStr='';
		$statusArr = ['yes','no'];
		$cuisineTypeArr = ['veg','nonveg'];
        $returnRowArray = [];
        for ($i = 0; $i < count($rowArray); $i++) {
            if (!in_array(strtolower($rowArray[$i][5]), $cuisineTypeArr)) {
                $cuisineStr = $cuisineStr . " Row" . ($i + 1) . ", ";
				 if (!in_array($i, $returnRowArray)) {
					array_push($returnRowArray, $i);
				 }
            }
			if (!in_array(strtolower($rowArray[$i][10]), $statusArr)) {
                $itemStatusStr = $itemStatusStr . " Row" . ($i + 1) . ", ";
				 if (!in_array($i, $returnRowArray)) {
					array_push($returnRowArray, $i);
				 }
            }
			
			$menuCateCode = $this->getCodeFromName('menucategory',$rowArray[$i][8],'menuCategoryName');
			if($menuCateCode=='invalid'){
                $menuCateStr = $menuCateStr . " Row" . ($i + 1) . ", ";
                if (!in_array($i, $returnRowArray)) {
                    array_push($returnRowArray, $i);
                }
            }else{
				$condition['menusubcategory.isActive'] = array('=',1);
				$condition['menusubcategory.menuCategoryCode'] = array('=',$menuCateCode);
				$extraCondition = " lower(menusubcategory.menuSubCategoryName)='".strtolower($rowArray[$i][9])."'";
				$subCateData = $this->model->selectQuery(array('menusubcategory.code'),'menusubcategory',array(),$condition,array(),array(),"","",$extraCondition);
				if($subCateData && count($subCateData)>0){}else{
					$menuSubcStr = $menuSubcStr . " Row" . ($i + 1) . ", ";
					if (!in_array($i, $returnRowArray)) {
						array_push($returnRowArray, $i);
					}
				}
				
			}
        }
        $msg = '';
        if ($cuisineStr != '') $msg = $msg . '<li><h5><b>'.$cuisineLineStr.' : </b><span style="color:#F68D0F">'. rtrim($cuisineStr, ', ').'</span></h5></li>';
		if ($menuCateStr != '') $msg = $msg . '<li><h5><b>'.$menuCateLineStr.' : </b><span style="color:#F68D0F">'. rtrim($menuCateStr, ', ').'</span></h5></li>';
		if ($menuSubcStr != '') $msg = $msg . '<li><h5><b>'.$menuSubcLineStr.' : </b><span style="color:#F68D0F">'. rtrim($menuSubcStr, ', ').'</span></h5></li>';
		if ($itemStatusStr != '') $msg = $msg . '<li><h5><b>'.$itemStatusLineStr.' : </b><span style="color:#F68D0F">'. rtrim($itemStatusStr, ', ').'</span></h5></li>';
		if($msg!='') $msg = '<ul>'.$msg.'</ul>';
       $arr = array(
            "msg" => $msg,
            "rowArr" => json_encode($returnRowArray)
        );
        echo json_encode($arr);
	}
	
	public function getCodeFromName($tableName,$checkStr,$field){
		$condition["isActive"] = array("=",1);
		$extraCondition = " lower($field)='".strtolower($checkStr)."'";
		$getCode = $this->model->selectQuery(array("$tableName.code"),"$tableName",array(),$condition,array(),array(),"","",$extraCondition);
		if($getCode && count($getCode)){
			return $getCode[0]->code;
		}else{
			return 'invalid';
		}
	}
	

}
