<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;
use App\Models\CustomAddressMaster;
use App\Models\Restaurants;
use App\Models\Users;
use App\Models\Customer;
use App\Models\RestaurantHours;
use App\Classes\Notificationlibv_3;

class CronjobController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function cutletCronjob()
    {
        $currentdate = Carbon::now();
        $currentMinutes = $currentdate->format("i");
        if (($currentMinutes % 30 == 0) || ($currentMinutes == '00')) {
            $this->changeDeliveryBoyStatus();
            $this->assignDeliveryBoy();
        }
        if (($currentMinutes % 15 == 0) || ($currentMinutes == '00')) {
            $this->updateRestaurantServiceablity();
        }
        if (($currentMinutes % 60 == 0) || ($currentMinutes == '00')) {
            $this->updatePendingCutletStatus();
        }
    }

    public function connectServer()
    {

        $result = exec("/opt/cpanel/ea-nodejs16/bin/node server/app.js", $node_output, $rcode);
        if ($rcode == 1) {
            echo 'server started';
        } else {
            echo 'server not started';
        }
    }

    public function assignDeliveryBoy()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $tableName = "deliveryBoyActiveOrder";
        $orderColumns = array("deliveryBoyActiveOrder.*", "usermaster.deliveryType", "usermaster.cityCode");
        $condition = array('deliveryBoyActiveOrder.loginStatus' => array('=', 1), 'deliveryBoyActiveOrder.orderCount' => array('=', '0'), 'usermaster.isActive' => array('=', 1), 'deliveryBoyActiveOrder.isActive' => array('=', 1), 'usermaster.role' => array('=', 'DBOY'), 'usermaster.deliveryType' => array('=', 'food'));
        $joinType = array('usermaster' => 'inner');
        $orderBy = array('deliveryBoyActiveOrder.id' => "ASC");
        $join = array('usermaster' => array('deliveryBoyActiveOrder.deliveryBoyCode', 'usermaster.code'));
        $checkData = $this->model->selectQuery($orderColumns, $tableName, $join, $condition, $orderBy, array(), '', '', "", $joinType);
        if (!empty($checkData)) {
            foreach ($checkData as $d) {
                $deliveryBoyCode = $d->deliveryBoyCode;
                $deliveryBoyCity = $d->cityCode;
                $actCode = $d->code;
                if ($d->orderCode == "" || $d->orderCode == null) {
                    DB::enableQueryLog();
                    $limit = 1;
                    $tableName1 = "restaurantordermaster";
                    $orderColumns1 = array("restaurantordermaster.*");
                    $condition1 = array('restaurantordermaster.orderStatus' => array('=', 'PND'), 'restaurant.cityCode' => array('=', $deliveryBoyCity));
                    $orderBy1 = array('restaurantordermaster.id' => "ASC");
                    $extraCondition1 = " (restaurantordermaster.deliveryBoyCode IS NULL or restaurantordermaster.deliveryBoyCode='')";
                    $extraCondition1 .= " and (restaurantordermaster.defaultStatus IS NULL or restaurantordermaster.defaultStatus='')";
                    $joinType1 = array('restaurant' => 'inner');
                    $join1 = array('restaurant' => array('restaurantordermaster.restaurantCode', 'restaurant.code'));
                    $getPendingOrders = $this->model->selectQuery($orderColumns1, $tableName1, $join1, $condition1, $orderBy1, array(), $limit, '', $extraCondition1, $joinType1);
                    //$query_1 = DB::getQueryLog();
                    //print_r($query_1); 
                    if (!empty($getPendingOrders) && $d->deliveryType = "food") {
                        foreach ($getPendingOrders as $r) {

                            $orderCode = $r->code;

                            //check if deliveryBoy released the selected order
                            $hasReleasedOrder = $this->model->hasDeliveryboyReleasedOrder($deliveryBoyCode, $orderCode);
                            //$hasReleasedOrder=true;
                            if ($hasReleasedOrder == false) {
                                $dataUpCnt['orderCount'] = 1;
                                $dataUpCnt['orderCode'] = $orderCode;
                                $dataUpCnt['orderType'] = 'food';
                                $dataUpCnt['editDate'] = date('Y-m-d H:i:s');
                                $dataUpCnt['editIP'] = $_SERVER['REMOTE_ADDR'];
                                $resultUpdateDB = $this->model->doEdit($dataUpCnt, 'deliveryBoyActiveOrder', $actCode);

                                if ($resultUpdateDB == true) {
                                    //activity log start
                                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . $deliveryBoyCode .    "	" . "Order " . $orderCode . " assign through cronjob to " . $deliveryBoyCode;
                                    $this->model->activity_log($data);
                                    //activity log end

                                    $dataUpdateDb['deliveryBoyCode'] = $deliveryBoyCode;
                                    $dataUpdateDb['editDate'] = date('Y-m-d H:i:s');
                                    $dataUpdateDb['editIP'] = $_SERVER['REMOTE_ADDR'];
                                    $resultUpdateDB = $this->model->doEdit($dataUpdateDb, 'restaurantordermaster', $orderCode);

                                    //send notification to the delivery boy
                                    $message = 'Order (' . $orderCode . ') is successfully assign. ';
                                    $title = 'New Order';
                                    $userData  = Users::select("usermaster.firebase_id")
                                        ->where('usermaster.code', $deliveryBoyCode)
                                        ->first();

                                    if (!empty($userData)) {
                                        $DeviceIdsArr = array();
                                        $DeviceIdsArr[] = $userData->firebase_id;
                                        $this->sendNotification($DeviceIdsArr, $title, $message, $orderCode);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo 'false';
        }
    }

    public function updateRestaurantServiceablity() 
    {
		DB::enableQueryLog();
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $dayofweek = strtolower(date('l'));
        $currtime = date('H:i:s');
        $resResult = Restaurants::select("restauranthours.restaurantCode", "restaurant.isServiceable")
            ->join("restauranthours", "restauranthours.restaurantCode", "=", "restaurant.code")
            ->where('restauranthours.weekDay', $dayofweek)
            ->whereRaw("(CAST( ' " . $currtime . "' AS time ) BETWEEN `restauranthours`.`fromTime` AND `restauranthours`.`toTime` OR ( NOT CAST( ' " . $currtime . "' AS time ) BETWEEN `restauranthours`.`toTime` AND `restauranthours`.`fromTime` AND `restauranthours`.`fromTime` > `restauranthours`.`toTime`))")
            ->groupBy('restauranthours.restaurantCode')
            ->get();
        $inRestaurantCode = [];
        //$query_1 = DB::getQueryLog();
       // print_r($query_1);   
        if (!empty($resResult)) {
            foreach ($resResult as $r) {
                array_push($inRestaurantCode, $r->restaurantCode);
                //activity log start
                $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . $r->restaurantCode .    "	" . $r->restaurantCode . " service status updated.";
                $this->model->activity_log($data);
                //activity log end
            }
            if ($inRestaurantCode != "") {
                $result = Restaurants::where('manualIsServiceable', 1)->whereIN('code', $inRestaurantCode)->update([
                    'isServiceable' => 1
                ]);
                $result = Restaurants::where('manualIsServiceable', 1)->whereNotIn('code', $inRestaurantCode)->update([
                    'isServiceable' => 0
                ]);
            }
        }
    }

    public function changeDeliveryBoyStatus()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $currtime = date('H:i:s');
        DB::enableQueryLog();
        $dBoyResult = Users::select("usermaster.*", "deliveryBoyActiveOrder.loginStatus", "deliveryBoyActiveOrder.deliveryBoyCode")
            ->join("deliveryBoyActiveOrder", "usermaster.code", "=", "deliveryBoyActiveOrder.deliveryBoyCode")
            ->where('usermaster.isActive', 1)
            ->whereRaw("(usermaster.isDelete=0 OR usermaster.isDelete IS NULL) AND (deliveryBoyActiveOrder.loginStatus='0')")
            ->whereRaw("(CAST( '" . $currtime . "' AS time ) BETWEEN `usermaster`.`availableStartTime` AND `usermaster`.`availableEndTime`)")
            ->orderBy('usermaster.id', 'DESC')
            ->get();
        //$query_1 = DB::getQueryLog();
        //print_r($query_1);
        if (!empty($dBoyResult)) {
            foreach ($dBoyResult as $result) {
                $DBcode = $result->deliveryBoyCode;
                $data = array('loginStatus' => 1);
                $updatedResult = $this->model->doEditWithField($data, 'deliveryBoyActiveOrder', 'deliveryBoyCode', $DBcode);
                if ($updatedResult == true) {
                    //activity log start
                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . $DBcode .    "	" . $DBcode . " login status updated.";
                    $this->model->activity_log($data);
                    //activity log end
                }
            }
        }
    }

    public function updatePendingCutletStatus()
    {
        DB::enableQueryLog();
        $ip = $_SERVER['REMOTE_ADDR'];
        $currentdate = Carbon::now();
        $currtime = date('Y-m-d H:i:s');
        $orderColumns = array("clientmaster.walletPoints", "wallettransactions.orderCode", "wallettransactions.point", "wallettransactions.code", "wallettransactions.clientCode", "wallettransactions.status");
        $conditions = array("wallettransactions.isActive" => array("=", 1), "clientmaster.isActive" => array("=", 1), "restaurantordermaster.isActive" => array("=", 1), "restaurantordermaster.orderStatus" => array("=", "DEL"), "wallettransactions.status" => array("=", "pending"));       
        $join = array(
            "restaurantordermaster" => array("restaurantordermaster.code", "wallettransactions.orderCode"),
            "clientmaster" => array("clientmaster.code", "restaurantordermaster.clientCode")
        );
        $extraCondition = " TIMESTAMPDIFF(hour,wallettransactions.addDate,'$currtime') >= '24'";
        $checkPendingCutlet = $this->model->selectQuery($orderColumns, "wallettransactions", $join, $conditions, array(), array(), "", "", $extraCondition);
        /*
        $query_1 = DB::getQueryLog();
        print_r($query_1);
        print_r($checkPendingCutlet);
        exit;
        */
        if ($checkPendingCutlet && count($checkPendingCutlet) > 0) {
            foreach ($checkPendingCutlet as $chk) {
                $updateData['wallettransactions.status'] = "success";
                $updateData['wallettransactions.editDate'] = $currtime;
                $updatedResult = $this->model->doEdit($updateData, 'wallettransactions', $chk->code);
                if ($updatedResult) {
                    $newWalletPoint = $chk->point + $chk->walletPoints;
                    DB::table("clientmaster")->where("code", $chk->clientCode)->update(["walletPoints" => $newWalletPoint]);
                    $message = 'Your cutlet points Rs.' . $chk->point . ' for order ' . $chk->orderCode . ' is activated. New wallet Balance is ' . $newWalletPoint;
                    $title = 'Order ' . $chk->orderCode . ' Cutlet Points Activated';
                    $customerData  = DB::table('clientdevicedetails')->select("clientdevicedetails.firebaseId")
                        ->where('clientdevicedetails.clientCode', $chk->clientCode)
                        ->first();
                    if (!empty($customerData)) {
                        if ($customerData->firebaseId != "" && $customerData->firebaseId != null) {
                            $DeviceIdsArr[] = $customerData->firebaseId;
                            $this->sendNotification($DeviceIdsArr, $title, $message, $chk->orderCode);
                        }
                    }
                    $data = $currentdate->toDateTimeString() .    "	"    . $ip .    "	"    . "cron job" .    "	" . $chk->code . " updated transaction status from pending to success";
                    $this->model->activity_log($data);
                }
            }
        }
    }

    public function sendNotification($DeviceIdsArr = array(), $title, $message, $orderCode)
    {
        $random = rand(0, 999);
        $dataArr = $notification = array();
        $dataArr['device_id'] = $DeviceIdsArr;
        $dataArr['message'] = $message;
        $dataArr['title'] = $title;
        $dataArr['order_id'] = $orderCode;
        $dataArr['random_id'] = $random;
        $dataArr['type'] = 'order';
        $notification['device_id'] = $DeviceIdsArr;
        $notification['message'] = $message;
        $notification['title'] = $title;
        $notification['order_id'] = $orderCode;
        $notification['random_id'] = $random;
        $notification['type'] = 'order';
        $noti = new Notificationlibv_3;
        $result = $noti->sendNotification($dataArr, $notification);
        //print_r($result);
    }
}
