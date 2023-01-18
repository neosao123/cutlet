<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalModel;
use App\Models\ApiModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
use App\Classes\Razorpayment;
use App\Models\Restaurants;
use App\Models\Wallet;
use App\Models\Customer;
use App\Classes\Notificationlibv_3;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use App\Classes\FirestoreActions;

class CustomerController extends Controller
{
    public function __construct(GlobalModel $model, ApiModel $apimodel)
    {
        $this->model = $model;
        $this->apimodel = $apimodel;
    }

    public function getCityList()
    {
        $result = $this->model->selectQuery(["citymaster.id", "citymaster.code", "citymaster.cityName", "citymaster.isActive"], "citymaster", array(), array("citymaster.isActive" => array('=', 1)), array("citymaster.id" => "ASC"), [], '', '');
        $data = array();
        if ($result && $result->count() > 0) {
            return response()->json(["status" => 200, "result" => $result], 200);
        } else {
            return response()->json(["status" => 300, "message" => "No data found"], 200);
        }
    }

    public function sendotpsms($number, $otp)
    {
        $message = "Your OTP is $otp. Do not share OTP with anyone. - Hippyness";
        $template = str_replace(" ", "%20", $message);

        $apikey = "LdBVqZARBEi9p6ewMUs3Kg";
        $senderid = 'BHippy';

        $url = "https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=$apikey&senderid=$senderid";
        $url .= "&channel=2&DCS=0&flashsms=0&number=$number&text=$template&route=1";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        try {
            $res = json_decode($response);
            if ($res->ErrorCode  == "000") return true;
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function sendRegisterOTP(Request $r)
    {
        $dataValidate = $r->validate([
            'contactNumber' => 'required|digits:10',
        ]);
        $contactNumber = $dataValidate['contactNumber'];
        $otpNumber = $this->apimodel->generateOTPMaster($contactNumber);
        $result = $this->sendotpsms($contactNumber, $otpNumber);
        if ($result) {
            return response()->json(["status" => 200, "message" => "OTP was sent successfully!", "result" => $otpNumber], 200);
        } else {
            return response()->json(["status" => 300, "message" => "Failed to send  OTP!"], 200);
        }
    }

    public function verifyRegisterOTP(Request $r)
    {
        $dataValidate = $r->validate([
            'contactNumber' => 'required|digits:10',
            'otp' => 'required',
        ]);
        $otp = $dataValidate['otp'];
        $contactNumber = $dataValidate['contactNumber'];
        /*$customer = Customer::where('mobile',$dataValidate['contactNumber'])->where('isActive',1)->first();
		if ($customer) {*/
        $result = $this->apimodel->checkRegisterOTP($otp, $contactNumber);
        if ($result) {
            $condition = array(
                "clientmaster.mobile" => $contactNumber,
                "clientmaster.isActive" => 1
            );
            $res = $this->apimodel->read_user_information($condition);
            if ($res) {
                $customer = Customer::where('mobile', $dataValidate['contactNumber'])->where('isActive', 1)->first();
                $token = $customer->createToken('CutletCustomerApp')->plainTextToken;
                $usdata = $res[0];
                return response()->json(["status" => 200, "token" => $token, "message" => "OTP Verified and Logged in successfully", "isUserExists" => true, "result" => $usdata], 200);
            } else {
                $res = (object)[];
                return response()->json(["status" => 200, "message" => "OTP Verified but User does not exists", "isUserExists" => false, "result" => $res], 200);
            }
        } else {
            $res = (object)[];
            return response()->json(["status" => 300, "message" => "Invalid OTP", "result" => $res], 200);
        }
        /*}else{
			$res = (object)[];
			return response()->json(["status" => 300,"message" => "User not found","result"=>$res], 200);
		}*/
    }

    public function registration(Request $r)
    {
        $contactNumber = $r->contactNumber;
        $cityCode = $r->cityCode;
        $name = $r->name;
        $emailId = $r->emailId;
        if (isset($contactNumber) && $contactNumber != '' && isset($cityCode) && $cityCode  != '' && isset($name) && $name  != '') {
            if (strlen($contactNumber) == 10) {
                $checkIfCityValid = $this->model->selectQuery("citymaster.code", "citymaster", array(), array("citymaster.code" => array('=', $cityCode)));
                if ($checkIfCityValid != false && count($checkIfCityValid) > 0) {
                    $customer = Customer::where('mobile', $contactNumber)->where('isActive', 1)->first();
                    if ($customer) {
                        $code = $customer->code;
                        $condition = array(
                            "clientmaster.code" => $code
                        );
                        $resultData = $this->apimodel->read_user_information($condition);
                        $result['userData'] = $resultData[0];
                        return response()->json(["status" => 300, "message" => "User Already Exists", "result" => $result], 200);
                    } else {
                        $insertArr = array(
                            "mobile" => $contactNumber,
                            'cityCode' => $cityCode,
                            'name' => $name,
                            "isActive" => 1
                        );
                        if (isset($emailId) && $emailId != "") {
                            $insertArr['emailId'] = $emailId;
                        }
                        $insertResult = $this->model->addNewWithYear($insertArr, 'clientmaster', 'CLNT');
                        if ($insertResult) {
                            $customer = Customer::where('mobile', $contactNumber)->where('isActive', 1)->first();
                            $token = $customer->createToken('CutletCustomerApp')->plainTextToken;
                            $data = array(
                                "clientCode" => $insertResult,
                                "isActive" => 1
                            );
                            $profileResult = $this->model->addWithoutCode($data, 'clientprofile');
                            if ($profileResult) {
                                $condition = array(
                                    "clientmaster.code" => $insertResult
                                );
                                $resultData = $this->apimodel->read_user_information($condition);
                                $result = $resultData[0];
                                return response()->json(["status" => 200, "token" => $token, "message" => "Registration Successful..", "result" => $result], 200);
                            } else {
                                return response()->json(["status" => 300, "message" => "Failed to register"], 200);
                            }
                        } else {
                            return response()->json(["status" => 300, "message" => " Opps...! Something went wrong please try again."], 200);
                        }
                    }
                } else {
                    return response()->json(["status" => 300, "message" => "Invalid City"], 200);
                }
            } else {
                return response()->json(["status" => 300, "message" => "Contact Number should be exactly 10 digit number only!"], 200);
            }
        } else {
            return response()->json(["status" => 400, "message" => " * are required field(s)."], 200);
        }
    } //end registration  Process

    //get Custom added address and area list
    public function getCustomAddressList(Request $r)
    {
        $dataValidate = $r->validate([
            'cityCode' => 'required',
        ]);
        $cityCode = $dataValidate['cityCode'];
        $condition = array("customaddressmaster.isService" => array('=', 1), "customaddressmaster.isActive" => array('=', 1), "customaddressmaster.cityCode" => array('=', $cityCode));
        $addressDetails = $this->model->selectQuery("customaddressmaster.*", "customaddressmaster", array(), $condition);
        if ($addressDetails != false && count($addressDetails) > 0) {
            $addressList = [];
            foreach ($addressDetails as $row) {
                $data = array(
                    'addressCode' => $row->code,
                    'place' => $row->place,
                    'district' => $row->district,
                    'taluka' => $row->taluka,
                    'pincode' => $row->pincode,
                    'state' => $row->state,
                );
                array_push($addressList, $data);
            }
            $result['addressList'] = $addressList;
            return response()->json(["status" => 200, "message" => " Address List where Services Available", "result" => $result], 200);
        } else {
            return response()->json(["status" => 300, "message" => " No data Found"], 400);
        }
    }

    public function gethomeSliderImages(Request $req)
    {
        $dataValidate = $req->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $imageArray = array();
        $cityCode = "";
        $geoLocation = ",ROUND( 6371 * acos( cos( radians(" . $req->latitude . ") ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians(" . $req->longitude . ") ) + sin( radians(" . $req->latitude . ") ) * sin(radians(restaurant.latitude)) ) ) AS distance ";
        $having = " HAVING distance <= 15";

        $query = "select `homeslider`.* $geoLocation from `homeslider`";
        $query .= " inner join `restaurant` on `homeslider`.`restaurantCode` = `restaurant`.`code` ";
        $query .= " where `homeslider`.`isActive` = 1 ";
        $query .= "  $having order by `homeslider`.`id` desc";

        $images_result = DB::select($query);
        if (!empty($images_result)) {
            for ($img = 0; $img < sizeof($images_result); $img++) {
                $imgData['imagePath'] = '';
                $cityCode = $images_result[$img]->cityCode;
                $imgData['code'] = $images_result[$img]->code;
                $imgData['restaurantCode'] = $images_result[$img]->restaurantCode;
                if (file_exists('uploads/homeslider/' . $images_result[$img]->imagePath)) {
                    $imgData['imagePath'] = url('uploads/homeslider/' . $images_result[$img]->imagePath);
                }
                array_push($imageArray, $imgData);
            }
        }

        $data["cityCode"] = $cityCode;
        if ($cityCode != "") {
            $cond = array("homeslider.isActive" => array("=", 1), "homeslider.cityCode" => array("=", $cityCode));
            $orderBy = array("homeslider.id" => 'ASC');
            $extraCondition = " homeslider.restaurantCode is null";
            $images_result = $this->model->selectQuery(array("homeslider.*"), 'homeslider', array(), $cond, $orderBy, array(), "", "", $extraCondition);
            if ($images_result) {
                for ($img = 0; $img < sizeof($images_result); $img++) {
                    $imgData['imagePath'] = '';
                    $imgData['code'] = $images_result[$img]->code;
                    $imgData['restaurantCode'] = $images_result[$img]->restaurantCode;
                    if (file_exists('uploads/homeslider/' . $images_result[$img]->imagePath)) {
                        $imgData['imagePath'] = url('uploads/homeslider/' . $images_result[$img]->imagePath);
                    }
                    array_push($imageArray, $imgData);
                }
            }
        }

        if (count($imageArray) > 0) {
            $data['homesliderImages'] = $imageArray;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $data], 200);
        } else {
            $data = (object)[];
            return response()->json(["status" => 300, "message" => "Data not found.", "result" => $data], 200);
        }
    }

    //Customer profile
    public function getUserProfile(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
        ]);
        $clientCode = $dataValidate['clientCode'];
        $orderColumns = array("clientmaster.code", "clientmaster.name", "clientmaster.emailId", "clientmaster.mobile", "clientmaster.cityCode", "clientmaster.cartCode", "clientmaster.isCodEnabled", "clientprofile.gender", "clientprofile.address", "clientprofile.latitude", "clientprofile.longitude", "clientprofile.areaCode", "clientprofile.addressType", "clientprofile.area", "clientprofile.local", "clientprofile.flat", "clientprofile.pincode", "clientprofile.state", "clientprofile.landmark", "citymaster.cityName");
        $cond = array("clientmaster.code" => array("=", $clientCode), 'clientprofile.isActive' => array("=", 1));
        $orderBy = array("clientmaster.id" => 'ASC', "clientprofile.id" => "DESC");
        $join = array('citymaster' => array('citymaster.code', 'clientmaster.cityCode'), "clientprofile" => array('clientprofile.clientCode', 'clientmaster.code'));
        $resultData = $this->model->selectQuery($orderColumns, 'clientmaster', $join, $cond, $orderBy);
        if ($resultData) {
            $result['userProfile'] = $resultData;
            return response()->json(["status" => 200, "result" => $result], 200);
        } else {
            return response()->json(["status" => 300, "msg" => "No Data Found!"], 200);
        }
    }
    public function updateProfile(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'name' => 'required',
        ]);
        $emailID = '';
        if (isset($r->emailId) && $r->emailId != "") {
            if (filter_var($r->emailId, FILTER_VALIDATE_EMAIL)) {
                $emailID = $r->emailId;
            } else {
                return response()->json(["status" => 300, "message" => "Invalid Email ID"], 200);
            }
        }
        $dataMaster = [
            "name" => $r->name,
        ];
        if ($emailID != "") {
            $dataMaster["emailId"] = $emailID;
        }
        if (isset($r->cityCode) && $r->cityCode != "") {
            $dataMaster["cityCode"] = $r->cityCode;
        }
        $resultData = $this->model->selectDataByCode('clientmaster', $r->clientCode);
        $array = json_decode(json_encode($resultData), true);
        if ($array && count($array) > 0) {
            $resultMaster = $this->model->doEdit($dataMaster, 'clientmaster', $r->clientCode);
            if ($resultMaster != false) {
                return response()->json(["status" => 200, "message" => "Your profile has been updated successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => " Failed to update your profile."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function updateProfileAddress(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'area' => 'required',
            'local' => 'required',
            'flat' => 'required',
            'pincode' => 'required',
            'areaCode' => 'required',
        ]);
        $resultData = $this->model->selectDataByCode('clientmaster', $r->clientCode);
        $array = json_decode(json_encode($resultData), true);
        if ($array && count($array) > 0) {
            $dataProfile = [
                "city" => $r->city,
                "local" => $r->local,
                "area" => $r->area,
                "state" => $r->state,
                "flat" => $r->flat,
                "pincode" => $r->pincode,
                "areaCode" => $r->areaCode,
            ];
            if (isset($r->landMark) && $r->landMark != "") {
                $dataProfile["landMark"] = $r->landMark;
            }
            $resultProfile = $this->model->doEditWithField($dataProfile, 'clientprofile', 'clientCode', $r->clientCode);
            if ($resultProfile != false) {
                return response()->json(["status" => 200, "message" => "Your profile address has been updated successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => " Failed to update your profile."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function use_User_Coupon($actionType, $restaurantofferCode, $couponCode, $clientCode, $decidedExisitingLimit, $restaurantCode)
    {
        if ($actionType == "add") {
            $data['restaurantofferCode'] = $restaurantofferCode;
            $data['couponCode'] = $couponCode;
            $data['clientCode'] = $clientCode;
            $data['decidedExisitingLimit'] = $decidedExisitingLimit;
            $data['restaurantCode'] = $restaurantCode;
            $data['addID'] = $clientCode;
            $data['addIP'] = $_SERVER['REMOTE_ADDR'];
            $data['usedDate'] = date('Y-m-d H:i:s');
            $data['isActive'] = 1;
            $insert = $this->model->addNew($data, 'couponusesdetail', 'CUDS');
            if ($insert != 'false') {
                return true;
            } else {
                return false;
            }
        } else {
            $data['decidedExisitingLimit'] = $decidedExisitingLimit;
            $data['editID'] = $clientCode;
            $data['editIP'] = $_SERVER['REMOTE_ADDR'];
            $data['usedDate'] = date('Y-m-d H:i:s');
            try {
                DB::table('couponusesdetail')
                    ->where('clientCode', $clientCode)
                    ->where('restaurantCode', $restaurantCode)
                    ->where('couponCode', $couponCode)
                    ->where('restaurantofferCode', $restaurantofferCode)
                    ->update($data);
                return true;
            } catch (Exception $e) {
                return alse;
            }
        }
    }

    public function updateFirebaseId(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'firebaseId' => 'required',
            'os_version' => 'required',
            'app_version' => 'required',
            'mobile_model' => 'required'
        ]);
        $clientCode = $r->clientCode;
        if ((isset($r->os_version)) && $r->os_version != "") {
            $condition = array('clientdevicedetails.clientCode' => array('=', $clientCode), 'clientdevicedetails.mobile_model' => array("=", $r->mobile_model), 'clientdevicedetails.app_version' => array("=", $r->app_version));
            $device = $this->model->selectQuery(array("clientdevicedetails.code"), "clientdevicedetails", array(), $condition);
            if ($device && count($device) > 0) {
                foreach ($device as $d) {
                    $deviceCode = $d->code;
                }
                $dataDevice['firebaseID'] = $r->firebaseId;
                $dataDevice['editID'] = $clientCode;
                $dataDevice['editIP'] = $_SERVER['REMOTE_ADDR'];
                $result = $this->model->doEdit($dataDevice, 'clientdevicedetails', $deviceCode);
                if ($result != false) {
                    return response()->json(["status" => 200, "message" => "Firebase Id Update Successfully"], 200);
                } else {
                    return response()->json(["status" => 300, "message" => " Failed to update Firebase Id."], 200);
                }
            } else {
                $dataDevice['os_version'] = $r->os_version;
                $dataDevice['app_version'] = $r->app_version;
                $dataDevice['mobile_model'] = $r->mobile_model;
                $dataDevice['clientCode'] = $clientCode;
                $dataDevice['firebaseID'] = $r->firebaseId;
                $dataDevice['addID'] = $clientCode;
                $dataDevice['addIP'] = $_SERVER['REMOTE_ADDR'];
                $result = $this->model->addNewWithYear($dataDevice, 'clientdevicedetails', 'CDD');
                if ($result) {
                    return response()->json(["status" => 200, "message" => "Firebase Id Update Successfully"], 200);
                } else {
                    return response()->json(["status" => 300, "message" => " Failed to update Firebase Id."], 200);
                }
            }
        } else {
            return response()->json(["status" => 300, "message" => " Failed to find device details!"], 200);
        }
    }

    public function getMaintenanceDetails()
    {
        $resultData = $this->model->selectQuery(array('settings.*'), 'settings', array(), array('settings.settingName' => array('=', 'maintenance_mode')));
        if ($resultData && count($resultData) > 0) {
            $maintenance_mode = array();
            foreach ($resultData as $result) {
                $maintenance_mode['maintenance'] = $result->settingValue;
                $maintenance_mode['messageTitle'] = $result->messageTitle;
                $maintenance_mode['messageDescription'] = $result->messageDescription;
            }
            return response()->json(["status" => 200, "result" => $maintenance_mode], 200);
        } else {
            return response()->json(["status" => 400, "message" => " * are required field(s)."], 400);
        }
    }

    public function getCityByLatitudeLongitude(Request $r)
    {
        $dataValidate = $r->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $loc_latitude = $dataValidate['latitude'];
        $loc_longitude = $dataValidate['longitude'];
        // 		$Result = $this->model->selectQuery(array('customaddressmaster.*'), 'customaddressmaster',array(), array('customaddressmaster.isActive' => array('=',1)));
        // 		if ($Result) {
        // 			foreach ($Result as $key) {
        // 				$latitude = $key->latitude;
        // 				$longitude = $key->longitude;
        $radius = 7;
        $R = 6371;
        $maxLat = $loc_latitude + rad2deg($radius / $R);
        $minLat = $loc_latitude - rad2deg($radius / $R);
        $maxLon = $loc_longitude + rad2deg(asin($radius / $R) / cos(deg2rad($loc_latitude)));
        $minLon = $loc_longitude - rad2deg(asin($radius / $R) / cos(deg2rad($loc_latitude)));
        $query = DB::table('customaddressmaster')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLon, $maxLon])
            ->where('isActive', 1)
            ->get();
        if ($query && count($query) > 0) {
            foreach ($query as $qu) {
                $areaCode = $qu->code;
                $place = $qu->place;
                $cityCode = $qu->cityCode;
                $isService = $qu->isService;
                $cityName = "";
                $Result = $this->model->selectQuery(array('citymaster.cityName'), 'citymaster', array(), array('citymaster.code' => array('=', $cityCode)));
                if ($Result) {
                    foreach ($Result as $res) {
                        $cityName = $res->cityName;
                    }
                }
                $data['address'] = array('cityCode' => $cityCode, 'cityName' => $cityName, 'areaCode' => $areaCode, 'place' => $place, 'isService' => $isService);
                return response()->json(["status" => 200, "message" => "Address Updated Successfully.", "result" => $data], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "No Data Found."], 200);
        }
        // 			}
        // 		} else {
        // 			return response()->json(["status" => 300, "message" => "No Data Found."], 200);
        // 		}
    }

    public function checkServiceableAreaByLatitudeLongitude(Request $r)
    {
        $dataValidate = $r->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'restaurantCode' => 'required'
        ]);
        $loc_latitude = $dataValidate['latitude'];
        $loc_longitude = $dataValidate['longitude'];
        $restaurantCode = $dataValidate['restaurantCode'];
        $distance = 10;
        $query = "SELECT ROUND( 6371 * acos( cos( radians('" . $loc_latitude . "') ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians('" . $loc_longitude . "') ) + sin( radians('" . $loc_latitude . "') ) * sin(radians(restaurant.latitude)) ) ) AS distance ,restaurant.* FROM `restaurant` where restaurant.code='" . $restaurantCode . "' having distance < " . $distance;
        $result = DB::select($query);
        if (!empty($result) && count($result) > 0) {
            return response()->json(["status" => 200, "message" => "Serviceable location."], 200);
        } else {
            return response()->json(["status" => 300, "message" => "This Location is too far away from the restaurent. Please pickup a location near to the restaurent."], 200);
        }
    }

    public function addClientAddress(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'addressType' => 'required',
            'flat' => 'required',
            'landMark' => 'required',
            'area' => 'required',
            'cityCode' => 'required',
        ]);
        $clientCode = $dataValidate["clientCode"];
        $Result = $this->model->selectDataByCode('clientmaster', $clientCode);
        if ($Result) {
            $ResultUpdateData = $this->model->selectQuery(array('clientprofile.*'), 'clientprofile', array(), array('clientprofile.clientCode' => array("=", $clientCode)));
            if ($ResultUpdateData) {
                $data = array('isSelected' => 0);
                $resUpdate = $this->model->doEditWithField($data, 'clientprofile', 'clientCode', $clientCode);
            }

            $dataProfile = [
                "clientCode" => $dataValidate["clientCode"],
                "address" => $dataValidate["address"],
                "latitude" => $dataValidate["latitude"],
                "longitude" => $dataValidate["longitude"],
                "flat" => $dataValidate["flat"],
                "addressType" => $dataValidate["addressType"],
                "landMark" => $dataValidate["landMark"],
                "areaCode" => $dataValidate["area"],
                "cityCode" => $dataValidate["cityCode"],
                "isSelected" => 1,
                "isActive" => 1,
            ];
            $insertResult = $this->model->addWithoutCode($dataProfile, 'clientprofile');
            if ($insertResult) {
                $Result = $this->model->selectQuery(array('clientprofile.*'), 'clientprofile', array(), array('clientprofile.clientCode' => array("=", $clientCode), 'clientprofile.isSelected' => array("=", 1)));
                if ($Result) {
                    $data['addressdata'] = $Result[0];
                    return response()->json(["status" => 200, "message" => "Location Updated Successfully.", "result" => $data], 200);
                } else {
                    return response()->json(["status" => 300, "message" => "No data Found."], 200);
                }
            } else {
                return response()->json(["status" => 300, "message" => "Something Went Wrong."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "User not registered. Please register user."], 200);
        }
    } //end address line client

    public function getAddressesByclienCode(Request $r)
    {
        $addressList = [];
        $dataValidate = $r->validate([
            'clientCode' => 'required',
        ]);
        $clientCode = $dataValidate["clientCode"];
        $join["clientmaster"] = array("clientprofile.clientCode", "clientmaster.code");
        $joinType["clientmaster"] = "inner";
        $Result = $this->model->selectQuery(array('clientprofile.*', 'clientmaster.cityCode as clientCityCode'), 'clientprofile',  $join, array('clientprofile.clientCode' => array("=", $clientCode), 'clientprofile.isActive' => array("=", 1)), array(), array(), "", "", "", $joinType);
        if ($Result) {
            foreach ($Result as $row) {
                $cityCode = $row->clientCityCode;
                $areaCode = $row->areaCode;
                if (($cityCode != "" || $cityCode != null) && ($areaCode != "" || $areaCode != null)) {
                    $cityName = "";
                    $ResultCity = $this->model->selectDataByCode('citymaster', $cityCode);
                    if ($ResultCity) {
                        $cityName = $ResultCity->cityName;
                    }
                    $place = "";
                    $ResultArea = $this->model->selectDataByCode('customaddressmaster', $areaCode);
                    if ($ResultArea) {
                        $place = $ResultArea->place;
                    }
                    $data = array(
                        'city' => $cityName,
                        'area' => $place,
                        'id' => $row->id,
                        'address' => $row->address,
                        'latitude' => $row->latitude,
                        'longitude' => $row->longitude,
                        'cityCode' => $row->cityCode,
                        'area' => $row->areaCode,
                        'addressType' => $row->addressType == null ? "home" : $row->addressType,
                        'flat' => $row->flat,
                        'landMark' => $row->landMark,
                        'isSelected' => $row->isSelected,
                    );
                    array_push($addressList, $data);
                }
            }
            $result['addresses'] = $addressList;
            return response()->json(["status" => 200, "message" => "Addresses List.", "result" => $result], 200);
        } else {
            return response()->json(["status" => 300, "message" => "No data Found."], 200);
        }
    }

    public function updateClientAddress(Request $r)
    {
        $dataValidate = $r->validate([
            'id' => 'required',
            'clientCode' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'addressType' => 'required',
            'flat' => 'required',
            'landMark' => 'required',
            'area' => 'required',
            'cityCode' => 'required'
        ]);
        $clientCode = $dataValidate['clientCode'];
        $id = $dataValidate['id'];
        $Result = $this->model->selectQuery(array('clientmaster.*'), 'clientmaster', array(), array('clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            $dataProfile = [
                "clientCode" => $clientCode,
                "address" => $dataValidate['address'],
                "latitude" => $dataValidate['latitude'],
                "longitude" => $dataValidate['longitude'],
                "flat" => $dataValidate['flat'],
                "addressType" => $dataValidate['addressType'],
                "landMark" => $dataValidate['landMark'],
                "areaCode" => $dataValidate['area'],
                "cityCode" => $dataValidate['cityCode'],
            ];
            $updateResult = $this->model->doEditWithField($dataProfile, 'clientprofile', 'id', $id);
            if ($updateResult) {
                return response()->json(["status" => 200, "message" => "Address Updated Successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Something Went Wrong."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function deleteClientAddress(Request $r)
    {
        $dataValidate = $r->validate([
            'id' => 'required',
            'clientCode' => 'required',
        ]);
        $clientCode = $dataValidate['clientCode'];
        $id = $dataValidate['id'];
        $Result = $this->model->selectQuery(array('clientmaster.id'), 'clientmaster', array(), array('clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            $deleteResult = $this->model->updateOnDeleteRecordWithId($id, 'clientprofile');
            if ($deleteResult) {
                return response()->json(["status" => 200, "message" => "Address Deleted Successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Something Went Wrong."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function setDefaultAddress(Request $r)
    {
        $dataValidate = $r->validate([
            'addressId' => 'required',
            'clientCode' => 'required',
        ]);
        $data['isSelected'] = 0;
        $data['editIP'] = $_SERVER['REMOTE_ADDR'];
        $data['editID'] = $dataValidate['clientCode'];
        $result = $this->model->doEditWithField($data, 'clientprofile', 'clientCode', $dataValidate['clientCode']);

        $data1['isSelected'] = 1;
        $data1['editIP'] = $_SERVER['REMOTE_ADDR'];
        $data1['editID'] = $dataValidate['clientCode'];
        $result = $this->model->doEditWithField($data1, 'clientprofile', 'id', $dataValidate['addressId']);
        if ($result) {
            return response()->json(["status" => 200, "message" => "Address is set to default successfully"], 200);
        } else {
            return response()->json(["status" => 300, "message" => "Failed to set as default address"], 200);
        }
    }

    public function checkUserExists(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
        ]);
        $clientCode = $dataValidate['clientCode'];
        $Result = $this->model->selectQuery(array('clientmaster.id'), 'clientmaster', array(), array('clientmaster.isActive' => array('=', 1), 'clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            return response()->json(["status" => 200, "message" => "Active User"], 200);
        } else {
            return response()->json(["status" => 300, "message" => "Inactive User"], 200);
        }
    }

    public function getEntityCategoryList()
    {
        $condition = array('entitycategory.isActive' => array('=', 1));
        $orderBy = array('entitycategory.id' => 'DESC');
        $Records = $this->model->selectQuery(array("entitycategory.code", "entitycategory.entityCategoryName"), "entitycategory", array(), $condition, $orderBy);
        if ($Records) {
            $data = array();
            foreach ($Records as $re) {
                $data[] = array(
                    "code" => $re->code,
                    "entityCategoryName" => $re->entityCategoryName
                );
            }
            $response['entitycategory'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300, "message" => 'No Data Found'], 200);
        }
    }

    public function getFoodSliderImages()
    {
        $cond = array("foodslider.isActive" => array('=', 1));
        $orderBy = array("foodslider.id" => 'ASC');
        $Records = $this->model->selectQuery(array("foodslider.code", "foodslider.sliderPhoto", "foodslider.caption"), 'foodslider', array(), $cond, $orderBy);
        if ($Records) {
            $data = array();
            foreach ($Records as $r) {
                $path = "";
                if (file_exists('uploads/restaurant/sliderimage/' . $r->sliderPhoto)) {
                    $path = url('uploads/restaurant/sliderimage/' . $r->sliderPhoto);
                }
                $data[] = array("code" => $r->code, "sliderPhoto" => $path);
            }
            $response['foodSliderList'] = $data;
            return response()->json(["status" => 200,  "message" => 'Data Found', "result" => $response], 200);
        } else {
            $data['sliderImages'] = array();
            return response()->json(["status" => 300, "message" => "Data not found.", 'result' => $data], 200);
        }
    }

    public function getMenuCategoryList()
    {
        $tableName = "menucategory";
        $orderColumns = array("menucategory.id", "menucategory.code", "menucategory.menuCategoryName", "menucategory.isActive", "menucategory.priority");
        $condition = array('menucategory.isActive' => array("=", 1));
        $orderBy = array('menucategory.id' => 'ASC');
        $Records = $this->model->selectQuery($orderColumns, "menucategory", array(), $condition, $orderBy);
        if ($Records) {
            $data = array();
            foreach ($Records as $r) {
                $data[] = $r;
            }
            $response['menuCategory'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300, "message" => 'No Data Found'], 200);
        }
    }

    public function getMenuSubCategoryList()
    {
        $tableName = "menusubcategory";
        $orderColumns = array("menusubcategory.id", "menusubcategory.code", "menusubcategory.menuCategoryCode", "menucategory.menuCategoryName", "menusubcategory.menuSubCategoryName", "menusubcategory.isActive");
        $condition = array('menusubcategory.isActive' => array("=", 1));
        $orderBy = array('menusubcategory.id' => 'ASC');
        $join = array('menucategory' => array('menucategory.code', 'menusubcategory.menuCategoryCode'));
        $Records = $this->model->selectQuery($orderColumns, "menusubcategory", $join, $condition, $orderBy);
        if ($Records) {
            $data = array();
            foreach ($Records as $r) {
                $data[] = $r;
            }
            $response['menuSubCategory'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300, "message" => 'No Data Found'], 200);
        }
    }

    public function getCuisinesList(Request $req)
    {
        $dataValidate = $req->validate([
            'offset' => 'required',
        ]);
        //DB::enableQueryLog();
        $condition = array('cuisinemaster.isActive' => array("=", 1));
        $limit = 8;
        $offset = 0;
        if (isset($req->offset)) {
            if ($req->offset > 0) $offset = $req->offset;
        }
        $orderBy = array('cuisinemaster.id' => 'ASC');
        $Records = $this->model->selectQuery(array("cuisinemaster.*"), "cuisinemaster", array(), $condition, $orderBy, array(), $limit, $offset);

        //$query_1 = DB::getQueryLog();
        //print_r($query_1);
        if ($Records) {
            $data = array();
            foreach ($Records as $r) {
                $path = "";
                if ($r->cuisinePhoto != "") {
                    $path = url('uploads/restaurant/cuisine/' . $r->cuisinePhoto);
                }
                $data[] = array("code" => $r->code, "cuisineName" => $r->cuisineName, "cuisinePhoto" => $path);
            }
            $response['cuisinesList'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300, "message" => 'No Data Found'], 200);
        }
    }

    public function ratingData($resCode, $level)
    {
        $result = DB::table("rating")->select(DB::raw('IFNULL(sum(rating),0) as rating'))->where('rating', $level)->where('restaurantCode', $resCode)->where('isAccept', 1)->first();
        return [$result->rating];
    }

    public function getRatings(string $id)
    {
        $star_5 = $this->ratingData($id, 5);
        $star_4 = $this->ratingData($id, 4);
        $star_3 = $this->ratingData($id, 3);
        $star_2 = $this->ratingData($id, 2);
        $star_1 = $this->ratingData($id, 1);

        $counts = DB::table("rating")->select("*")->where('restaurantCode', $id)->where('isAccept', 1)->count();

        $sumratings = 0;
        if (count($star_5) > 0) {
            $sumratings += $star_5[0];
        }
        if (count($star_4) > 0) {
            $sumratings += $star_4[0];
        }
        if (count($star_3) > 0) {
            $sumratings += $star_3[0];
        }
        if (count($star_2) > 0) {
            $sumratings += $star_2[0];
        }
        if (count($star_1) > 0) {
            $sumratings += $star_1[0];
        }
        if ($sumratings > 0 && $counts > 0) {
            $avgRating = number_format(($sumratings / $counts), 1, ".", "");
            return $avgRating;
        }
        return 0;
    }


    public function getRestaurantList(Request $req)
    {
        DB::enableQueryLog();
        $currentDate = date('Y-m-d');
        $cond = "restaurant.isActive=1";
        $dataValidate = $req->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'offset' => 'required'
        ]);

        if (isset($req->cuisineCode) && $req->cuisineCode != "") {
            $split_cuisine = explode(',', $req->cuisineCode);
            if (!empty($split_cuisine)) {
                $values  = "";
                foreach ($split_cuisine as $cui) {
                    $values != "" && $values .= ",";
                    $values .= "'" . $cui . "'";
                }
                if ($cond != "") $cond .= " and restaurantcuisinelineentries.cuisineCode in (" . $values . ") ";
                else $cond .= " restaurantcuisinelineentries.cuisineCode in (" . $values . ") ";
            }
        }

        // 		if ($cond != "") $cond .= " and restaurant.cityCode ='" . $req->cityCode ."'";
        // 		else $cond .= " restaurant.cityCode ='" . $req->cityCode ."'";
        // 		if (isset($req->entitycategoryCode)) {
        // 			if ($req->entitycategoryCode != "") {
        // 				if ($cond != "") $cond .= " and restaurant.entitycategoryCode='" . $req->entitycategoryCode . "' ";
        // 				else $cond .= " restaurant.entitycategoryCode='" . $req->entitycategoryCode . "' ";
        // 			}
        // 		}

        if (isset($req->entityName)) {
            if ($req->entityName != "") {
                if ($cond != "") $cond .= " and restaurant.entityName like '%" . $req->entityName . "%' ";
                else $cond .= " restaurant.entityName like '%" . $req->entityName . "%' ";
            }
        }

        $geoLocation = "";
        $having = "";
        $geoLocation = ",ROUND( 6371 * acos( cos( radians(" . $req->latitude . ") ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians(" . $req->longitude . ") ) + sin( radians(" . $req->latitude . ") ) * sin(radians(restaurant.latitude)) ) ) AS distance";
        $having = " HAVING distance <= 15";

        $columns = "select restaurant.*,entitycategory.entityCategoryName,citymaster.cityName,customaddressmaster.place" . $geoLocation . ",restaurantoffer.code as offerCode,restaurantoffer.restaurantCode as restaurantCode,restaurantoffer.couponCode,restaurantoffer.offerType,ifnull(GREATEST(ifnull(MAX(restaurantoffer.discount),0),ifnull(MAX(restaurantoffer.flatAmount),0)),'') as discount,restaurantoffer.minimumAmount,restaurantoffer.perUserLimit,restaurantoffer.startDate,restaurantoffer.endDate,restaurantoffer.capLimit,restaurantoffer.termsAndConditions,restaurantoffer.isAdminApproved as vAdminapproved,restaurantoffer.isActive as status from restaurant ";
        $orderBy = " order by restaurant.isServiceable DESC , restaurant.id ASC";
        $join = " INNER JOIN entitycategory ON restaurant.entitycategoryCode = entitycategory.`code` LEFT JOIN restaurantcuisinelineentries ON restaurant.`code` = restaurantcuisinelineentries.vendorCode ";
        $join .= " inner JOIN citymaster ON citymaster.`code`=restaurant.cityCode  LEFT JOIN customaddressmaster ON restaurant.`addressCode` = customaddressmaster.code ";
        $join .= " LEFT JOIN restaurantoffer ON restaurant.`code` = restaurantoffer.restaurantCode ";
        $groupBy = " Group by restaurant.code ";
        $limit = 3;
        $offset = "";
        if (isset($req->offset)) {
            if ($req->offset > 0) $limit_offset = " limit " . $req->offset . ',' . $limit;
            else $limit_offset = " limit " . $limit;
        } else {
            $limit_offset = " limit " . $limit;
        }
        if ($cond != "") $whereCondition = " where " . $cond;
        else $whereCondition = "";
        $query = $columns . $join . $whereCondition . $groupBy . $having . $orderBy . $limit_offset;
        $result = DB::select($query);
        $totalRestaurantCount = 0;
        $countQuery = DB::select('select count(restaurant.id) ' . $geoLocation . ' from restaurant ' . $join . $whereCondition . $groupBy . $having . $orderBy);
        if (!empty($result)) {
            $data = array();
            $totalRestaurantCount = count($countQuery);
            foreach ($result as $r) {

                $cuisinesList = "";
                $cuisineRecords = DB::table("cuisinemaster")
                    ->select(DB::raw("ifnull(GROUP_CONCAT(cuisinemaster.cuisineName SEPARATOR ','),'') as `cuisines`"))
                    ->join('restaurantcuisinelineentries', 'restaurantcuisinelineentries.cuisineCode', 'cuisinemaster.code')
                    ->where('restaurantcuisinelineentries.vendorCode', $r->code)
                    ->where('cuisinemaster.isActive', 1)
                    ->get();
                if ($cuisineRecords && count($cuisineRecords) > 0) {
                    foreach ($cuisineRecords as $cu)
                        $cuisinesList = $cu->cuisines;
                }
                $vendorar['code'] = $r->code;
                $vendorar['entityName'] = $r->entityName;
                /*$vendorar['firstName'] = $r->firstName;
				$vendorar['middleName'] = $r->middleName;
				$vendorar['lastName'] = $r->lastName;
				$vendorar['latitude'] = $r->latitude;
				$vendorar['longitude'] = $r->longitude;*/
                $vendorar['isServiceable'] = $r->isServiceable;
                if ($r->entityImage != "") {
                    $path = 'uploads/restaurant/restaurantimage/' . $r->entityImage;
                    if (file_exists($path)) $vendorar['entityImage'] = url($path);
                    else $vendorar['entityImage']  = $path = url('uploads/restaurent_no_image.png');
                } else $vendorar['entityImage']  = url('uploads/restaurent_no_image.png');

                $vendorar['address'] = $r->address;
                /*$vendorar['packagingType'] = $r->packagingType;
				$vendorar['cartPackagingPrice'] = $r->cartPackagingPrice;
				$vendorar['gstApplicable'] = $r->gstApplicable;
				$vendorar['gstPercent'] = $r->gstPercent;*/
                $vendorar['ownerContact'] = $r->ownerContact;
                //$vendorar['entityContact'] = $r->entityContact;
                $vendorar['email'] = $r->email;
                $vendorar['entityCategoryName'] = $r->entityCategoryName;
                $vendorar['fssaiNumber'] = $r->fssaiNumber;
                $vendorar['cityName'] = $r->cityName;
                $vendorar['aresName'] = $r->place;
                $vendorar['cuisinesList'] = $cuisinesList;
                /*$discount =array();
                if ($r->discount != 0 && $r->vAdminapproved == 1 && $r->startDate <= $currentDate && $r->endDate >= $currentDate && $r->status== 1) {
                       $discount['offerCode'] = $r->offerCode;
                       $discount['vendorCode'] = $r->restaurantCode;
                       $discount['coupanCode'] = $r->couponCode;
					   $discount['offerType'] = $r->offerType;
					   $sign='';
						if($r->offerType==='flat'){
							$sign=' ₹';
						}else{
							$sign=' %';
						}
						$discount['discount'] = $r->discount.$sign;
                       $discount['minimumAmount'] = $r->minimumAmount;
                       $discount['perUserLimit'] = $r->perUserLimit;
                       $discount['startDate'] = date('d-m-Y h:i A', strtotime($r->startDate));
                       $discount['endDate'] = date('d-m-Y h:i A', strtotime($r->endDate));
                       $discount['capLimit'] = $r->capLimit;
                       $discount['termsAndConditions'] = $r->termsAndConditions;
                }
				$vendorar['discount'] = $discount;*/
                /*$dayofweek= strtolower(date('l'));
				
				$timeQuery = "select restauranthours.code as hourCode,time_format(restauranthours.fromTime,'%h:%i %p') as fromTime,time_format(restauranthours.toTime,'%h:%i %p') as toTime from restauranthours where restauranthours.restaurantCode = '".$r->code."' and restauranthours.weekDay='".$dayofweek."'";
				$timeData = DB::select($timeQuery);	
				if($timeData) {
				    $vendorar['restaurantHours'] = $timeData;
				} else {
				    $vendorar['restaurantHours'] = array();
				}
				 $vendorar['ratingDetails']=[];*/
                $sum = 0;
                $count = 0;
                $sum1 = 0;
                $avgRating = 0;
                /*
                $ratingfinalArray = [];
                $orderColumns1 = array("rating.id", "clientmaster.name", "rating.clientCode", "rating.orderCode", "rating.restaurantCode", "rating.rating", "rating.review", "rating.addDate", "rating.isAccept");
                $condition1 = array("rating.restaurantCode" => array("=", $r->code), "rating.isAccept" => array("=", 1));
                $join1 = array("clientmaster" => array("clientmaster.code", "rating.clientCode"));
                $extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                $ratingDetails = $this->model->selectQuery($orderColumns1, 'rating', $join1, $condition1, array(), array(), "", "", $extraCondition1);
                if (!empty($ratingDetails) && count($ratingDetails) > 0) {
                    foreach ($ratingDetails as $rating) {
                        $sum = $sum + $rating->rating;
                        $count++;
                        /*$dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $rating->addDate);
						$ratingDate = $dFormat->format('d-m-Y H:i:s');
						$ratingArr['id'] = $rating->id;
						$ratingArr['orderCode'] =  $rating->orderCode;
						$ratingArr['clientCode'] =  $rating->clientCode;
						$ratingArr['clientName'] =  $rating->name;
						$ratingArr['rating'] =$rating->rating;
						$ratingArr['review'] = $rating->review;
						$ratingArr['date'] = $ratingDate;
						$ratingOrderArray[]=$ratingArr;
                    }
                    $orderColumns2 = array(DB::raw("ifnull(sum(rating.rating),0) as rating"), DB::raw("ifnull(count(id),0) as count"));
                    $condition2 = array("rating.restaurantCode" => array("=", $r->code), "rating.isAccept" => array("=", 1));
                    $extraCondition2 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                    $groupBy2 = array("rating.rating");
                    $rating1 = $this->model->selectQuery($orderColumns2, 'rating', array(), $condition2, array(), array(), "", "", $extraCondition1, array(), $groupBy2);
                    if (!empty($rating1) && count($rating1) > 0) {
                        foreach ($rating1 as $ra) {
                            $sum1 = $sum1 + ($ra->rating * $ra->count);
                        }
                    }
                    //round($sum1/$sum,1);
                    $avgRating = round($sum1 / $sum, 1);
                    ///$ratingfinalArray['ratingOrderArray'] = $ratingOrderArray;
                }
                $vendorar['avgRating'] = strval($avgRating);
                */
                $vendorar['avgRating'] = $this->getRatings($r->code);
                $vendorar['distanceDetails'] = (object)array();
                if ($req->latitude != '' && $req->longitude != '' && $r->latitude != '' && $r->longitude != '') {
                    $vendorar['distanceDetails'] = $this->restaurantDistanceFromCustomer($req->latitude, $req->longitude, $r->latitude, $r->longitude);
                }
                $data[] = $vendorar;
            }
            $response['restaurants'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "totalRestaurantCount" => $totalRestaurantCount, "result" => $response], 200);
        } else {
            $response = (object)[];
            return response()->json(["status" => 300,  "message" => 'No Data Found', "result" => $response], 200);
        }
    }

    public function restaurantDistanceFromCustomer($custLat, $custLong, $restLat, $restLong)
    {
        $placeApiKey = Config::get('constants.PLACE_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/directions/json?destination=$restLat,$restLong&mode=driving&origin=$custLat,$custLong&key=" . $placeApiKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $arrayDist = $arrayTime = array();
        $minDistance = 0;
        $minTime = 0;
        $response_all = json_decode($response, TRUE);
        if (!empty($response_all['routes'])) {
            foreach ($response_all['routes'] as $res1 => $val) {
                foreach ($val['legs'] as $keys => $value) {
                    $distance = round($value['distance']['value'] / 1000);
                    array_push($arrayTime, $value['duration']['text']);
                    array_push($arrayDist, $distance);
                }
            }
            $minDistance = min($arrayDist);
            $minTime = min($arrayTime);
        }
        $res['minDistance'] = $minDistance . ' KM';
        $res['time'] = $minTime;
        return $res;
    }
    public function getMenuItemList(Request $r)
    {
        $dataValidate = $r->validate([
            'restaurantCode' => 'required',
            'offset' => 'required'
        ]);
        $keyword = $r->keyword;
        $cuisineType = $r->cuisineType;
        $offset = $r->offset;
        $restaurantCode = $dataValidate['restaurantCode'];
        $condition1 = array('menucategory.isActive' => array("=", 1));
        $orderBy1 = array('menucategory.priority' => 'ASC');
        $Records = $this->model->selectQuery(array("menucategory.*"), "menucategory", array(), $condition1, $orderBy1);
        if (!empty($Records)) {
            $data = array();
            foreach ($Records as $ra) {
                $mainitemArray = array();


                $maincount = 0;
                $catCode = $ra->code;
                $catName = $ra->menuCategoryName;
                $orderColumns2 = array("restaurantitemmaster.*", "tagmaster.tagTitle", "tagmaster.tagColor", "restaurant.entityName", "restaurant.isServiceable as vendorIsServiceable");
                $condition2 = array('restaurantitemmaster.isActive' => array("=", 1), 'restaurantitemmaster.cuisineType' => array("=", $cuisineType),  "restaurantitemmaster.restaurantCode" => array("=", $restaurantCode), "restaurantitemmaster.menuCategoryCode" => array("=", $catCode), "restaurantitemmaster.isAdminApproved" => array("=", 1));
                $orderBy2 = array('restaurantitemmaster.id' => 'DESC');
                $join2 = array("restaurant" => array("restaurantitemmaster.restaurantCode", "restaurant.code"), 'tagmaster' => array('tagmaster.code', 'restaurantitemmaster.tagCode'));
                $jointType2 = array('restaurant' => 'inner', 'tagmaster' => 'left');
                $like2 = array("restaurantitemmaster.itemName" => $keyword);
                $extraCondition2 = " (restaurantitemmaster.menuSubCategoryCode='' or restaurantitemmaster.menuSubCategoryCode IS NULL)";
                $itemRecords = $this->model->selectQuery($orderColumns2, "restaurantitemmaster", $join2, $condition2, $orderBy2, $like2, 10, $offset, $extraCondition2, $jointType2);
                if (!empty($itemRecords)) {
                    foreach ($itemRecords as $r) {
                        $addonArray = array();
                        $choiceArray = array();
                        $vendorItemCode = $r->code;
                        $CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'addon')));
                        if (!empty($CCRecordsAddon)) {
                            foreach ($CCRecordsAddon as $ccra) {
                                $customizedCategoryCode = $ccra->code;
                                $categoryTitle = $ccra->categoryTitle;
                                $CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                                $addonCustomizedCategoryArray = array();
                                if (!empty($CCRecordsAddonLine)) {
                                    foreach ($CCRecordsAddonLine as $ccraL) {
                                        //	$addonCustomizedCategoryArray = array();
                                        $subCategoryTitle = $ccraL->subCategoryTitle;
                                        $price = $ccraL->price;
                                        $addonCustomizedCategoryArray[] = array(
                                            "lineCode" => $ccraL->code,
                                            "subCategoryTitle" => $subCategoryTitle,
                                            "price" => $price,
                                        );
                                    }
                                }
                                if (count($addonCustomizedCategoryArray) > 0) {
                                    $addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
                                }
                            }
                        }

                        $CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'choice')));
                        if (!empty($CCRecordsChoice)) {
                            foreach ($CCRecordsChoice as $ccrc) {
                                $customizedCategoryCode = $ccrc->code;
                                $categoryTitle = $ccrc->categoryTitle;
                                $CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                                $choiceCustomizedCategoryArray = array();
                                if (!empty($CCRecordsChoiceLine)) {
                                    foreach ($CCRecordsChoiceLine as $ccrcL) {
                                        $subCategoryTitle = $ccrcL->subCategoryTitle;
                                        $price = $ccrcL->price;
                                        $choiceCustomizedCategoryArray[] = array(
                                            "lineCode" => $ccrcL->code,
                                            "subCategoryTitle" => $subCategoryTitle,
                                            "price" => $price,
                                        );
                                    }
                                }
                                if (count($choiceCustomizedCategoryArray) > 0) {
                                    $choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
                                }
                            }
                        }

                        $path = url('uploads/restaurent_no_image.png');
                        if ($r->itemPhoto != "") {
                            $filepath = 'uploads/restaurant/restaurantitemimage/' . $r->restaurantCode . '/' . $r->itemPhoto;
                            if (file_exists($filepath))  $path =  url($filepath);
                        }
                        $mainitemArray[] = array(
                            "restaurantCode" => $r->restaurantCode,
                            "itemCode" => $r->code,
                            "itemName" => $r->itemName,
                            "itemDescription" => $r->itemDescription,
                            "salePrice" => $r->salePrice,
                            "itemPhoto" => $path,
                            "restaurantName" => $r->entityName,
                            "vendorIsServiceable" => $r->vendorIsServiceable,
                            "itemTag" => $r->tagTitle,
                            "itemTagColor" => $r->tagColor,
                            "isServiceable" => $r->itemActiveStatus,
                            "cuisineType" => $r->cuisineType,
                            "isActive" => $r->isActive,
                            "maxOrderQty" => $r->maxOrderQty,
                            "itemPackagingPrice" => $r->itemPackagingPrice,
                            "addons" => $addonArray,
                            "choice" => $choiceArray,
                        );
                        $maincount++;
                    }
                }

                $subCategoryItemArray = array();

                $tableName3 = "menusubcategory";
                $orderColumns3 = array("menusubcategory.*");
                $condition3 = array('menusubcategory.isActive' => array("=", 1), "menusubcategory.menuCategoryCode" => array("=", $catCode));
                $orderBy3 = array('menusubcategory.id' => 'ASC');
                $subCateRecords = $this->model->selectQuery($orderColumns3, "menusubcategory", array(), $condition3, $orderBy3);
                if (!empty($subCateRecords)) {
                    $subcount    = sizeof($subCateRecords);
                    foreach ($subCateRecords as $subrow) {
                        $subCategoryCode = $subrow->code;
                        $subCategoryName = $subrow->menuSubCategoryName;

                        $tableName4 = "restaurantitemmaster";
                        $orderColumns4 = array("restaurantitemmaster.*", "restaurant.entityName", "restaurant.isServiceable as vendorIsServiceable");
                        $condition4 = array('restaurantitemmaster.isActive' => array("=", 1), 'restaurantitemmaster.cuisineType' => array("=", $cuisineType), "restaurantitemmaster.restaurantCode" => array("=", $restaurantCode), "restaurantitemmaster.menuSubCategoryCode" => array("=", $subCategoryCode), "restaurantitemmaster.isAdminApproved" => array("=", 1));
                        $orderBy4 = array('restaurantitemmaster.id' => 'DESC');
                        $join4 = array("restaurant" => array("restaurantitemmaster.restaurantCode", "restaurant.code"), "menusubcategory" => array("restaurantitemmaster.menuSubCategoryCode", "menusubcategory.code"));
                        $like4 = array("restaurantitemmaster.itemName" => $keyword);
                        $Records = $this->model->selectQuery($orderColumns4, $tableName4, $join4, $condition4, $orderBy4, $like4, 10, $offset);
                        if (!empty($Records)) {
                            $itemArray = array();
                            $count = sizeof($Records);
                            foreach ($Records as $r) {
                                $addonArray = $choiceArray = [];
                                $vendorItemCode = $r->code;
                                $CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'addon')));
                                if (!empty($CCRecordsAddon)) {
                                    foreach ($CCRecordsAddon as $ccra) {
                                        $customizedCategoryCode = $ccra->code;
                                        $categoryTitle = $ccra->categoryTitle;
                                        $CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                                        $addonCustomizedCategoryArray = array();
                                        if (!empty($CCRecordsAddonLine)) {
                                            foreach ($CCRecordsAddonLine as $ccraL) {
                                                $subCategoryTitle = $ccraL->subCategoryTitle;
                                                $price = $ccraL->price;
                                                $addonCustomizedCategoryArray[] = array(
                                                    "lineCode" => $ccraL->code,
                                                    "subCategoryTitle" => $subCategoryTitle,
                                                    "price" => $price,
                                                );
                                            }
                                        }
                                        if (count($addonCustomizedCategoryArray) > 0) {
                                            $addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
                                        }
                                    }
                                }

                                $CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantitemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'choice')));
                                if (!empty($CCRecordsChoice)) {
                                    foreach ($CCRecordsChoice as $ccrc) {
                                        $customizedCategoryCode = $ccrc->code;
                                        $categoryTitle = $ccrc->categoryTitle;
                                        $CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                                        $choiceCustomizedCategoryArray = array();
                                        if (!empty($CCRecordsChoiceLine)) {
                                            foreach ($CCRecordsChoiceLine as $ccrcL) {
                                                $subCategoryTitle = $ccrcL->subCategoryTitle;
                                                $price = $ccrcL->price;
                                                $choiceCustomizedCategoryArray[] = array(
                                                    "lineCode" => $ccrcL->code,
                                                    "subCategoryTitle" => $subCategoryTitle,
                                                    "price" => $price,
                                                );
                                            }
                                        }
                                        if (count($choiceCustomizedCategoryArray) > 0) {
                                            $choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
                                        }
                                    }
                                }
                                $path = url('uploads/restaurent_no_image.png');
                                if ($r->itemPhoto != "") {
                                    $filepath = 'uploads/restaurant/restaurantitemimage/' . $r->restaurantCode . '/' . $r->itemPhoto;
                                    if (file_exists($filepath))  $path =  url($filepath);
                                }
                                $itemArray[] = array(
                                    "restaurantCode" => $r->restaurantCode,
                                    "itemCode" => $r->code,
                                    "itemName" => $r->itemName,
                                    "itemDescription" => $r->itemDescription,
                                    "salePrice" => $r->salePrice,
                                    "itemPhoto" => $path,
                                    "restaurantName" => $r->entityName,
                                    "vendorIsServiceable" => $r->vendorIsServiceable,
                                    "cuisineType" => $r->cuisineType,
                                    "isActive" => $r->isActive,
                                    "isServiceable" => $r->itemActiveStatus,
                                    "maxOrderQty" => $r->maxOrderQty,
                                    "itemPackagingPrice" => $r->itemPackagingPrice,
                                    "addons" => $addonArray,
                                    "choice" => $choiceArray,
                                );
                                $maincount++;
                            }
                            if (!empty($itemArray)) {
                                $subCategoryItemArray[] = array("subCategoryCode" => $subCategoryCode, "subCategoryName" => $subCategoryName, "count" => $count, "itemList" => $itemArray);
                            }
                        }
                    }
                }
                if ($maincount > 0) {
                    $data[] = array("menuCategoryCode" => $catCode, "count" => $maincount, "menuCategoryName" => $catName, "itemList" => $mainitemArray, "subCategoryList" => $subCategoryItemArray);
                }
            }
            $response['menuItemList'] = $data;
            return response()->json(["status" => 200,  "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300,  "message" => 'No Data Found'], 200);
        }
    }

    public function getRestaurantByCode(Request $r)
    {
        $dataValidate = $r->validate([
            'restaurantCode' => 'required'
        ]);
        $restaurantCode = $dataValidate['restaurantCode'];
        $checkData = $this->model->selectQuery(array('restaurant.code', 'restaurant.entityName', 'restaurant.isServiceable', 'restaurant.ownerContact', 'restaurant.email', 'restaurant.entityImage', 'restaurant.addressCode', 'restaurant.fssaiNumber', 'entitycategory.entityCategoryName', 'citymaster.cityName', 'restaurant.latitude', 'restaurant.longitude'), 'restaurant', array('entitycategory' => array('entitycategory.code', 'restaurant.entitycategoryCode'), 'citymaster' => array('citymaster.code', 'restaurant.cityCode')), array('restaurant.code' => array('=', $restaurantCode)));
        if (!empty($checkData)) {
            foreach ($checkData as $chk) {
                $path = url('uploads/restaurent_no_image.png');
                $entityImage = $chk->entityImage;
                $addressCode = $chk->addressCode;
                $place = "";
                $checkPlace = $this->model->selectQuery(array('customaddressmaster.place'), 'customaddressmaster', array(), array('customaddressmaster.code' => array('=', $addressCode)));
                if (!empty($checkPlace)) {
                    foreach ($checkPlace as $chkPlace) {
                        $place = $chkPlace->place;
                    }
                }
                $path = url('uploads/restaurent_no_image.png');
                if ($entityImage != "") {
                    $path = url('uploads/restaurant/restaurantimage/' . $entityImage);
                }
                $cuisinesList = "";
                $cuisineRecords = DB::table("cuisinemaster")
                    ->select(DB::raw("ifnull(GROUP_CONCAT(cuisinemaster.cuisineName SEPARATOR ','),'') as `cuisines`"))
                    ->join('restaurantcuisinelineentries', 'restaurantcuisinelineentries.cuisineCode', 'cuisinemaster.code')
                    ->where('restaurantcuisinelineentries.vendorCode', $restaurantCode)
                    ->where('cuisinemaster.isActive', 1)
                    ->get();
                if ($cuisineRecords && count($cuisineRecords) > 0) {
                    foreach ($cuisineRecords as $cu)
                        $cuisinesList = $cu->cuisines;
                }
                $response = $chk;
                $response->code = $chk->code;
                $response->isServiceable = $chk->isServiceable;
                $response->entityName = $chk->entityName;
                $response->address = $r->address;
                $response->ownerContact = $chk->ownerContact;
                $response->email = $chk->email;
                $response->entityCategoryName = $chk->entityCategoryName;
                $response->fssaiNumber = $chk->fssaiNumber;
                $response->cityName = $chk->cityName;
                $response->entityImage = $path;
                $response->address = $place;
                $response->cuisinesList = $cuisinesList;
                $dayofweek = strtolower(date('l'));
                $timeQuery = "select restauranthours.code as hourCode,time_format(restauranthours.fromTime,'%h:%i %p') as fromTime,time_format(restauranthours.toTime,'%h:%i %p') as toTime from restauranthours where restauranthours.restaurantCode = '" . $restaurantCode . "' and restauranthours.weekDay='" . $dayofweek . "' order by fromTime asc";
                $timeData = DB::select($timeQuery);
                if ($timeData && count($timeData) > 0) {
                    $response->vendorHours = $timeData;
                } else {
                    $response->vendorHours = array();
                }
                $sum = 0;
                $count = 0;
                $sum1 = 0;
                $avgRating = 0;
                /*
                $ratingfinalArray = [];
                $orderColumns1 = array("rating.id", "clientmaster.name", "rating.clientCode", "rating.orderCode", "rating.restaurantCode", "rating.rating", "rating.review", "rating.addDate", "rating.isAccept");
                $condition1 = array("rating.restaurantCode" => array("=", $restaurantCode), "rating.isAccept" => array("=", 1));
                $join1 = array("clientmaster" => array("clientmaster.code", "rating.clientCode"));
                $extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                $ratingDetails = $this->model->selectQuery($orderColumns1, 'rating', $join1, $condition1, array(), array(), "", "", $extraCondition1);
                if (!empty($ratingDetails) && count($ratingDetails) > 0) {
                    foreach ($ratingDetails as $rating) {
                        $sum = $sum + $rating->rating;
                        $count++;
                        /*$dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $rating->addDate);
						$ratingDate = $dFormat->format('d-m-Y H:i:s');
						$ratingArr['id'] = $rating->id;
						$ratingArr['orderCode'] =  $rating->orderCode;
						$ratingArr['clientCode'] =  $rating->clientCode;
						$ratingArr['clientName'] =  $rating->name;
						$ratingArr['rating'] =$rating->rating;
						$ratingArr['review'] = $rating->review;
						$ratingArr['date'] = $ratingDate;
						$ratingOrderArray[]=$ratingArr;
                    }
                    $orderColumns2 = array(DB::raw("ifnull(sum(rating.rating),0) as rating"), DB::raw("ifnull(count(id),0) as count"));
                    $condition2 = array("rating.restaurantCode" => array("=", $restaurantCode), "rating.isAccept" => array("=", 1));
                    $extraCondition2 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                    $groupBy2 = array("rating.rating");
                    $rating1 = $this->model->selectQuery($orderColumns2, 'rating', array(), $condition2, array(), array(), "", "", $extraCondition1, array(), $groupBy2);
                    if (!empty($rating1) && count($rating1) > 0) {
                        foreach ($rating1 as $ra) {
                            $sum1 = $sum1 + ($ra->rating * $ra->count);
                        }
                    }
                    //round($sum1/$sum,1);
                    $avgRating = round($sum1 / $sum, 1);
                    ///$ratingfinalArray['ratingOrderArray'] = $ratingOrderArray;
                }
                $response->avgRating = strval($avgRating);
                */
                $response->avgRating = $this->getRatings($restaurantCode);
                $discount = array();
                $currentDate = date('Y-m-d');
                $maxDiscountQuery = "Select restaurantoffer.code as offerCode,restaurantoffer.restaurantCode as restaurantCode,restaurantoffer.couponCode,restaurantoffer.offerType,ifnull(GREATEST(ifnull(MAX(restaurantoffer.discount),0),ifnull(MAX(restaurantoffer.flatAmount),0)),'') as discount,restaurantoffer.minimumAmount,restaurantoffer.perUserLimit,restaurantoffer.startDate,restaurantoffer.endDate,restaurantoffer.capLimit,restaurantoffer.termsAndConditions,restaurantoffer.isAdminApproved as vAdminapproved,restaurantoffer.isActive from restaurantoffer where restaurantCode= '" . $restaurantCode . "'";
                $maxDiscount = DB::select($maxDiscountQuery);
                if ($maxDiscount) {
                    foreach ($maxDiscount as $max) {
                        $discount = array();
                        if ($max->discount != 0 && $max->vAdminapproved == 1 && $max->startDate <= $currentDate && $max->endDate >= $currentDate && $max->isActive == 1) {
                            $discount['offerCode'] = $r->offerCode;
                            $discount['vendorCode'] = $r->restaurantCode;
                            $discount['coupanCode'] = $r->couponCode;
                            $discount['offerType'] = $r->offerType;
                            $sign = '';
                            if ($r->offerType === 'flat') {
                                $sign = ' ₹';
                            } else {
                                $sign = ' %';
                            }
                            $discount['discount'] = $r->discount . $sign;
                            $discount['minimumAmount'] = $r->minimumAmount;
                            $discount['perUserLimit'] = $r->perUserLimit;
                            $discount['startDate'] = date('d-m-Y h:i A', strtotime($r->startDate));
                            $discount['endDate'] = date('d-m-Y h:i A', strtotime($r->endDate));
                            $discount['capLimit'] = $r->capLimit;
                            $discount['termsAndConditions'] = $r->termsAndConditions;
                        }
                    }
                }
                $response->discount = $discount;
                $response->distanceDetails = (object)array();
                if ($r->latitude != '' && $r->longitude != '' && $chk->latitude != '' && $chk->longitude != '') {
                    $response->distanceDetails = $this->restaurantDistanceFromCustomer($r->latitude, $r->longitude, $chk->latitude, $chk->longitude);
                }
                return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "No Data Found."], 200);
        }
    }


    public function getCouponList(Request $r)
    {
        $dataValidate = $r->validate([
            'restaurantCode' => 'required',
        ]);
        $today = date('Y-m-d');
        DB::enableQueryLog();
        //$couponQuery= "Select restaurantoffer.code as offerCode,restaurantoffer.restaurantCode as restaurantCode,restaurantoffer.couponCode,restaurantoffer.offerType,ifnull(GREATEST(ifnull(MAX(restaurantoffer.discount),null),ifnull(MAX(restaurantoffer.flatAmount),null)),null) as discount,restaurantoffer.minimumAmount,restaurantoffer.perUserLimit,restaurantoffer.startDate,restaurantoffer.endDate,restaurantoffer.capLimit,restaurantoffer.termsAndConditions,restaurantoffer.isAdminApproved as vAdminapproved,restaurantoffer.isActive from restaurantoffer where restaurantCode= '".$r->restaurantCode."' and ( '" . $today . "' between restaurantoffer.startDate and restaurantoffer.endDate) and restaurantoffer.isAdminApproved=1";
        $couponQuery = "Select restaurantoffer.code as offerCode,restaurantoffer.restaurantCode as restaurantCode,restaurantoffer.couponCode,restaurantoffer.offerType,restaurantoffer.discount,restaurantoffer.flatAmount,restaurantoffer.minimumAmount,restaurantoffer.perUserLimit,restaurantoffer.startDate,restaurantoffer.endDate,restaurantoffer.capLimit,restaurantoffer.termsAndConditions,restaurantoffer.isAdminApproved as vAdminapproved,restaurantoffer.isActive from restaurantoffer where restaurantCode= '" . $r->restaurantCode . "' and ( '" . $today . "' between restaurantoffer.startDate and restaurantoffer.endDate) and restaurantoffer.isAdminApproved=1";
        $Result = DB::select($couponQuery);
        //$query_1 = DB::getQueryLog();
        //print_r($query_1);
        if (!empty($Result)) {
            $recordsCount = count($Result);
            $data = array();
            foreach ($Result as $r) {
                $data[] = $r;
            }
            $res['offersList'] = $data;
            return response()->json(["status" => 200, "totalRecords" => $recordsCount, "message" => "Data found", "result" => $res], 200);
        } else {
            return response()->json(["status" => 300, "msg" => "No Data found"], 200);
        }
    }

    public function getCoupanDetails(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'couponCode' => 'required',
            'restaurantCode' => 'required',
            'itemDetails' => 'required',
        ]);
        $couponCode = $dataValidate['couponCode'];
        $restaurantCode = $dataValidate['restaurantCode'];
        $cartAmount = 0;
        $clientCode = $dataValidate['clientCode'];
        $today = date('Y-m-d');
        $discountAmount = $offerCodeExcludedAmount = 0;
        $couponQuery = "Select restaurantoffer.* from restaurantoffer where restaurantCode= '" . $restaurantCode . "' and couponCode='" . $couponCode . "' and isActive=1 and perUserLimit>=1 and isAdminApproved=1";
        $Result = DB::select($couponQuery);
        if (!empty($Result) && $this->containsOnlyNull($Result) == false) {
            foreach ($Result as $res) {
                $vendorOfferCode = $res->code;
                $minimumAmount = $res->minimumAmount;
                $offerType = $res->offerType;
                $couponCode = $res->couponCode;
                $sign = '';
                if ($res->offerType === 'flat') {
                    $discount = $res->flatAmount;
                } else {
                    $discount = $res->discount;
                }
                $capLimit = $res->capLimit;
                $termsAndConditions = $res->termsAndConditions;
                $perUserLimit = $res->perUserLimit;
                $applyOn = $res->applyOn;
                $offerItems = DB::table("offerincludeditems")
                    ->select(DB::raw("(GROUP_CONCAT(offerincludeditems.itemCode SEPARATOR ',')) as `itemsCodes`"))
                    ->where('offerincludeditems.offerCode', $vendorOfferCode)
                    ->get();

                $notFound = 0;
                $cart = json_decode($r->itemDetails, true);
                if (!empty($cart)) {
                    foreach ($cart as $ct) {
                        if ($applyOn == "item") {
                            if (strpos($offerItems, $ct['itemCode']) !== false) {
                                $discountAmount = $discountAmount + ($ct['price'] * $ct['quantity']);
                            } else {
                                $notFound++;
                                $offerCodeExcludedAmount = $offerCodeExcludedAmount + ($ct['price'] * $ct['quantity']);
                            }
                        } else {
                            $discountAmount = $discountAmount + ($ct['price'] * $ct['quantity']);
                        }
                    }
                }
                if ($notFound != count($cart)) {
                    if ($applyOn == 'item') {
                        $discountAmount = $discountAmount;
                    } else {
                        $discountAmount = $discountAmount + $offerCodeExcludedAmount;
                    }
                }

                if ($minimumAmount <= $discountAmount) {
                    $condition1 = array(
                        'couponusesdetail.restaurantOfferCode' => array("=", $vendorOfferCode),
                        'couponusesdetail.couponCode' => array("=", $couponCode),
                        'couponusesdetail.clientCode' => array("=", $clientCode),
                        "couponusesdetail.restaurantCode" => array("=", $restaurantCode)
                    );

                    $clientUseCouponResult = $this->model->selectQuery(array('couponusesdetail.*'), 'couponusesdetail', array(), $condition1);
                    if (!empty($clientUseCouponResult) && count($clientUseCouponResult) > 0) {
                        foreach ($clientUseCouponResult as $user) {
                            $userLimit = $user->decidedExisitingLimit;
                            if ($userLimit < $perUserLimit) {
                                $nextLimit = $userLimit + 1;
                                return response()->json(["status" => 200,  "message" => $couponCode . " is applicable"], 200);
                            } else {
                                return response()->json(["status" => 300, "message" => "Coupon Already Used"], 200);
                            }
                        }
                    } else {
                        return response()->json(["status" => 200,  "message" => $couponCode . " is applicable"], 200);
                    }
                } else {
                    return response()->json(["status" => 300, "message" => "Amount should be greater than " . $minimumAmount . ". Please check terms and condition..!"], 200);
                }
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid Coupon Code."], 200);
        }
    }

    function containsOnlyNull($input)
    {
        return empty(array_filter($input, function ($a) {
            return $a !== null;
        }));
    }

    public function getCartAmountDetails(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'restaurantCode' => 'required',
            'itemDetails' => 'required',
        ]);
        $cityCode = '';
        $clientCode = $dataValidate['clientCode'];
        $discountAmount = $offerCodeExcludedAmount = 0;
        $itemCodeArr = [];
        $discountRes = (object)array();
        $subTotal = 0;
        $discount = 0;
        $tax = 0;
        $vendorOfferCode = "";
        $couponCode = "";
        $perUserLimit = 0;
        $nextLimit = 0;
        $restaurantCode = $dataValidate['restaurantCode'];
        $serviceableFlag = false;
        $serviceableMessage = "";
        $isserviceable = false;

        $resResult = Restaurants::select("restaurant.isServiceable")
            ->where("restaurant.code", $restaurantCode)
            ->first();
        if (!empty($resResult)) {
            if ($resResult->isServiceable == 1) {
                $isserviceable = true;
            } else {
                $isserviceable = false;
            }
        }

        if (isset($r->couponCode) && $r->couponCode != "") {
            //echo "1";
            $couponData = $this->model->selectQuery(array("restaurantoffer.code", "restaurantoffer.couponCode", "restaurantoffer.perUserLimit", "restaurantoffer.discount as coupondiscount", "restaurantoffer.offerType", "restaurantoffer.capLimit", "restaurantoffer.flatAmount", "restaurantoffer.minimumAmount", "restaurantoffer.applyOn"), "restaurantoffer", array(), array("restaurantoffer.couponCode" => array("=", $r->couponCode)));
            if (!empty($couponData) && count($couponData) > 0) {
                $code = $couponData[0]->code;
                $vendorOfferCode = $code;
                $discountRes->offerCode = $vendorOfferCode;
                $couponCode = $couponData[0]->couponCode;
                $perUserLimit = $couponData[0]->perUserLimit;
                $applyOn = $couponData[0]->applyOn;
                $offerItems = DB::table("offerincludeditems")
                    ->select(DB::raw("(GROUP_CONCAT(offerincludeditems.itemCode SEPARATOR ',')) as `itemsCodes`"))
                    ->where('offerincludeditems.offerCode', $code)
                    ->get();
                $notFound = 0;
                $cart = json_decode($r->itemDetails, true);
                //print_r($cart);
                if (!empty($cart)) {
                    foreach ($cart as $ct) {
                        array_push($itemCodeArr, $ct['itemCode']);
                        if ($applyOn == "item") {
                            if (strpos($offerItems, $ct['itemCode']) !== false) {
                                $discountAmount = $discountAmount + ($ct['price'] * $ct['quantity']);
                            } else {
                                $notFound++;
                                $offerCodeExcludedAmount = $offerCodeExcludedAmount + ($ct['price'] * $ct['quantity']);
                            }
                        } else {
                            $discountAmount = $discountAmount + ($ct['price'] * $ct['quantity']);
                        }
                    }
                }
                if ($notFound != count($cart)) {
                    $status = false;
                    $submessage = '';
                    if ($applyOn == 'item') {
                        $discountAmount = $discountAmount;
                    } else {
                        $discountAmount = $discountAmount + $offerCodeExcludedAmount;
                    }

                    if ($discountAmount >= $couponData[0]->minimumAmount) {
                        switch ($couponData[0]->offerType) {
                            case 'flat':
                                $discount = $couponData[0]->flatAmount;
                                //$discountRes->title= "Flat ".$discount." ₹ Applied";
                                $discountRes->title = "Flat " . $discount . " Applied";
                                $discountRes->message = "₹" . $discount . " savings with this coupon";
                                break;
                            case 'cap':
                                $discount = round($discountAmount * ($couponData[0]->coupondiscount / 100), 2);
                                if ($discount > $couponData[0]->capLimit) {
                                    $discount = $couponData[0]->capLimit;
                                }

                                $discountRes->title = "" . $couponData[0]->coupondiscount . "% Applied";
                                $discountRes->message = "₹" . $discount . " savings with this coupon";
                                break;
                        }
                    }
                } else {
                    $status = false;
                    $submessage = 'Coupon applied only on mentioned items';
                }
            }
        } else {
            $cart = json_decode($r->itemDetails, true);
            if (!empty($cart)) {
                foreach ($cart as $ct1) {
                    array_push($itemCodeArr, $ct1['itemCode']);
                    $discountAmount = $discountAmount + ($ct1['price'] * $ct1['quantity']);
                }
            }
        }

        if ($discount > $discountAmount && $discountAmount != 0 && $discount != 0) {
            $submessage = 'Invalid Coupon discount';
            $discount = 0;
            $status = true;
        } else {
            $submessage = '';
            $status = false;
            $subTotal = ($discountAmount - $discount) + $offerCodeExcludedAmount;
            $taxAmount = $cartPackagingPrice = 0;
            $restLatitude = $restLongitude = '';
            $gstData  = $this->model->selectQuery(array("restaurant.gstApplicable", "restaurant.packagingType", "restaurant.gstPercent", "restaurant.latitude", "restaurant.longitude", "restaurant.cartPackagingPrice"), "restaurant", array(), array("restaurant.code" => array("=", $dataValidate['restaurantCode'])));
            if (!empty($gstData) && count($gstData) > 0) {
                foreach ($gstData as $gst) {
                    $restLatitude = $gst->latitude;
                    $restLongitude = $gst->longitude;
                    $taxApplicable = $gst->gstApplicable;
                    $packagingType = $gst->packagingType;
                    $tax = $gst->gstPercent;
                    if ($packagingType == 'CART') {
                        $cartPackagingPrice = $gst->cartPackagingPrice;
                    } else {

                        if (!empty($itemCodeArr)) {

                            $getProductPackingPrice = DB::table('restaurantitemmaster')
                                ->select(DB::raw('ifnull(sum(itemPackagingPrice),0) as packagingPrice'))
                                ->whereIn('code', $itemCodeArr)
                                ->get();

                            if (!empty($getProductPackingPrice)) {
                                foreach ($getProductPackingPrice as $prd) {
                                    $cartPackagingPrice = $prd->packagingPrice;
                                }
                            }
                        }
                    }
                }
                if ($taxApplicable == 'YES') {
                    if ($tax > 0) {
                        $taxAmount = round(($subTotal * $tax) / 100, 2);
                    }
                }
            }
            $orderAmount = $subTotal + $taxAmount + $cartPackagingPrice;
            $serviceableFlag = true;
            //check selected address serviceable
            $latitude = $longitude = '';
            $addressLatitudeData = $this->model->selectQuery(array("clientprofile.latitude", "clientprofile.longitude"), "clientprofile", array(), array("clientprofile.isActive" => array("=", 1), "clientprofile.isSelected" => array("=", 1), "clientprofile.clientCode" => array("=", $clientCode)));
            if (!empty($addressLatitudeData) &&  count($addressLatitudeData) > 0) {
                $data = $addressLatitudeData[0];
                $latitude = $data->latitude;
                $longitude = $data->longitude;
                $distance = 15;
                $queryServiceable = "SELECT ROUND( 6371 * acos( cos( radians('" . $latitude . "') ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians('" . $longitude . "') ) + sin( radians('" . $latitude . "') ) * sin(radians(restaurant.latitude)) ) ) AS distance ,restaurant.cityCode FROM `restaurant` where restaurant.code='" . $restaurantCode . "' having distance < " . $distance;
                $resultServiceable = DB::select($queryServiceable);
                if (!empty($resultServiceable) && count($resultServiceable) > 0) {
                    foreach ($resultServiceable as $resRw) {
                        $cityCode = $resRw->cityCode;
                    }
                    $serviceableFlag = true;
                } else {

                    $serviceableFlag = false;
                    $serviceableMessage = "You are in new location!";
                }
            } else {
                $serviceableFlag = false;
                $serviceableMessage = "Add or Select Address!";
            }


            // 			$client = $this->model->selectDataByCode('clientmaster',$clientCode);
            // 			if ($client) $cityCode = $client->cityCode;
            $deliveryCharge = 0;
            // shortest distance delivery charges

            $shortestdistance = 0;
            $charges = 0;
            $arrayDist = array();
            $placeApiKey = Config::get('constants.PLACE_API_KEY');
            if ($latitude != "" && $longitude != "" && $restLatitude != "" && $restLongitude != "" && $serviceableFlag) {
                $url = "https://maps.googleapis.com/maps/api/directions/json?destination=$restLatitude,$restLongitude&mode=driving&origin=$latitude,$longitude&key=" . $placeApiKey;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                $response_all = json_decode($response, TRUE);
                //print_r($response_all);
                if (!empty($response_all['routes'])) {
                    foreach ($response_all['routes'] as $res1 => $val) {
                        foreach ($val['legs'] as $keys => $value) {
                            $distance = round($value['distance']['value'] / 1000);
                            array_push($arrayDist, $distance);
                        }
                    }
                    //print_r($arrayDist);
                    $mindistance = min($arrayDist);
                    $shortestdistance = $mindistance;

                    $chargesResult = $this->model->selectQuery(array('deliverychargesslot.fromKM', 'deliverychargesslot.toKM', 'deliverychargesslot.deliveryCharges'), 'deliverychargesslot', array(), array('deliverychargesslot.cityCode' => array("=", $cityCode)), array('deliverychargesslot.fromKM' => 'ASC'));
                    if (!empty($chargesResult) && count($chargesResult) > 0) {
                        $notApply = 0;
                        foreach ($chargesResult as $charge) {
                            if ($charge->fromKM != NULL && $charge->toKM != NULL) {
                                if ($mindistance >= $charge->fromKM && $mindistance <= $charge->toKM) {
                                    $notApply = 1;
                                    $deliveryCharge = $charge->deliveryCharges;
                                    break;
                                }
                            }
                        }
                        if ($notApply == 0) {
                            $deliveryCharge = $chargesResult[0]->deliveryCharges;
                        }
                    }
                }
            }
        }

        //next coupon limit
        if ($discount > 0) {
            $condition1 = array(
                'couponusesdetail.restaurantOfferCode' => array("=", $vendorOfferCode),
                'couponusesdetail.couponCode' => array("=", $couponCode),
                'couponusesdetail.clientCode' => array("=", $clientCode),
                "couponusesdetail.restaurantCode" => array("=", $restaurantCode)
            );

            $clientUseCouponResult = $this->model->selectQuery(array('couponusesdetail.*'), 'couponusesdetail', array(), $condition1);
            if (!empty($clientUseCouponResult) && count($clientUseCouponResult) > 0) {
                foreach ($clientUseCouponResult as $user) {
                    $userLimit = $user->decidedExisitingLimit;
                    if ($userLimit < $perUserLimit) {
                        $nextLimit = $userLimit + 1;
                    }
                }
            } else {
                $nextLimit = 1;
            }

            $discountRes->nextLimit = $nextLimit;
            $discountRes->couponCode = $couponCode;
        }



        $amountBeforeCutlettsApply = $orderAmount + $deliveryCharge;
        $isPointApplicable = false;
        $pointCalculation = (object)array();
        $clientPoints = $minOrderAmount = $perOfPoint = 0;
        $walletData  = $this->model->selectDataByCode('clientmaster', $clientCode);
        if ($walletData) $clientPoints = $walletData->walletPoints;

        if ($clientPoints > 0 && isset($r->applyCuttlets) && $r->applyCuttlets <= $clientPoints && $r->applyCuttlets > 0) {
            $applyCuttlets = $r->applyCuttlets;
            $amountBeforeCutlettsApply = $amountBeforeCutlettsApply - $applyCuttlets;
            $pointCalculation->appliedPoints = $applyCuttlets;
            $pointCalculation->appliedPoints = $applyCuttlets;
            $pointCalculation->message = $applyCuttlets . " Cuttlets Applied";

            /* $minOrderAmountSetting = $this->model->selectQuery('settings.settingValue', 'settings', array(),array('settings.code' => array('=','SET_9')));
			if($minOrderAmountSetting && count($minOrderAmountSetting)>0) $minOrderAmount = $minOrderAmountSetting[0]->settingValue;
			$perOfPointSetting = $this->model->selectQuery('settings.settingValue', 'settings', array(),array('settings.code' => array('=','SET_10')));
			if($perOfPointSetting && count($perOfPointSetting)>0) $perOfPoint = $perOfPointSetting[0]->settingValue;
            $pointCalculation->minimumPointLimit = $minOrderAmount;
            $pointCalculation->perOfPoint = $perOfPoint;
            $pointCalculation->clientPoints = $clientPoints;
            if ($orderAmount < $minOrderAmount) {
                $pointCalculation->appliedPoints = 0;
                $pointCalculation->message = "You can use " . $perOfPoint . " % of points to your cart amount unless minimum Cart amount " . $minOrderAmount;
            } else {
                $perAmount = round(($orderAmount * $perOfPoint) / 100, 2);
                if ($perAmount >= $clientPoints) {
                    $isPointApplicable = true;
                    $pointCalculation->appliedPoints = $clientPoints;
                    $pointCalculation->message = "Points Applied Successfully";
                } else {
                   $isPointApplicable = true;
                   $pointCalculation->appliedPoints= $perAmount;
                   $pointCalculation->message = "Points Applied Successfully";
               }
           }*/
        }

        $finalOrderAmount = $amountBeforeCutlettsApply;
        $itemTotal = $discountAmount + $offerCodeExcludedAmount;
        $res['status'] = $status;
        $res['submessage'] = $submessage;
        $res['itemTotal'] = $discountAmount + $offerCodeExcludedAmount;
        $res['discountAppliedAmount'] = $discountAmount;
        $res['discountExcludedAmount'] = $offerCodeExcludedAmount;
        $res['discount'] = strval($discount);
        $res["subTotal"] = ($discountAmount - $discount) + $offerCodeExcludedAmount;
        $res["taxPer"] = $tax;
        $res["taxAmount"] = strval($taxAmount);
        $res["packagingCharges"] = $cartPackagingPrice;
        $res["shortestDistance"] = $shortestdistance . ' km';
        $res["shippingCharges"] = $deliveryCharge;
        $res["finalOrderAmount"] = round(number_format($finalOrderAmount, 2, ".", ""));
        $res["availableCutlets"] = (int)$clientPoints;
        $res["pointCalculation"] = $pointCalculation;
        $res["discountDetails"] = $discountRes;
        $res["locationServiceable"] = $serviceableFlag;
        $res["locationServiceableMessage"] = $serviceableMessage;
        $res["isserviceable"] = $isserviceable;

        return response()->json(["status" => 200, "message" => "Data Found", "result" => $res], 200);
    }

    public function placeOrder(Request $r)
    {
        $dataValidate = $r->validate([
            'restaurantCode' => 'required',
            'clientCode' => 'required',
            'paymentMode' => 'required',
            'address' => 'required',
            'discount' => 'required',
            'subTotal' => 'required',
            'grandTotal' => 'required',
            'shippingCharges' => 'required',
            'packagingCharges' => 'required',
            'tax' => 'required',
            'usedPoints' => 'required',
            'addressType' => 'required',
            'phone' => 'required',
            'cart' => 'required',
        ]);
        $restaurantCode = $dataValidate['restaurantCode'];
        $clientCode = $dataValidate['clientCode'];
        $couponCode = '';
        if (isset($r->couponCode)) {
            $couponCode = $r->couponCode;
        }
        $isOnlinePayment = false;
        $paymentStatus = 'PNDG';
        $pmode = strtoupper($r->paymentMode);
        if ($pmode == 'COD') {
            $orderStatus = "PND";
        } else {
            $isOnlinePayment = true;
            $orderStatus = "PND";
        }
        $currTime = date('H:i:s');
        $dayofweek = strtolower(date('l'));
        $getClientDetails = $this->model->selectQuery(array("clientmaster.name", "clientmaster.emailId", "clientmaster.mobile"), "clientmaster", array(), array("clientmaster.code" => array("=", $clientCode)));
        if ($getClientDetails) {
            $clientData = $getClientDetails[0];
            $name = $clientData->name;
            $email = $clientData->emailId;
            $mobile = $clientData->mobile;
            $address = $r->address;
            if ($name != "" && $email != "" && $mobile != "") {
                $condition = array("restauranthours.restaurantCode" => array("=", $r->restaurantCode), "restauranthours.weekDay" => array("=", $dayofweek));
                $extracondition = " ('" . $currTime . "' between restauranthours.fromTime and restauranthours.toTime)";
                $timeResult = $this->model->selectQuery("restauranthours.restaurantCode", "restauranthours", array(), $condition, array(), array(), '', '', $extracondition);
                if (!empty($timeResult)) {
                    $serviceable = 1;
                } else {
                    $serviceable = 0;
                }
                $packagingType = "";
                $getRestDetails = $this->model->selectQuery(array("restaurant.packagingType"), "restaurant", array(), array("restaurant.code" => array("=", $restaurantCode)));
                if ($getRestDetails) {
                    $restData = $getRestDetails[0];
                    $packagingType = $restData->packagingType;
                }
                if ($serviceable == 1) {
                    $cart = json_decode($r->cart, true);
                    if (!empty($cart)) {
                        $data = [
                            "restaurantCode" => $restaurantCode,
                            "clientCode" => $clientCode,
                            "couponCode" => $couponCode,
                            "addressType" => $r->addressType,
                            "discount" => $r->discount,
                            "subTotal" => $r->subTotal,
                            "grandTotal" => $r->grandTotal,
                            "shippingCharges" => $r->shippingCharges,
                            "totalPackgingCharges" => $r->packagingCharges,
                            "paymentStatus" => $paymentStatus,
                            "paymentMode" => strtoupper($r->paymentMode),
                            "orderStatus" => $orderStatus,
                            "address" => $r->address,
                            'phone' => $r->phone,
                            "tax" => $r->tax,
                            "packagingType" => $packagingType,
                            "isActive" => 1,
                        ];
                        $orderMasterArray = array();
                        if (isset($r->clientNote) && $r->clientNote != "") $data['clientNote'] = $r->clientNote;
                        if (isset($r->latitude) && $r->latitude != "") $data['latitude'] = $r->latitude;
                        if (isset($r->longitude) && $r->longitude != "") $data['longitude'] = $r->longitude;
                        if (isset($r->flat) && $r->flat != "") $data['flat'] = $r->flat;
                        if (isset($r->landmark) && $r->landmark != "") $data['landmark'] = $r->landmark;
                        $gateway = '';
                        if ($isOnlinePayment) {
                            $orderid = "ORDER" . rand(000, 999);
                            $settingGateway = $this->model->selectQuery(array("settings.settingValue"), "settings", array(), array("settings.code" => array("=", "SET_6")));
                            if (!empty($settingGateway)) {
                                $gateway = $settingGateway[0]->settingValue;
                                if ($gateway == 'razorpay') {
                                    $razor = new Razorpayment;
                                    $result = $razor->createRazorpayOrderid(($r->grandTotal * 100), $name, $email, $mobile, $address);

                                    if ($result == 1) {
                                        return response()->json(["status" => 300, "message" => "Failed to generate payment..Please try again"], 200);
                                    } else {
                                        $webhookUrl = Config::get('constants.PAYMENT_WEBHOOK_URL');
                                        $orderMasterArray['paymentOrderId'] = $result['orderId'];
                                        $orderMasterArray['paymentWebhookUrl'] = $webhookUrl;
                                    }
                                } else {
                                    return response()->json(["status" => 300, "message" => "Payment gateway mismatch"], 200);
                                }
                            } else {
                                return response()->json(["status" => 300, "message" => "Payment gateway not initialised..Please contact administrator"], 200);
                            }
                        }
                        $orderCode = 'ORDER' . rand(99, 99999);
                        $insertResult = $this->model->addNew($data, 'restaurantordermaster', $orderCode);
                        if ($insertResult) {
                            $orderMasterArray['orderCode'] = $insertResult;
                            if ($isOnlinePayment) {
                                $this->model->doEdit(array("paymentOrderId" => $result['orderId']), "restaurantordermaster", $insertResult);
                            }
                            foreach ($cart as $a) {
                                $addons = $a['addons'];
                                $addonsCode = $a['addonsCode'];
                                $addonPrice = $a['addonPrice'];
                                $price = $a['price'];
                                $quantity = $a['quantity'];
                                $itemCode = $a['itemCode'];
                                /*if ($r->packagingType == "PRODUCT") {
									$itemPackagingCharges = 0;
								} else {
									if (isset($a['itemPackagingCharges']) && $a['itemPackagingCharges'] != "") {
										$itemPackagingCharges = $a['itemPackagingCharges'];
									} else {
										$itemPackagingCharges = 0;
									}
								}*/
                                $dataLine = array(
                                    "orderCode" => $insertResult,
                                    "restaurantItemCode" => $itemCode,
                                    "addons" => $addons,
                                    "addonPrice" => $addonPrice,
                                    "addonsCode" => $addonsCode,
                                    "quantity" => $quantity,
                                    "priceWithQuantity" => $price * $quantity,
                                    //'itemPackagingCharges' => $itemPackagingCharges, 
                                    "isActive" => 1
                                );
                                $orderLineResult = $this->model->addNew($dataLine, 'restaurantorderlineentries', 'ORDL');
                            }

                            if ($couponCode != "") {
                                if ((isset($r->restaurantOfferCode)) && $r->restaurantOfferCode != "") {
                                    if ((isset($r->decidedExisitingLimit)) && $r->decidedExisitingLimit > 1) {
                                        $this->use_User_Coupon("update", $r->restaurantOfferCode, $couponCode, $clientCode, $r->decidedExisitingLimit, $restaurantCode);
                                    } else {
                                        $this->use_User_Coupon("add", $r->restaurantOfferCode, $couponCode, $clientCode, $r->decidedExisitingLimit, $restaurantCode);
                                    }
                                }
                            }
                            //update wallet when used points
                            $client_wallet = $this->model->selectQuery(array('clientmaster.walletPoints'), "clientmaster", array(), array("clientmaster.code" => array("=", $clientCode), "clientmaster.isActive" => array("=", 1)));
                            if ($client_wallet && count($client_wallet) > 0) {
                                $previous_amt = $client_wallet[0]->walletPoints ?? 0;
                                if ($r->usedPoints != "" && $r->usedPoints > 0 && $r->usedPoints != NULL && $previous_amt >= $r->usedPoints) {
                                    $message = 'Points used against order - ' . $insertResult;
                                    $update_wallet = $previous_amt - $r->usedPoints;
                                    $update['clientmaster.walletPoints'] = $update_wallet;
                                    $this->model->doEditWithField($update, 'clientmaster', 'code', $clientCode);
                                    $this->addWalletTransaction($clientCode, $insertResult, $r->usedPoints, $message, 'Subtract');
                                    $ordData['restaurantordermaster.usedPoints'] = $r->usedPoints;
                                }
                            }
                            sleep(1);
                            //add cutlet points to customer
                            $this->addCutlet($clientCode, $insertResult, $restaurantCode, $r->grandTotal, $r->shippingCharges);
                            if (!empty($ordData)) {
                                $this->model->doEdit($ordData, 'restaurantordermaster', $insertResult);
                            }
                            $order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster", array(), array("restaurantorderstatusmaster.statusSName" => array("=", $orderStatus)));
                            if ($order_status && count($order_status) > 0) {
                                $order_status_record = $order_status[0];
                                $statusTitle = $order_status_record->messageTitle;
                                #replace $ template in title 
                                $statusDescription = $order_status_record->messageDescription;
                                $statusDescription = str_replace("$", $insertResult, $statusDescription);
                                $dataBookLine = array(
                                    "orderCode" => $insertResult,
                                    "statusPutCode" => $clientCode,
                                    "statusLine" => $orderStatus,
                                    "reason" => 'Booked by Client',
                                    "statusTime" => date("Y-m-d H:i:s"),
                                    "statusTitle" => $statusTitle,
                                    "statusDescription" => $statusDescription,
                                    "isActive" => 1
                                );
                                $bookLineResult = $this->model->addNew($dataBookLine, 'bookorderstatuslineentries', 'BOL');
                            }
                            return response()->json(["status" => 200, "message" => "Order Placed Successfully" . $insertResult, "result" => $orderMasterArray], 200);
                        } else {
                            return response()->json(["status" => 300, "message" => " Opps...! Something went wrong please try again."], 200);
                        }
                    } else {
                        return response()->json(["status" => 300, "message" => " Opps...! empty cart."], 200);
                    }
                } else {
                    return response()->json(["status" => 300, "message" => "Restaurant has been closed and shall not accept the order! Sorry for your inconvenience."], 200);
                }
            } else {
                return response()->json(["status" => 300, "message" => "Please complete your profile first"], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid Client Code"], 200);
        }
    }

    public function verifyPayment(Request $r)
    {
        Log::info(json_encode($r->all()));

        $dt = file_get_contents('php://input');
        $header = getallheaders();
        $receivedData = $r->all();
        $webhookBody =  json_encode($r->toArray());
        $receivedSignature = $r->header('X-Razorpay-Signature');
        $webhookSecret = Config::get('constants.RAZOR_WEBHHOK_SECRET');
        $razor_pay_sec_key = Config::get('constants.RAZOR_KEY_SECRET');
        $razorKeyId = Config::get('constants.RAZOR_KEY_ID');




        Log::info("signature->" . json_encode($receivedSignature) . " Headers->" . json_encode($header));
        $calculated = hash_hmac('sha256', $dt, 'cutlett@Cutlett123');

        if ($calculated == $receivedSignature) {
            Log::info("true calculated->" . $calculated . " received->" . $receivedSignature);
            return response()->json(["status" => 200, "message" => "signatue match", "calculated" => $calculated, "receivedSignature" => $receivedSignature], 200);
        } else {
            Log::info("else calculated->" . $calculated . " received->" . $receivedSignature);
            return response()->json(["status" => 300, "message" => "signatue failed", "calculated" => $calculated, "receivedSignature" => $receivedSignature], 300);
        }



        $razorpay_payment_id = $receivedData['payload']['payment']['entity']['id'];
        $razorpay_order_id = $receivedData['payload']['payment']['entity']['order_id'];

        $api = new Api($razorKeyId, $razor_pay_sec_key);

        try {
            $api->utility->verifyWebhookSignature($dt, $receivedSignature, $webhookSecret);
            $signatureFlag = true;
        } catch (Exception $e) {
            Log::info('RazorPay Webhook validation Error: ' . $e->getMessage());
            $signatureFlag = false;
            return response()->json(["status" => 300, "message" => "Payment Failed", "paymentId" => $razorpay_payment_id, "payment_status" => "FAILED"], 300);
        }



        if ($signatureFlag) {
            $random = rand(0, 999);
            $random = date('his') . $random;

            //   $razor_signature =  $r->razorpay_signature;
            //   $clientCode =  $r->clientCode;
            //   $orderCode =  $r->orderCode;
            $dataStore['webhookResponse'] = json_encode($receivedData);
            $this->model->doEditWithField($dataStore, 'restaurantordermaster', 'paymentOrderId', $razorpay_order_id);
            $result = $this->model->selectQuery(array("restaurantordermaster.code", "restaurantordermaster.clientCode", "restaurantordermaster.paymentStatus"), 'restaurantordermaster', array(), array("restaurantordermaster.paymentOrderId" => array("=", $razorpay_order_id)));
            if (count($result) > 0) {
                $paymentStatus = $result[0]->paymentStatus;
                $orderCode = $result[0]->code;
                $clientCode = $result[0]->clientCode;
                if ($paymentStatus == 'PNDG') {
                    if ($receivedData['payload']['payment']['entity']['status'] == "captured") {
                        $data['paymentId'] = $razorpay_payment_id;
                        $data['orderStatus'] = 'PND';
                        $data['paymentStatus'] = "PID";
                        $result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
                        $dataNoti = array('title' => 'Payment Successful', 'message' => 'Order placed successfully', 'unique_id' => $orderCode, 'random_id' => $random, 'type' => 'Order');
                        $this->sendCustomerNotification($clientCode, $dataNoti);
                        return response()->json(["status" => 200, "message" => "Payment Successful", "paymentId" => $razorpay_payment_id, "payment_status" => "SUCCESS"], 200);
                    } else {
                        $data['paymentId'] = $razorpay_payment_id;
                        $data['orderStatus'] = 'CAN';
                        $data['paymentStatus'] = "RJCT";
                        $result = $this->model->doEdit($data, 'restaurantordermaster', $orderCode);
                        $dataNoti = array('title' => 'Payment Failed', 'message' => 'Order Cancelled', 'unique_id' => $orderCode, 'random_id' => $random, 'type' => 'Order');
                        $this->sendCustomerNotification($clientCode, $dataNoti);
                        return response()->json(["status" => 300, "message" => "Payment Failed", "paymentId" => $razorpay_payment_id, "payment_status" => "FAILED"], 200);
                    }
                } else {
                    return response()->json(["status" => 300, "message" => "Unable to process payment"], 200);
                }
            } else {
                return response()->json(["status" => 300, "message" => "Unable to process payment"], 200);
            }
        }
    }

    public function sendCustomerNotification($clientCode, $dataNoti)
    {
        $orderBy = array('clientdevicedetails.id' => 'DESC');
        $checkdevices = $this->model->selectQuery(array('clientdevicedetails.firebaseId'), 'clientdevicedetails', array(), array('clientdevicedetails.clientCode' => array("=", $clientCode)), $orderBy);
        if ($checkdevices) {
            $DeviceIdsArr = array();
            foreach ($checkdevices as $c) {
                $DeviceIdsArr[] = $c->firebaseId;
            }
            if (!empty($DeviceIdsArr)) {
                $dataArr = array();
                $dataArr['device_id'] = $DeviceIdsArr;
                $dataArr['message'] = $dataNoti['message']; //Message which you want to send
                $dataArr['title'] = $dataNoti['title'];
                $dataArr['unique_id'] = $dataNoti['unique_id'];
                $dataArr['random_id'] = $dataNoti['random_id'];
                $dataArr['type'] = $dataNoti['type'];
                $notification['device_id'] = $DeviceIdsArr;
                $notification['message'] = $dataNoti['message']; //Message which you want to send
                $notification['title'] = $dataNoti['title'];
                $notification['unique_id'] = $dataNoti['unique_id'];
                $notification['random_id'] = $dataNoti['random_id'];
                $notification['type'] = $dataNoti['type'];
                $noti = new Notificationlibv_3;
                $result = $noti->sendNotification($dataArr, $notification);
                //print_r($result);
            }
        }
        return true;
    }

    public function saveDeliveryBoyRating(Request $r)
    {
        $dataValidate = $r->validate([
            'orderCode' => 'required',
            'clientCode' => 'required',
            'deliveryBoyCode' => 'required',
            'rating' => 'required',
        ]);
        $clientCode = $dataValidate['clientCode'];
        $Result = $this->model->selectQuery(array('clientmaster.*'), 'clientmaster', array(), array('clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            $dataProfile = [
                "clientCode" => $clientCode,
                "orderCode" => $dataValidate['orderCode'],
                "deliveryBoyCode" => $dataValidate['deliveryBoyCode'],
                "rating" => $dataValidate['rating'],
                "addDate" => date("Y-m-d H:i:s")
            ];
            if (isset($r->review) && $r->review != '') {
                $dataProfile['review'] = $r->review;
            }
            $currentId = DB::table('rating')->insertGetId($dataProfile);
            if ($currentId > 0) {
                return response()->json(["status" => 200, "message" => "Rating submitted successfully"], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Something Went Wrong."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function saveRestaurantRating(Request $r)
    {
        $dataValidate = $r->validate([
            'orderCode' => 'required',
            'clientCode' => 'required',
            'restaurantCode' => 'required',
            'rating' => 'required'
        ]);
        $clientCode = $dataValidate['clientCode'];
        $Result = $this->model->selectQuery(array('clientmaster.*'), 'clientmaster', array(), array('clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            $dataProfile = [
                "clientCode" => $clientCode,
                "orderCode" => $dataValidate['orderCode'],
                "restaurantCode" => $dataValidate['restaurantCode'],
                "rating" => $dataValidate['rating'],
                "addDate" => date("Y-m-d H:i:s"),
                "isAccept" => 0
            ];
            if (isset($r->review) && $r->review != '') {
                $dataProfile['review'] = $r->review;
            }
            $currentId = DB::table('rating')->insertGetId($dataProfile);
            if ($currentId > 0) {
                return response()->json(["status" => 200, "message" => "Rating submitted successfully"], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Something Went Wrong."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function favouriteRestaurantUpdate(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'restaurantCode' => 'required'
        ]);
        $clientCode = $dataValidate['clientCode'];
        $restaurantCode = $dataValidate['restaurantCode'];
        $Result = $this->model->selectQuery(array('clientmaster.*'), 'clientmaster', array(), array('clientmaster.code' => array('=', $clientCode)));
        if ($Result && count($Result) > 0) {
            $checkfavourite = $this->model->selectQuery(array('favourite_restaurants.code'), 'favourite_restaurants', array(), array('favourite_restaurants.restaurantCode' => array('=', $restaurantCode), 'favourite_restaurants.clientCode' => array('=', $clientCode)));
            if ($checkfavourite && count($checkfavourite) > 0) {
                $code = $checkfavourite[0]->code;
                $deleteResult = $this->model->deletePermanent($code, 'favourite_restaurants');
                if ($code) {
                    return response()->json(["status" => 200, "message" => "Restaurant removed from favourites"], 200);
                } else {
                    return response()->json(["status" => 300, "message" => "Failed to remove from favourites"], 200);
                }
            } else {
                $favouriteData = [
                    "clientCode" => $clientCode,
                    "restaurantCode" => $restaurantCode,
                    "isActive" => 1,
                    "isDelete" => 0,
                    "addDate" => date("Y-m-d H:i:s")
                ];
                $code = $this->model->addNew($favouriteData, "favourite_restaurants", "FR");
                if ($code) {
                    return response()->json(["status" => 200, "message" => "Restaurant added to favourite", "result" => $code], 200);
                } else {
                    return response()->json(["status" => 300, "message" => "Failed to add to favourites"], 200);
                }
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid client code"], 200);
        }
    }

    public function getFavRestaurantList(Request $r)
    {
        $currentDate = date('Y-m-d');
        $cond = "restaurant.isActive=1";
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'restaurantCode' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'cityCode' => 'required'
        ]);
        if (isset($r->cuisineCode) && $r->cuisineCode != "") {
            $split_cuisine = explode(',', $r->cuisineCode);
            if (!empty($split_cuisine)) {
                $values  = "";
                foreach ($split_cuisine as $cui) {
                    $values != "" && $values .= ",";
                    $values .= "'" . $cui . "'";
                }
                if ($cond != "") $cond .= " and restaurantcuisinelineentries.cuisineCode in (" . $values . ") ";
                else $cond .= " restaurantcuisinelineentries.cuisineCode in (" . $values . ") ";
            }
        }
        if ($cond != "") $cond .= " and restaurant.cityCode ='" . $r->cityCode . "'";
        else $cond .= " restaurant.cityCode ='" . $r->cityCode . "'";
        if (isset($r->entitycategoryCode)) {
            if ($r->entitycategoryCode != "") {
                if ($cond != "") $cond .= " and restaurant.entitycategoryCode='" . $r->entitycategoryCode . "' ";
                else $cond .= " restaurant.entitycategoryCode='" . $r->entitycategoryCode . "' ";
            }
        }

        if (isset($r->entityName)) {
            if ($r->entityName != "") {
                if ($cond != "") $cond .= " and restaurant.entityName like '%" . $r->entityName . "%' ";
                else $cond .= " restaurant.entityName like '%" . $r->entityName . "%' ";
            }
        }

        $geoLocation = "";
        $having = "";
        $geoLocation = ",ROUND( 6371 * acos( cos( radians(" . $r->latitude . ") ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians(" . $r->longitude . ") ) + sin( radians(" . $r->latitude . ") ) * sin(radians(restaurant.latitude)) ) ) AS distance";
        $having = " HAVING distance <= 500";

        $columns = "select restaurant.*,entitycategory.entityCategoryName,citymaster.cityName,customaddressmaster.place" . $geoLocation . ",restaurantoffer.code as offerCode,restaurantoffer.restaurantCode as restaurantCode,restaurantoffer.couponCode,restaurantoffer.offerType,ifnull(GREATEST(ifnull(MAX(restaurantoffer.discount),0),ifnull(MAX(restaurantoffer.flatAmount),0)),'') as discount,restaurantoffer.minimumAmount,restaurantoffer.perUserLimit,restaurantoffer.startDate,restaurantoffer.endDate,restaurantoffer.capLimit,restaurantoffer.termsAndConditions,restaurantoffer.isAdminApproved as vAdminapproved,restaurantoffer.isActive as status from restaurant ";
        $orderBy = " order by restaurant.isServiceable DESC , restaurant.id ASC";
        $join = " INNER JOIN favourite_restaurants ON restaurant.code = favourite_restaurants.`restaurantCode` ";
        $join .= " INNER JOIN entitycategory ON restaurant.entitycategoryCode = entitycategory.`code` LEFT JOIN restaurantcuisinelineentries ON restaurant.`code` = restaurantcuisinelineentries.vendorCode ";
        $join .= " inner JOIN citymaster ON citymaster.`code`=restaurant.cityCode  LEFT JOIN customaddressmaster ON restaurant.`addressCode` = customaddressmaster.code ";
        $join .= " LEFT JOIN restaurantoffer ON restaurant.`code` = restaurantoffer.restaurantCode ";
        $groupBy = " Group by restaurant.code ";
        $limit = 10;
        $offset = "";
        if (isset($r->offset)) {
            if ($r->offset > 0) $limit_offset = " limit " . $r->offset . ',10';
            else $limit_offset = " limit 10";
        } else {
            $limit_offset = " limit 10";
        }
        if ($cond != "") $whereCondition = " where " . $cond;
        else $whereCondition = "";
        $query = $columns . $join . $whereCondition . $groupBy . $having . $orderBy . $limit_offset;
        //DB::enableQueryLog();
        $result = DB::select($query);
        //$query_1 = DB::getQueryLog();
        // print_r($query_1);
        //exit();
        if (!empty($result)) {
            $data = array();
            foreach ($result as $r) {
                $cuisinesList = "";
                $cuisineRecords = DB::table("cuisinemaster")
                    ->select(DB::raw("(GROUP_CONCAT(cuisinemaster.cuisineName SEPARATOR ',')) as `cuisines`"))
                    ->join('restaurantcuisinelineentries', 'restaurantcuisinelineentries.cuisineCode', 'cuisinemaster.code')
                    ->where('restaurantcuisinelineentries.vendorCode', $r->code)
                    ->where('cuisinemaster.isActive', 1)
                    ->get();
                if ($cuisineRecords) {
                    foreach ($cuisineRecords as $cu)
                        $cuisinesList = $cu->cuisines;
                }
                $vendorar['code'] = $r->code;
                $vendorar['entityName'] = $r->entityName;
                $vendorar['firstName'] = $r->firstName;
                $vendorar['middleName'] = $r->middleName;
                $vendorar['lastName'] = $r->lastName;
                $vendorar['latitude'] = $r->latitude;
                $vendorar['longitude'] = $r->longitude;
                $vendorar['isServiceable'] = $r->isServiceable;
                if ($r->entityImage != "") {
                    $path = 'uploads/restaurant/restaurantimage/' . $r->entityImage;
                    if (file_exists($path)) $vendorar['entityImage'] = url($path);
                    else $vendorar['entityImage']  = url('uploads/restaurent_no_image.png');
                } else $vendorar['entityImage']  = url('uploads/restaurent_no_image.png');

                $vendorar['address'] = $r->address;
                $vendorar['packagingType'] = $r->packagingType;
                $vendorar['cartPackagingPrice'] = $r->cartPackagingPrice;
                $vendorar['gstApplicable'] = $r->gstApplicable;
                $vendorar['gstPercent'] = $r->gstPercent;
                $vendorar['ownerContact'] = $r->ownerContact;
                $vendorar['entityContact'] = $r->entityContact;
                $vendorar['email'] = $r->email;
                $vendorar['entityCategoryName'] = $r->entityCategoryName;
                $vendorar['fssaiNumber'] = $r->fssaiNumber;
                $vendorar['cityName'] = $r->cityName;
                $vendorar['aresName'] = $r->place;
                $vendorar['cuisinesList'] = $cuisinesList;
                $discount = array();
                if ($r->discount != '' && $r->vAdminapproved == 1 && $r->startDate <= $currentDate && $r->endDate >= $currentDate && $r->status == 1) {
                    $discount->offerCode = $r->offerCode;
                    $discount->vendorCode = $r->vendorCode;
                    $discount->coupanCode = $r->coupanCode;
                    $discount->offerType = $r->offerType;
                    $sign = '';
                    if ($r['offerType'] === 'flat') {
                        $sign = ' ₹';
                    } else {
                        $sign = ' %';
                    }
                    $discount->discount = $r->discount . $sign;
                    $discount->minimumAmount = $r->minimumAmount;
                    $discount->perUserLimit = $r->perUserLimit;
                    $discount->startDate = date('d-m-Y h:i A', strtotime($r->startDate));
                    $discount->endDate = date('d-m-Y h:i A', strtotime($r->endDate));
                    $discount->capLimit = $r->capLimit;
                    $discount->termsAndConditions = $r->termsAndConditions;
                }
                $vendorar['discount'] = $discount;
                $dayofweek = strtolower(date('l'));

                $timeQuery = "select restauranthours.code as hourCode,time_format(restauranthours.fromTime,'%h:%i %p') as fromTime,time_format(restauranthours.toTime,'%h:%i %p') as toTime from restauranthours where restauranthours.restaurantCode = '" . $r->code . "' and restauranthours.weekDay='" . $dayofweek . "'";
                $timeData = DB::select($timeQuery);
                if ($timeData) {
                    $vendorar['restaurantHours'] = $timeData;
                } else {
                    $vendorar['restaurantHours'] = array();
                }
                $vendorar['ratingDetails'] = [];
                $sum = 0;
                $count = 0;
                $sum1 = 0;
                $ratingfinalArray = [];
                $orderColumns1 = array("rating.id", "clientmaster.name", "rating.clientCode", "rating.orderCode", "rating.restaurantCode", "rating.rating", "rating.review", "rating.addDate", "rating.isAccept");
                $condition1 = array("rating.restaurantCode" => array("=", $r->code), "rating.isAccept" => array("=", 1));
                $join1 = array("clientmaster" => array("clientmaster.code", "rating.clientCode"));
                $extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                $ratingDetails = $this->model->selectQuery($orderColumns1, 'rating', $join1, $condition1, array(), array(), "", "", $extraCondition1);
                if (!empty($ratingDetails) && count($ratingDetails) > 0) {
                    foreach ($ratingDetails as $rating) {
                        $sum = $sum + $rating->rating;
                        $count++;
                        $dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $rating->addDate);
                        $ratingDate = $dFormat->format('d-m-Y H:i:s');
                        $ratingArr['id'] = $rating->id;
                        $ratingArr['orderCode'] =  $rating->orderCode;
                        $ratingArr['clientCode'] =  $rating->clientCode;
                        $ratingArr['clientName'] =  $rating->name;
                        $ratingArr['rating'] = $rating->rating;
                        $ratingArr['review'] = $rating->review;
                        $ratingArr['date'] = $ratingDate;
                        $ratingOrderArray[] = $ratingArr;
                    }
                    $orderColumns2 = array(DB::raw("ifnull(sum(rating.rating),0) as rating"), DB::raw("ifnull(count(id),0) as count"));
                    $condition2 = array("rating.restaurantCode" => array("=", $r->code), "rating.isAccept" => array("=", 1));
                    $extraCondition2 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                    $groupBy2 = array("rating.rating");
                    $rating1 = $this->model->selectQuery($orderColumns2, 'rating', array(), $condition2, array(), array(), "", "", $extraCondition1, array(), $groupBy2);
                    if (!empty($rating1) && count($rating1) > 0) {
                        foreach ($rating1 as $ra) {
                            $sum1 = $sum1 + ($ra->rating * $ra->count);
                        }
                    }
                    //round($sum1/$sum,1);
                    $ratingfinalArray['avgRating'] = strval(round($sum1 / $sum, 1));
                    $ratingfinalArray['ratingOrderArray'] = $ratingOrderArray;
                }
                $vendorar['averageRating'] = $ratingfinalArray;
                $data[] = $vendorar;
            }
            $response['restaurants'] = $data;
            return response()->json(["status" => 200, "message" => 'Data Found', "result" => $response], 200);
        } else {
            return response()->json(["status" => 300,  "message" => 'No Data Found'], 200);
        }
    }

    public function getSupportContact()
    {
        $contact = '';
        $settingResult = $this->model->selectQuery('settings.settingValue', 'settings', array(), array('settings.code' => array('=', 'SET_8'), 'settings.isActive' => array('=', 1)));
        if ($settingResult && count($settingResult)) {
            $contact = $settingResult[0]->settingValue;
            return response()->json(["status" => 200, "message" => "For Support", "contactNumber" => $contact], 200);
        } else {
            return response()->json(["status" => 200, "message" => "For Support", "contactNumber" => $contact], 200);
        }
    }

    // check wishlist 
    public function checkWishList(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'itemCode' => 'required'
        ]);
        $clientCode = $r->code;
        $condition2 = array("clientCode" => array("=", $r->clientCode), "itemCode" => array("=", $r->itemCode), "isActive" => array("=", 1));
        $wishlist_result = $this->model->selectQuery(array('clientwishlist.id'), 'clientwishlist', array(), $condition2);
        if ($wishlist_result && count($wishlist_result) > 0) {
            return response()->json(["status" => 200, "message" => 'item already exist in wishlist'], 200);
        } else {
            return response()->json(["status" => 300, "message" => 'iteam not added to wishlist yet'], 200);
        }
    }
    public function addToWishlist(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'itemCode' => 'required'
        ]);
        $itemCode = $r->itemCode;
        $clientCode = $r->clientCode;
        $condition2 = array('itemCode' => array('=', $itemCode), 'clientCode' => array('=', $clientCode), 'isActive' => array('=', 1));
        $clientWishList = $this->model->selectQuery(array('clientwishlist.*'), 'clientwishlist', array(), $condition2);
        if ($clientWishList && count($clientWishList) > 0) {
            $code = $clientWishList[0]->code;
            $this->model->deletePermanent($code, 'clientwishlist');
            return response()->json(["status" => 200, "message" => "Item removed from wishlist successfully."], 200);
        } else {
            $data = [
                'itemCode' => $itemCode,
                'clientCode' => $clientCode,
                'isActive' => 1
            ];
            $code = $this->model->addNew($data, 'clientwishlist', 'WISH');
            if ($code) {
                return response()->json(["status" => 200, "message" => "Item added to wishlist successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Item not added. Please try again later."], 200);
            }
        }
    }

    public function getWishList(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
        ]);
        $addonArray = [];
        $choiceArray = [];
        $mainCount = 0;
        $orderColumns2 = array("restaurantitemmaster.*", "restaurant.entityName", "restaurant.isServiceable as vendorIsServiceable");
        $condition2 = array('restaurantitemmaster.isActive' => array("=", 1), "restaurantitemmaster.isAdminApproved" => array("=", 1));
        $orderBy2 = array('restaurantitemmaster.id' => 'DESC');
        $join2 = array("clientwishlist" => array("clientwishlist.itemCode", "restaurantitemmaster.code"), "restaurant" => array("restaurantitemmaster.restaurantCode", "restaurant.code"));
        $itemRecords = $this->model->selectQuery($orderColumns2, "restaurantitemmaster", $join2, $condition2, $orderBy2);
        if (!empty($itemRecords)) {
            foreach ($itemRecords as $r) {
                $vendorItemCode = $r->code;
                $CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'addon')));
                if (!empty($CCRecordsAddon)) {
                    foreach ($CCRecordsAddon as $ccra) {
                        $customizedCategoryCode = $ccra->code;
                        $categoryTitle = $ccra->categoryTitle;
                        $CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                        $addonCustomizedCategoryArray = array();
                        if (!empty($CCRecordsAddonLine)) {
                            $addonCustomizedCategoryArray = array();
                            foreach ($CCRecordsAddonLine as $ccraL) {
                                $subCategoryTitle = $ccraL->subCategoryTitle;
                                $price = $ccraL->price;
                                $addonCustomizedCategoryArray[] = array(
                                    "lineCode" => $ccraL->code,
                                    "subCategoryTitle" => $subCategoryTitle,
                                    "price" => $price,
                                );
                            }
                        }
                        $addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
                    }
                }

                $CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'choice')));
                if (!empty($CCRecordsChoice)) {
                    foreach ($CCRecordsChoice as $ccrc) {
                        $customizedCategoryCode = $ccrc->code;
                        $categoryTitle = $ccrc->categoryTitle;
                        $CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                        $choiceCustomizedCategoryArray = array();
                        if (!empty($CCRecordsChoiceLine)) {
                            $choiceCustomizedCategoryArray = array();
                            foreach ($CCRecordsChoiceLine as $ccrcL) {
                                $subCategoryTitle = $ccrcL->subCategoryTitle;
                                $price = $ccrcL->price;
                                $choiceCustomizedCategoryArray[] = array(
                                    "lineCode" => $ccrcL->code,
                                    "subCategoryTitle" => $subCategoryTitle,
                                    "price" => $price,
                                );
                            }
                        }
                        $choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
                    }
                }

                $path = url('uploads/restaurent_no_image.png');
                if ($r->itemPhoto != "") {
                    $filepath = url('uploads/restaurant/' . $r->restaurantCode . '/restaurantitemimage/' . $r->itemPhoto);
                    if (file_exists($filepath))  $path =  $filepath;
                }
                $mainitemArray[] = array(
                    "restaurantCode" => $r->restaurantCode,
                    "itemCode" => $r->code,
                    "itemName" => $r->itemName,
                    "itemDescription" => $r->itemDescription,
                    "salePrice" => $r->salePrice,
                    "itemPhoto" => $path,
                    "restaurantName" => $r->entityName,
                    "vendorIsServiceable" => $r->vendorIsServiceable,
                    "isServiceable" => $r->itemActiveStatus,
                    "cuisineType" => $r->cuisineType,
                    "isActive" => $r->isActive,
                    "maxOrderQty" => $r->maxOrderQty,
                    "itemPackagingPrice" => $r->itemPackagingPrice,
                    "addons" => $addonArray,
                    "choice" => $choiceArray,
                );
                $mainCount++;
            }
            $response['wishList'] = $mainitemArray;
            return response()->json(["status" => 200, "message" => 'Data Found', "totalRecords" => $mainCount, "result" => $response], 200);
        } else {
            return response()->json(["status" => 300,  "message" => 'No Data Found'], 200);
        }
    }

    public function getClientOrderList(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
        ]);

        $clientCode = $r->clientCode;
        $tableName = "restaurantordermaster";
        $orderColumns = array(DB::raw("restaurantordermaster.code as orderCode,restaurant.entityImage as restaurantImage,restaurantordermaster.deliveryBoyCode,usermaster.name as dBoyName,usermaster.latitude as dBoyLat,usermaster.longitude as dBoyLong,restaurantordermaster.restaurantCode,restaurantordermaster.totalPackgingCharges,restaurantordermaster.packagingType,restaurantordermaster.addressType,restaurantordermaster.tax,restaurantordermaster.discount,restaurantordermaster.subTotal,restaurantordermaster.phone,restaurantordermaster.shippingCharges as deliveryCharges,restaurantordermaster.usedPoints as useCutlets,restaurantordermaster.paymentmode,restaurantordermaster.address,restaurantordermaster.grandTotal as orderTotalPrice,restaurantordermaster.addDate as orderDate, restaurantorderstatusmaster.statusName as orderStatus, paymentstatusmaster.statusName as paymentStatus,restaurant.entityName,restaurant.address as pickUpAddress,restaurant.latitude as sourceLat,restaurant.longitude as sourceLong,restaurant.cityCode,restaurant.addressCode,citymaster.cityName,restaurantordermaster.latitude as destiLat,restaurantordermaster.longitude as destiLong"));
        $cond = array('restaurantordermaster.clientCode' => array("=", $clientCode), "restaurantordermaster.isActive" => array("=", 1));
        $orderBy = array("restaurantordermaster.id" => 'DESC');
        //$join = array('usermaster'=>array('restaurantordermaster.deliveryBoyCode','usermaster.code'),'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus','restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus','paymentstatusmaster.statusSName'), "restaurant" => array("restaurantordermaster.restaurantCode","restaurant.code"), "citymaster" => array("restaurant.cityCode","citymaster.code"), "customaddressmaster" => array("restaurant.addressCode","customaddressmaster.code"),"bookorderstatuslineentries" => array("restaurantordermaster.orderStatus","bookorderstatuslineentries.statusLine"));
        //$joinType = array('usermaster'=>'left','restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner', "restaurant" => "inner", "citymaster" => "left", "customaddressmaster" => "left","bookorderstatuslineentries" => "left");

        $join = array('usermaster' => array('restaurantordermaster.deliveryBoyCode', 'usermaster.code'), 'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus', 'restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus', 'paymentstatusmaster.statusSName'), "restaurant" => array("restaurantordermaster.restaurantCode", "restaurant.code"), "citymaster" => array("restaurant.cityCode", "citymaster.code"));
        $joinType = array('usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner', "restaurant" => "inner", "citymaster" => "left");
        //$extracondition=" bookorderstatuslineentries.orderCode = restaurantordermaster.code";
        $extracondition = "";
        $limit = 10;
        $offset = "0";
        if (isset($r->offset)) {
            $offset = $r->offset;
        }
        $resultQuery = $this->model->selectQuery($orderColumns, $tableName, $join, $cond, $orderBy, array(), $limit, $offset, $extracondition, $joinType);
        $query_1 = DB::getQueryLog();
        // print_r($query_1);
        $imageArray = array();
        if ($resultQuery && count($resultQuery) > 0) {
            $totalOrders = sizeof($resultQuery);
            $clientOrderList = json_decode(json_encode($resultQuery), true);
            for ($i = 0; $i < $totalOrders; $i++) {
                $orderCode = $clientOrderList[$i]['orderCode'];


                $restaurantImage = $clientOrderList[$i]['restaurantImage'];
                $imagePath = url('uploads/restaurent_no_image.png');
                if ($restaurantImage != "") {
                    $path = 'uploads/restaurant/restaurantimage/' . $restaurantImage;
                    if (file_exists($path)) $imagePath = url($path);
                }

                $rating = 0;
                $review = '';
                $isAccept = 0;
                $orderColumns1 = array("rating.rating", "rating.review", "rating.isAccept");
                $condition1 = array("rating.orderCode" => array("=", $orderCode));
                $extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                $ratingDetails = $this->model->selectQuery($orderColumns1, 'rating', array(), $condition1, array(), array(), "", "", $extraCondition1);
                if (!empty($ratingDetails) && count($ratingDetails) > 0) {
                    foreach ($ratingDetails as $rat) {
                        $rating = $rat->rating;
                        $review = $rat->review;
                        $isAccept = $rat->isAccept;
                    }
                }
                $clientOrderList[$i]['rating'] = $rating;
                $clientOrderList[$i]['review'] = $review;
                $clientOrderList[$i]['isAdminApproved'] = $isAccept;
                $clientOrderList[$i]['restaurantImage'] = $imagePath;
                //$clientOrderList[$i]['statusTime']=date('d-m-Y h:i A',strtotime($clientOrderList[$i]['statusTime']));
                $addonArray = $resultAddonArray = [];
                $linetableName = "restaurantorderlineentries";
                $lineorderColumns = array(DB::raw("restaurantorderlineentries.restaurantItemCode,restaurantitemmaster.cuisineType,restaurantorderlineentries.quantity,restaurantorderlineentries.priceWithQuantity as priceWithQuantity,restaurantorderlineentries.addons,restaurantorderlineentries.addonsCode,restaurantitemmaster.itemName,restaurantorderlineentries.itemPackagingCharges"));
                $linecond = array("restaurantorderlineentries.orderCode" => array("=", $clientOrderList[$i]['orderCode']));
                $lineorderBy = array('restaurantorderlineentries' . ".id" => 'ASC');
                $linejoin = array('restaurantitemmaster' => array('restaurantorderlineentries.restaurantItemCode', 'restaurantitemmaster.code'));
                $linejoinType = array('restaurantitemmaster' => 'inner');
                $orderProductRes = $this->model->selectQuery($lineorderColumns, $linetableName, $linejoin, $linecond, $lineorderBy, array(), "", "", "", $linejoinType);
                if ($orderProductRes) {
                    foreach ($orderProductRes as $subItem) {
                        $addonArray = $subItem;
                        $resultArr = [];
                        /*if($subItem->addonsCode!='' && $subItem->addonsCode!=NULL){
							$subItem->addonsCode = rtrim($subItem->addonsCode,',');
							$savedaddonsCodes = explode(',',$subItem->addonsCode);
							foreach($savedaddonsCodes as $addon){
								$categoryArr=[];
								$joinType1 = array('customizedcategory' => 'inner');
								$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
								$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
								$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),"","","",$joinType1);
								if($getAddonDetails && count($getAddonDetails)>0){
									$categoryArr = $getAddonDetails[0];
								}
								$resultArr[]=$categoryArr;
							}
						}
						$addonArray->addonsDetails=$resultArr;*/
                        $resultAddonArray[] = $addonArray;
                    }
                }
                $clientOrderList[$i]['orderedItems'] = $resultAddonArray;
                $cutletDetails = array();
                $tableName = "wallettransactions";
                $orderColumns = array("wallettransactions.id", "wallettransactions.code", "wallettransactions.orderCode", "wallettransactions.type", "wallettransactions.message", "wallettransactions.point", "wallettransactions.status", DB::raw("DATE_FORMAT(wallettransactions.addDate, '%d %b %Y %h:%i %p') as transactionDate"));
                $cond = array('wallettransactions.clientCode' => array("=", $clientCode), 'wallettransactions.orderCode' => array("=", $clientOrderList[$i]['orderCode']));
                $orderBy = array("wallettransactions.id" => 'DESC');
                $extracondition = " (wallettransactions.status IS NOT NULL OR wallettransactions.status!='')";
                $resultQuery = $this->model->selectQuery($orderColumns, $tableName, array(), $cond, $orderBy, array(), "", "", $extracondition, array());
                if ($resultQuery && count($resultQuery) > 0) {
                    $cutletDetails = $resultQuery;;
                }
                $clientOrderList[$i]['cutletDetails'] = $cutletDetails;
            }
            $finalResult['orders'] = $clientOrderList;
            return response()->json(["status" => 200, "totalOrders" => $totalOrders, "result" => $finalResult], 200);
        } else {
            return response()->json(["status" => 300, "message" => "Data not found."], 200);
        }
    }

    public function cancelOrder(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'orderCode' => 'required',
        ]);
        $random = rand(0, 999);
        $clientCode = $r->clientCode;
        $orderData = $this->model->selectDataByCode('restaurantordermaster', $r->orderCode);
        if ($orderData) {
            $orderCode = $orderData->code;
            $orderStatus = $orderData->orderStatus;
            $grandTotal = $orderData->grandTotal;
            $usedPoints = $orderData->usedPoints;
            $deliveryBoyCode = $orderData->deliveryBoyCode;
            $nowdate = date('Y-m-d H:i:s');
            $data = array('orderStatus' => "CAN", "editDate" => $nowdate, "deliveryBoyCode" => "", 'editID' => $clientCode);
            $passresult = $this->model->doEditWithField($data, 'restaurantordermaster', 'code', $orderCode);
            if ($passresult) {
                $checkPendingCutlet = $this->model->selectQuery(array("wallettransactions.code"), 'wallettransactions', array(), array("wallettransactions.isActive" => array("=", 1), "wallettransactions.orderCode" => array("=", $orderCode), "wallettransactions.status" => array("=", "pending")));
                if ($checkPendingCutlet && count($checkPendingCutlet) > 0) {
                    foreach ($checkPendingCutlet as $pen) {
                        $this->model->deleteForeverFromField('code', $pen->code, 'wallettransactions');
                    }
                }
                $client_wallet = $this->model->selectQuery(array('clientmaster.walletPoints'), "clientmaster", array(), array("clientmaster.code" => array("=", $clientCode), "clientmaster.isActive" => array("=", 1)));
                if ($client_wallet && count($client_wallet) > 0) {
                    $previous_amt = $client_wallet[0]->walletPoints ?? 0;
                    $update_wallet = $previous_amt + $usedPoints;
                    $message = 'Refunded points for order ' . $orderCode;
                    $update['clientmaster.walletPoints'] = $update_wallet;
                    $this->model->doEditWithField($update, 'clientmaster', 'code', $clientCode);
                    $this->addWalletTransaction($clientCode, $orderCode, $usedPoints, $message, 'Add');
                    $dataNoti = array('title' => 'Point return to wallet', 'message' => 'Point used against order ' . $orderCode . ' return back to your wallet. New wallet : ' . $update_wallet, 'unique_id' => $orderCode, 'random_id' => $random, 'type' => 'Order');
                    $this->sendCustomerNotification($clientCode, $dataNoti);
                }
                $dataNoti = array('title' => 'Order Cancelled', 'message' => $orderCode . ' Order cancelled successfully', 'unique_id' => $orderCode, 'random_id' => $random, 'type' => 'Order');
                $this->sendCustomerNotification($clientCode, $dataNoti);
                $order_status = $this->model->selectQuery(array("restaurantorderstatusmaster.*"), "restaurantorderstatusmaster", array(), array("restaurantorderstatusmaster.statusSName" => array("=", "CAN")));
                if ($order_status && count($order_status) > 0) {
                    $order_status_record = $order_status[0];
                    $statusTitle = $order_status_record->messageTitle;
                    #replace $ template in title 
                    $statusDescription = $order_status_record->messageDescription;
                    $statusDescription = str_replace("$", $orderCode, $statusDescription);
                    $dataStatusChangeLine = array(
                        "orderCode" => $orderCode,
                        "statusPutCode" => $clientCode,
                        "statusLine" => 'CAN',
                        "reason" => 'Canceled By customer',
                        "statusTime" => date("Y-m-d H:i:s"),
                        "statusTitle" => $statusTitle,
                        "statusDescription" => $statusDescription,
                        "isActive" => 1
                    );
                    $bookLineResult = $this->model->addNew($dataStatusChangeLine, 'bookorderstatuslineentries', 'BOL');
                }
                return response()->json(["status" => 200, "message" => "Order Cancelled Successfully"], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Failed to cancel the order! Please Try Again."], 200);
            }
        } else {
            return response()->json(["status" => 300, "message" => "Invalid Order Code"], 200);
        }
    }

    public function cartTermsAndCondition()
    {
        $termsData = $this->model->selectQuery(array('termsAndConditions.id', 'termsAndConditions.code', 'termsAndConditions.type', 'termsAndConditions.text', 'termsAndConditions.isActive'), 'termsAndConditions', array(), array('termsAndConditions.type' => array('=', 'cart'), 'termsAndConditions.isActive' => array('=', 1)), array('termsAndConditions.id' => 'ASC'));
        if ($termsData && count($termsData)) {
            return response()->json(["status" => 200, "result" => $termsData], 200);
        } else {
            return response()->json(["status" => 300, "message" => "No data found"], 200);
        }
    }

    public function addWalletTransaction($clientCode, $orderCode, $point, $message, $type)
    {
        $walletTransactionData = array(
            "clientCode" => $clientCode,
            "orderCode" => $orderCode,
            "message" => $message,
            "type" => $type,
            "point" => $point,
            "isActive" => 1,
        );
        $walletTransactionResult = $this->model->addNewWithYear($walletTransactionData, "wallettransactions", "WT");
    }

    public function addCutlet($clientCode, $orderCode, $restaurantCode, $grandTotal, $shippingCharges)
    {
        $commissionPer  = 0;
        $restaurantData = $this->model->selectQuery(array('restaurant.commission'), 'restaurant', array(), array('restaurant.isActive' => array('=', 1), 'restaurant.code' => array('=', $restaurantCode)));
        if ($restaurantData && count($restaurantData) > 0) $commissionPer = $restaurantData[0]->commission;
        if ($commissionPer != 0 && $commissionPer != NULL && $commissionPer != '') {
            $extraCondition = " ($commissionPer between IFNULL(rewardpercentageslots.from,0) and IFNULL(rewardpercentageslots.to,0))";
            $getRewardSlot = $this->model->selectQuery(array('rewardpercentageslots.*'), 'rewardpercentageslots', array(), array('rewardpercentageslots.isActive' => array('=', 1)), [], [], 1, 0, $extraCondition);
            if ($getRewardSlot && count($getRewardSlot) > 0) {
                /*
                useless logic
                $newPer = $getRewardSlot[0]->minusValue;                
                if ($getRewardSlot[0]->isMinus == 1) {
                    $newPer = $commissionPer - $getRewardSlot[0]->minusValue;
                }
                */
                $cutletPercent = $getRewardSlot[0]->minusValue;
                $value = $grandTotal - $shippingCharges;
                if ($value > 0) {
                    $point = number_format((($value * $cutletPercent) / 100), 0, ".", "");
                    if ($point > 0) {
                        $walletTransactionData = array(
                            "clientCode" => $clientCode,
                            "orderCode" => $orderCode,
                            "message" => 'Points added against order ' . $orderCode,
                            "type" => 'Add',
                            "status" => "pending",
                            "point" => $point,
                            "isActive" => 1,
                        );
                        $Result = $this->model->addNewWithYear($walletTransactionData, "wallettransactions", "WT");
                    }
                }
            }
        }
    }

    public function getWalletAmount(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required'
        ]);
        $walletPoints = 0;
        $result = $this->model->selectQuery(DB::raw("ifnull(clientmaster.walletPoints,0) as walletPoints"), "clientmaster", array(), array("clientmaster.code" => array('=', $r->clientCode), "clientmaster.isActive" => array("=", 1)));
        if ($result && count($result) > 0) {
            $walletPoints = $result[0]->walletPoints;
            return response()->json(["status" => 200, "result" => $walletPoints], 200);
        } else {
            return response()->json(["status" => 300,  "message" => "No Data found", "result" => $walletPoints], 200);
        }
    }

    public function getCutletHistoryList(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'offset' => 'required'
        ]);
        $amount = $pendingPointSum = $walletPoint = 0;
        $offset = $r->offset;
        $whereArr["code"] = array("=", $r->clientCode);
        $result = $this->model->selectQuery(array("clientmaster.walletPoints"), "clientmaster", array(), $whereArr);
        if ($result && count($result) > 0) $walletPoint = $result[0]->walletPoints;

        $traArr['totalRecords'] = 0;
        $pendingCutletCount = 0;
        $traArr = [];
        $whereArr1["clientCode"] = array("=", $r->clientCode);
        $orderColumns = array("wallettransactions.code", "wallettransactions.orderCode", "wallettransactions.type", "wallettransactions.message", "wallettransactions.point", DB::raw("DATE_FORMAT(wallettransactions.addDate, '%d %b %Y %h:%i %p') as transactionDate"));
        $orderBy = array('wallettransactions.id' => 'DESC');
        $extraCondition = "IFNULL(wallettransactions.point,0)>'0' AND wallettransactions.status in ('success')";
        $result = $this->model->selectQuery($orderColumns, "wallettransactions", array(), $whereArr1, $orderBy, array(), 10, $offset, $extraCondition);

        if ($result && count($result) > 0) {
            //$traArr['totalRecords']=count($result);
            $traArr = $result;
        }
        DB::enableQueryLog();
        $extraCondition1 = " wallettransactions.point!=0 and wallettransactions.status='pending'";
        $result1 = $this->model->selectQuery($orderColumns, "wallettransactions", array(), $whereArr1, $orderBy, array(), '', '', $extraCondition1);
        if ($result1 && count($result1) > 0) {
            $pendingCutletCount = count($result1);
        }
        //$query_1 = DB::getQueryLog();
        //print_r($query_1);
        //exit();
        $res['clientPoints'] = $walletPoint;
        $res['pendingCutletCount'] = $pendingCutletCount;
        $res['transactionList'] = $traArr;
        return response()->json(["status" => 200, "result" => $res], 200);
    }

    public function getPendingCutletList(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'offset' => 'required'
        ]);
        $amount = $pendingPointSum = 0;
        $offset = $r->offset;
        $traArr['totalRecords'] = 0;
        $pendingCutletCount = 0;
        $traArr = [];
        $result1 = Wallet::select("wallettransactions.code", "wallettransactions.orderCode", "wallettransactions.type", "wallettransactions.message", "wallettransactions.point", DB::raw("DATE_FORMAT(wallettransactions.addDate, '%d %b %Y %h:%i %p') as transactionDate"))
            ->where('clientCode', $r->clientCode)
            ->whereRaw('( wallettransactions.point!=0 and wallettransactions.status="pending")')
            ->orderBy('wallettransactions.id', 'desc')
            ->offset($offset)
            ->limit(10)
            ->get();
        /*$whereArr["clientCode"] = array("=",);
        $orderColumns = array("wallettransactions.code","wallettransactions.orderCode","wallettransactions.type","wallettransactions.message","wallettransactions.point",DB::raw("DATE_FORMAT(wallettransactions.addDate, '%d %b %Y %h:%i %p') as transactionDate"));
        $orderBy = array('wallettransactions.id' => 'DESC');
		$extraCondition1 = " wallettransactions.point!=0 and wallettransactions.status='pending'";
        $result1 = $this->model->selectQuery($orderColumns,"wallettransactions",array(), $whereArr, $orderBy,array(),10,$offset,$extraCondition1);*/
        if ($result1 && count($result1) > 0) {
            foreach ($result1 as $res) {
                $pendingPointSum = $pendingPointSum + $res->point;
                $traArr[] = array('code' => $res->code, 'orderCode' => $res->orderCode, 'type' => $res->type, 'message' => $res->message, 'point' => $res->point, 'transactionDate' => $res->transactionDate);
            }
        }
        $res1['pendingPointSum'] = $pendingPointSum;
        $res1['transactionList'] = $traArr;
        return response()->json(["status" => 200, "result" => $res1], 200);
    }

    public function trackOrderById(Request $r)
    {
        $dataValidate = $r->validate([
            'orderCode' => 'required',
        ]);
        $orderCode = $r->orderCode;
        $track = [];

        $ar = $this->orderCheckStatus($orderCode, "PLC", "Placed");
        $track[] = $ar;

        $ar = $this->orderCheckStatus($orderCode, "PRE", "Preparing");
        $track[] = $ar;

        $ar = $this->orderCheckStatus($orderCode, "PUP", "On the Way");
        $track[] = $ar;

        //$ar = $this->orderCheckStatus($orderCode, "RCH", "Reached");
        //$track[] = $ar;

        $ar = $this->orderCheckStatus($orderCode, "DEL", "Delivered");
        $track[] = $ar;

        $ar = $this->orderCheckStatus($orderCode, "CAN", "Cancelled / Rejected");
        $track[] = $ar;

        $data['tracking_data'] = $track;
        return response()->json(["status" => 200, "message" => "Data found.", "result" => $data], 200);
    }


    public function orderCheckStatus($orderCode, $status, $fullStatus)
    {
        $orderBy = array();
        $extraCondition = '';
        if ($status == "CAN") {
            $condition = array("bookorderstatuslineentries.orderCode" => array("=", $orderCode));
            $extraCondition =  " (bookorderstatuslineentries.statusLine IN ('CAN','RJT'))";
        } else {
            $condition = array("bookorderstatuslineentries.statusLine" => array("=", $status), "bookorderstatuslineentries.orderCode" => array("=", $orderCode));
        }
        $Records = $this->model->selectQuery(array("bookorderstatuslineentries.*"), 'bookorderstatuslineentries', array(), $condition, array("bookorderstatuslineentries.id" => "ASC"), array(), '', '', $extraCondition);
        $ar = [];
        if ($Records && count($Records) > 0) {
            foreach ($Records as $s) {
                $ar['isActive'] = true;
                $ar['status'] = $status;
                $ar['statusTitle'] = $s->statusTitle;
                $ar['statusDescription'] = $s->statusDescription;
                $ar['statusDate'] = date('D, d M Y', strtotime($s->statusTime));
                $ar['statusTime'] = date('h:i A', strtotime($s->statusTime));
                $ar['statusDateTime'] = date('D, d M Y h:i A', strtotime($s->statusTime));
                break;
            }
        } else {
            $ar['isActive'] = false;
            $ar['status'] = $status;
            $ar['statusTitle'] = $fullStatus;
            $ar['statusDescription'] = $ar['statusDate'] = $ar['statusTime'] = $ar['statusDateTime'] = "";
        }
        return $ar;
    }

    public function getOrderHistoryListByTab(Request $r)
    {
        $dataValidate = $r->validate([
            'activeTab' => 'required',
            'clientCode' => 'required',
        ]);
        $clientCode = $r->clientCode;
        $activeTab = $r->activeTab;
        $limit = "";
        $offset = "";
        if (isset($r->offset) && $r->offset != "") {
            $offset = $r->offset;
            $limit = "10";
        }
        $orderStatusCondition = '';
        switch ($activeTab) {
            case 'active':
                $orderStatusCondition = " restaurantordermaster.orderStatus NOT IN ('CAN','DEL','RJT')";
                break;
            case 'delivered':
                $orderStatusCondition = " restaurantordermaster.orderStatus IN ('DEL')";
                break;
            case 'cancelled':
                $orderStatusCondition = " restaurantordermaster.orderStatus IN ('CAN','RJT')";
                break;
        }
        $tableName = "restaurantordermaster";
        $orderColumns = array(DB::raw("restaurantordermaster.code as orderCode,restaurantordermaster.deliveryBoyCode,usermaster.name as dBoyName,usermaster.latitude as dBoyLat,usermaster.longitude as dBoyLong,restaurantordermaster.restaurantCode,restaurantordermaster.totalPackgingCharges,restaurantordermaster.packagingType,restaurantordermaster.addressType,restaurantordermaster.tax,restaurantordermaster.discount,restaurantordermaster.subTotal,restaurantordermaster.shippingCharges as deliveryCharges,restaurantordermaster.usedPoints as useCutlets,restaurantordermaster.paymentmode,restaurantordermaster.address,restaurantordermaster.grandTotal as orderTotalPrice,restaurantordermaster.addDate as orderDate, restaurantorderstatusmaster.statusName as orderStatus, paymentstatusmaster.statusName as paymentStatus,restaurant.entityName,restaurant.address as pickUpAddress,restaurant.latitude as sourceLat,restaurant.longitude as sourceLong,restaurant.cityCode,restaurant.addressCode,citymaster.cityName,customaddressmaster.place,restaurantordermaster.latitude as destiLat,restaurantordermaster.longitude as destiLong,bookorderstatuslineentries.statusTime"));
        $cond = array('restaurantordermaster.paymentStatus' => array('=', 'PID'), 'restaurantordermaster.clientCode' => array("=", $clientCode), "restaurantordermaster.isActive" => array("=", 1));
        $orderBy = array("restaurantordermaster.id" => 'DESC');
        $join = array('usermaster' => array('restaurantordermaster.deliveryBoyCode', 'usermaster.code'), 'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus', 'restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus', 'paymentstatusmaster.statusSName'), "restaurant" => array("restaurantordermaster.restaurantCode", "restaurant.code"), "citymaster" => array("restaurant.cityCode", "citymaster.code"), "customaddressmaster" => array("restaurant.addressCode", "customaddressmaster.code"), "bookorderstatuslineentries" => array("restaurantordermaster.orderStatus", "bookorderstatuslineentries.statusLine"));
        $joinType = array('usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner', "restaurant" => "inner", "citymaster" => "left", "customaddressmaster" => "left", "bookorderstatuslineentries" => "left");
        if ($orderStatusCondition != '')  $orderStatusCondition .= ' and ';
        $orderStatusCondition .= " bookorderstatuslineentries.orderCode = restaurantordermaster.code";
        $Records = $this->model->selectQuery($orderColumns, $tableName, $join, $cond, $orderBy, array(), $limit, $offset, $orderStatusCondition, $joinType);
        $q = "";
        if ($Records) {
            $orders = [];
            $totalOrders = 0;
            foreach ($Records as $or) {
                $orderCode = $or->orderCode;
                $items = $this->model->selectQuery(DB::raw("ifnull(count(restaurantorderlineentries.orderCode),0) as itmcnt"), "restaurantorderlineentries", array(), array("restaurantorderlineentries.orderCode" => array("=", $orderCode)));
                if ($items && count($items) > 0) $itemCount = $items[0]->itmcnt;
                $or->item_count = $itemCount;
                $totalOrders++;
                $orders[] = $or;
            }
            $finalResult['orders'] = $orders;
            return response()->json(["status" => 200, "totalOrders" => $totalOrders, "result" => $finalResult], 200);
        } else {
            return response()->json(["status" => 300, "result" => $data, "message" => "No Data found"], 200);
        }
    }

    public function getOrderDetailsByOrderCode(Request $r)
    {
        $dataValidate = $r->validate([
            'clientCode' => 'required',
            'orderCode' => 'required',
        ]);
        $clientCode = $r->clientCode;
        $orderCode = $r->orderCode;
        $tableName = "restaurantordermaster";
        $orderColumns = array(DB::raw("restaurantordermaster.code as orderCode,restaurantordermaster.deliveryBoyCode,usermaster.name as dBoyName,usermaster.latitude as dBoyLat,usermaster.longitude as dBoyLong,restaurantordermaster.restaurantCode,restaurantordermaster.totalPackgingCharges,restaurantordermaster.packagingType,restaurantordermaster.addressType,restaurantordermaster.tax,restaurantordermaster.discount,restaurantordermaster.subTotal,restaurantordermaster.shippingCharges as deliveryCharges,restaurantordermaster.usedPoints as useCutlets,restaurantordermaster.paymentmode,restaurantordermaster.address,restaurantordermaster.grandTotal as orderTotalPrice,restaurantordermaster.addDate as orderDate, restaurantorderstatusmaster.statusName as orderStatus, paymentstatusmaster.statusName as paymentStatus,restaurant.entityName,restaurant.address as pickUpAddress,restaurant.latitude as sourceLat,restaurant.longitude as sourceLong,restaurant.cityCode,restaurant.addressCode,citymaster.cityName,customaddressmaster.place,restaurantordermaster.latitude as destiLat,restaurantordermaster.longitude as destiLong,bookorderstatuslineentries.statusTime"));
        $cond = array('restaurantordermaster.code' => array("=", $orderCode), 'restaurantordermaster.clientCode' => array("=", $clientCode), "restaurantordermaster.isActive" => array("=", 1));
        $orderBy = array("restaurantordermaster.id" => 'DESC');
        $join = array('usermaster' => array('restaurantordermaster.deliveryBoyCode', 'usermaster.code'), 'restaurantorderstatusmaster' => array('restaurantordermaster.orderStatus', 'restaurantorderstatusmaster.statusSName'), 'paymentstatusmaster' => array('restaurantordermaster.paymentStatus', 'paymentstatusmaster.statusSName'), "restaurant" => array("restaurantordermaster.restaurantCode", "restaurant.code"), "citymaster" => array("restaurant.cityCode", "citymaster.code"), "customaddressmaster" => array("restaurant.addressCode", "customaddressmaster.code"), "bookorderstatuslineentries" => array("restaurantordermaster.orderStatus", "bookorderstatuslineentries.statusLine"));
        $joinType = array('usermaster' => 'left', 'restaurantorderstatusmaster' => 'inner', 'paymentstatusmaster' => 'inner', "restaurant" => "inner", "citymaster" => "left", "customaddressmaster" => "left", "bookorderstatuslineentries" => "left");
        $extracondition = " bookorderstatuslineentries.orderCode = restaurantordermaster.code";
        $resultQuery = $this->model->selectQuery($orderColumns, $tableName, $join, $cond, $orderBy, array(), "", "", $extracondition, $joinType);
        $imageArray = array();
        if ($resultQuery && count($resultQuery) > 0) {
            $totalOrders = sizeof($resultQuery);
            $clientOrderList = json_decode(json_encode($resultQuery), true);
            for ($i = 0; $i < $totalOrders; $i++) {
                $clientOrderList[$i]['statusTime'] = date('d-m-Y h:i A', strtotime($clientOrderList[$i]['statusTime']));
                $addonArray = $resultAddonArray = [];
                $linetableName = "restaurantorderlineentries";
                $lineorderColumns = array(DB::raw("restaurantorderlineentries.restaurantItemCode,restaurantorderlineentries.quantity,restaurantorderlineentries.priceWithQuantity as priceWithQuantity,restaurantorderlineentries.addons,restaurantorderlineentries.addonsCode,restaurantitemmaster.itemName,restaurantorderlineentries.itemPackagingCharges"));
                $linecond = array("restaurantorderlineentries.orderCode" => array("=", $clientOrderList[$i]['orderCode']));
                $lineorderBy = array('restaurantorderlineentries' . ".id" => 'ASC');
                $linejoin = array('restaurantitemmaster' => array('restaurantorderlineentries.restaurantItemCode', 'restaurantitemmaster.code'));
                $linejoinType = array('restaurantitemmaster' => 'inner');
                $orderProductRes = $this->model->selectQuery($lineorderColumns, $linetableName, $linejoin, $linecond, $lineorderBy, array(), "", "", "", $linejoinType);
                if ($orderProductRes) {
                    foreach ($orderProductRes as $subItem) {
                        $addonArray = $subItem;
                        $resultArr = [];
                        /*if($subItem->addonsCode!='' && $subItem->addonsCode!=NULL){
							$subItem->addonsCode = rtrim($subItem->addonsCode,', ');
							$savedaddonsCodes = explode(', ',$subItem->addonsCode);
							foreach($savedaddonsCodes as $addon){
								$categoryArr=[];
								$joinType1 = array('customizedcategory' => 'inner');
								$condition1 = array('customizedcategorylineentries.code'=>array("=",$addon));
								$join1 = array('customizedcategory' => array("customizedcategory.code","customizedcategorylineentries.customizedCategoryCode"));
								$getAddonDetails = $this->model->selectQuery(array("customizedcategory.categoryTitle","customizedcategory.categoryType","customizedcategorylineentries.subCategoryTitle","customizedcategorylineentries.price"),"customizedcategorylineentries",$join1,$condition1, array(), array(),"","","",$joinType1);
								if($getAddonDetails && count($getAddonDetails)>0){
									$categoryArr = $getAddonDetails[0];
								}
								$resultArr[]=$categoryArr;
							}
						}
						$addonArray->addonsDetails=$resultArr;*/
                        $resultAddonArray[] = $addonArray;
                    }
                }
                $clientOrderList[$i]['orderedItems'] = $resultAddonArray;
                $cutletDetails = array();
                $tableName = "wallettransactions";
                $orderColumns = array("wallettransactions.id", "wallettransactions.code", "wallettransactions.orderCode", "wallettransactions.type", "wallettransactions.message", "wallettransactions.point", "wallettransactions.status", DB::raw("DATE_FORMAT(wallettransactions.addDate, '%d %b %Y %h:%i %p') as transactionDate"));
                $cond = array('wallettransactions.clientCode' => array("=", $clientCode), 'wallettransactions.orderCode' => array("=", $orderCode));
                $orderBy = array("wallettransactions.id" => 'DESC');
                $extracondition = " (wallettransactions.status IS NOT NULL OR wallettransactions.status!='')";
                $resultQuery = $this->model->selectQuery($orderColumns, $tableName, array(), $cond, $orderBy, array(), "", "", $extracondition, array());
                if ($resultQuery && count($resultQuery) > 0) {
                    $cutletDetails = $resultQuery;;
                }
                $clientOrderList[$i]['cutletDetails'] = $cutletDetails;
            }
            $finalResult['orders'] = $clientOrderList;
            return response()->json(["status" => 200, "totalOrders" => $totalOrders, "result" => $finalResult], 200);
        } else {
            return response()->json(["status" => 300, "message" => "Data not found."], 200);
        }
    }

    /*
    public function getSearchListByTab(Request $r){
		 $dataValidate=$r->validate([
			'activeTab' => 'required',
			'cityCode' => 'required',
			'keyword' => 'required'
        ]);
		$activeTab = $r->activeTab;
		$cityCode = $r->cityCode;
		$data=array();
		if($activeTab=='menuitem'){
			$data = $this->getMenuItemListByKeyword($r->keyword,$r->cityCode);
		}elseif($activeTab=='restaurant'){
			$data = $this->getVendorListByKeyword($r->keyword,$r->cityCode);
		}
		return response()->json(["status" => 200,"result"=>$data], 200);
	}
	
	public function getVendorListByKeyword($keyword,$cityCode){
		$data=array();
		DB::enableQueryLog();
		$currentDate = date('Y-m-d');
		$table = "restaurant";
		$orderColumns = array("restaurant.*","entitycategory.entityCategoryName","citymaster.cityName","customaddressmaster.place","restaurantoffer.code as offerCode","restaurantoffer.restaurantCode as restaurantCode","restaurantoffer.couponCode","restaurantoffer.offerType",DB::raw("ifnull(GREATEST(ifnull(MAX(restaurantoffer.discount),0),ifnull(MAX(restaurantoffer.flatAmount),0)),'') as discount"),"restaurantoffer.minimumAmount","restaurantoffer.perUserLimit","restaurantoffer.startDate","restaurantoffer.endDate","restaurantoffer.capLimit","restaurantoffer.termsAndConditions","restaurantoffer.isAdminApproved as vAdminapproved","restaurantoffer.isActive as status");
		$condition = array("restaurant.cityCode"=>array("=",$cityCode),"restaurant.isActive" => array("=",1));
		$orderBy = array("restaurant.isServiceable"=> "DESC" , "restaurant.id"=> "ASC");
		$join = array("entitycategory" => array("restaurant.entitycategoryCode","entitycategory.code"),"restaurantcuisinelineentries"=>array("restaurant.code","restaurantcuisinelineentries.vendorCode"),"citymaster"=>array("restaurant.cityCode","citymaster.code"),"customaddressmaster"=>array("restaurant.addressCode","customaddressmaster.code"),"restaurantoffer"=>array("restaurant.code","restaurantoffer.restaurantCode"));
		$joinType = array("entitycategory" => "inner","restaurantcuisinelineentries"=>"left","citymaster"=>"left","customaddressmaster"=>"left","restaurantoffer"=>"left");
		$groupBy = array('restaurant.code');
		$like = array('restaurant.entityName' => $keyword);
		$Records = $this->model->selectQuery($orderColumns, $table, $join,$condition, $orderBy,$like, 10,"","");
		 $query_1 = DB::getQueryLog();
      // print_r($query_1);
	     //exit();
		if ($Records) {
			$data =array();
			foreach ($Records as $r) {  
				/* get cuisines served by restaurant
				$cuisinesList = "";
				$table1 = "cuisinemaster";
				$orderColumns1 = array(DB::raw("GROUP_CONCAT(cuisinemaster.cuisineName) as cuisines"));
				$condition1 = array("restaurantcuisinelineentries.vendorCode" => array("=",$r->code), "cuisinemaster.isActive" => array("=",1));
				$orderBy1 = array();
				$join1 = array("restaurantcuisinelineentries" => array("cuisinemaster.code","restaurantcuisinelineentries.cuisineCode"));
				$joinType1 = array("restaurantcuisinelineentries" => "inner");
				$like1 = array('cuisinemaster.cuisineName' => $keyword);
				$cuisineRecords = $this->model->selectQuery($orderColumns1, $table1, $join1,$condition1, $orderBy1,$like1,"","","" , $joinType1);
				if ($cuisineRecords && count($cuisineRecords)>0) {
					$cuisinesList = $cuisineRecords[0]->cuisines;
				}

				$vendorar['code'] = $r->code;
				$vendorar['entityName'] = $r->entityName;
				$vendorar['firstName'] = $r->firstName;
				$vendorar['middleName'] = $r->middleName;
				$vendorar['lastName'] = $r->lastName;
				$vendorar['latitude'] = $r->latitude;
				$vendorar['longitude'] = $r->longitude;
				$vendorar['isServiceable'] = $r->isServiceable;
				if ($r->entityImage != "") {
					$path = 'uploads/restaurantimage/'. $r->entityImage;
					if (file_exists($path)) $vendorar['entityImage'] = $path;
					else $vendorar['entityImage']  = "noimage";
				} else $vendorar['entityImage']  = "noimage";

				$vendorar['address'] = $r->address;
				$vendorar['packagingType'] = $r->packagingType;
				$vendorar['cartPackagingPrice'] = $r->cartPackagingPrice;
				$vendorar['gstApplicable'] = $r->gstApplicable;
				$vendorar['gstPercent'] = $r->gstPercent;
				$vendorar['ownerContact'] = $r->ownerContact;
				$vendorar['entityContact'] = $r->entityContact;
				$vendorar['email'] = $r->email;
				$vendorar['entityCategoryName'] = $r->entityCategoryName;
				$vendorar['fssaiNumber'] = $r->fssaiNumber;
				$vendorar['cityName'] = $r->cityName;
				$vendorar['aresName'] = $r->place;
				$vendorar['cuisinesList'] = $cuisinesList;
				$discount = array();
                if ($r->discount != '' && $r->vAdminapproved == 1 && $r->startDate <= $currentDate && $r->endDate >= $currentDate && $r->status == 1) {
                      $discount['offerCode'] = $r->offerCode;
                       $discount['vendorCode'] = $r->restaurantCode;
                       $discount['coupanCode'] = $r->couponCode;
					   $discount['offerType'] = $r->offerType;
					   $sign='';
						if($r->offerType==='flat'){
							$sign=' ₹';
						}else{
							$sign=' %';
						}
						$discount['discount'] = $r->discount.$sign;
                       $discount['minimumAmount'] = $r->minimumAmount;
                       $discount['perUserLimit'] = $r->perUserLimit;
                       $discount['startDate'] = date('d-m-Y h:i A', strtotime($r->startDate));
                       $discount['endDate'] = date('d-m-Y h:i A', strtotime($r->endDate));
                       $discount['capLimit'] = $r->capLimit;
                       $discount['termsAndConditions'] = $r->termsAndConditions;
                }
				$vendorar['discount'] = $discount;
				$dayofweek= strtolower(date('l'));
				
				$timeQuery = "select restauranthours.code as hourCode,time_format(restauranthours.fromTime,'%h:%i %p') as fromTime,time_format(restauranthours.toTime,'%h:%i %p') as toTime from restauranthours where restauranthours.restaurantCode = '".$r->code."' and restauranthours.weekDay='".$dayofweek."'";
				$timeData = DB::select($timeQuery);	
				if($timeData) {
					$vendorar['vendorHours'] = $timeData;
				} else {
					$vendorar['vendorHours'] = array();
				}
				$vendorar['ratingDetails']=[];
				$sum=0;
				$ratingfinalArray=[];
				$orderColumns1 = array("rating.id","clientmaster.name","rating.clientCode","rating.orderCode","rating.restaurantCode","rating.rating","rating.review","rating.addDate");
				$condition1=array("rating.restaurantCode"=>array("=",$r->code));
				$join1=array("clientmaster"=>array("clientmaster.code","rating.clientCode"));
				$extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
				$ratingDetails = $this->model->selectQuery($orderColumns1, 'rating',$join1,$condition1, array(),array(),"","",$extraCondition1);
				if (!empty($ratingDetails) && count($ratingDetails)>0) {
					foreach($ratingDetails as $rating){
						$sum = $sum+$rating->rating;
						$dFormat = Carbon::createFromFormat('Y-m-d H:i:s', $rating->addDate);
						$ratingDate = $dFormat->format('d-m-Y H:i:s');
						$ratingArr['id'] = $rating->id;
						$ratingArr['orderCode'] =  $rating->orderCode;
						$ratingArr['clientCode'] =  $rating->clientCode;
						$ratingArr['clientName'] =  $rating->name;
						$ratingArr['rating'] =$rating->rating;
						$ratingArr['review'] = $rating->review;
						$ratingArr['date'] = $ratingDate;
						$ratingOrderArray[]=$ratingArr;
						
					}
					$ratingfinalArray['avgRating'] = $sum;
					$ratingfinalArray['ratingOrderArray'] = $ratingOrderArray;
				}
				 $vendorar['ratingDetails']=$ratingfinalArray;
				
			 	$data[] = $vendorar;
			 
			}
		}
		return $data;
	}
	
	public function getMenuItemListByKeyword($keyword,$cityCode){
         DB::enableQueryLog();
		
		$condition1 = array('menucategory.isActive' => array("=",1));
		$orderBy1 = array('menucategory.priority' => 'ASC');
		$Records = $this->model->selectQuery(array("menucategory.*"), "menucategory",array(), $condition1, $orderBy1);
		if (!empty($Records)) {
			$data = array();
			foreach ($Records as $ra) {
				$mainitemArray = array();
				$addonArray = array();
				$choiceArray = array();
				$maincount = 0;
				$catCode = $ra->code;
				$catName = $ra->menuCategoryName;
				$orderColumns2 = array("restaurantitemmaster.*","restaurant.entityName","restaurant.isServiceable as vendorIsServiceable");
				$condition2 = array('restaurantitemmaster.isActive' => array("=",1), "restaurant.cityCode" => array("=",$cityCode),"restaurantitemmaster.menuCategoryCode" => array("=",$catCode), "restaurantitemmaster.isAdminApproved" => array("=",1));
				$orderBy2 = array('restaurantitemmaster.id' => 'DESC');
				$join2 = array("restaurant" => array("restaurantitemmaster.restaurantCode","restaurant.code"));
				$extraCondition2 = " ((restaurantitemmaster.isDelete=0 OR restaurantitemmaster.isDelete IS NULL) or (restaurantitemmaster.menuSubCategoryCode is Null or restaurantitemmaster.menuSubCategoryCode=''))";
				$like2 = array('restaurantitemmaster.itemName' => $keyword);
				$itemRecords = $this->model->selectQuery($orderColumns2, "restaurantitemmaster",$join2, $condition2, $orderBy2,$like2,"","",$extraCondition2);
				 $query_1 = DB::getQueryLog();
       // print_r($query_1);
		//exit();
				if (!empty($itemRecords)) {
					foreach ($itemRecords as $r) {
						$vendorItemCode = $r->code;
						$CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory',array(), array('customizedcategory.isEnabled'=>array("=",1),'customizedcategory.restaurantItemCode' => array("=",$vendorItemCode), 'customizedcategory.categoryType' => array("=",'addon')));
						if (!empty($CCRecordsAddon)) {
							foreach ($CCRecordsAddon as $ccra) {
								$customizedCategoryCode = $ccra->code;
								$categoryTitle = $ccra->categoryTitle;
								$CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries',array(), array('customizedcategorylineentries.isEnabled'=>array("=",1),'customizedcategorylineentries.customizedCategoryCode' => array("=",$customizedCategoryCode)));
								$addonCustomizedCategoryArray = array();
								if (!empty($CCRecordsAddonLine)) {
									$addonCustomizedCategoryArray = array();
									foreach ($CCRecordsAddonLine as $ccraL) {
											$subCategoryTitle = $ccraL->subCategoryTitle;
											$price = $ccraL->price;
											$addonCustomizedCategoryArray[] = array(
												"lineCode" => $ccraL->code,
												"subCategoryTitle" => $subCategoryTitle,
												"price" => $price,
											);
										}
									}
									$addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
								}
							}

							$CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory',array(), array('customizedcategory.isEnabled'=>array("=",1),'customizedcategory.restaurantItemCode' => array("=",$vendorItemCode), 'customizedcategory.categoryType' => array("=",'choice')));
							if (!empty($CCRecordsChoice)) {
								foreach ($CCRecordsChoice as $ccrc) {
									$customizedCategoryCode = $ccrc->code;
									$categoryTitle = $ccrc->categoryTitle;
									$CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries',array(), array('customizedcategorylineentries.isEnabled'=>array("=",1),'customizedcategorylineentries.customizedCategoryCode' => array("=",$customizedCategoryCode)));
									$choiceCustomizedCategoryArray = array();
									if (!empty($CCRecordsChoiceLine)) {
										$choiceCustomizedCategoryArray = array();
										foreach ($CCRecordsChoiceLine as $ccrcL) {
											$subCategoryTitle = $ccrcL->subCategoryTitle;
											$price = $ccrcL->price;
											$choiceCustomizedCategoryArray[] = array(
												"lineCode" => $ccrcL->code,
												"subCategoryTitle" => $subCategoryTitle,
												"price" => $price,
											);
										}
									}
									$choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
								}
							}

							$path = url('uploads/restaurent_no_image.png');
							if ($r->itemPhoto != "") {
								$filepath = url('uploads/restaurant/' . $r->restaurantCode . '/restaurantitemimage/' . $r->itemPhoto);
								if(file_exists($filepath))  $path =  $filepath;
							}
							$mainitemArray[] = array(
								"restaurantCode" => $r->restaurantCode,
								"itemCode" => $r->code,
								"itemName" => $r->itemName,
								"itemDescription" => $r->itemDescription,
								"salePrice" => $r->salePrice,
								"itemPhoto" => $path,
								"vendorName" => $r->entityName,
								"vendorIsServiceable"=>$r->vendorIsServiceable,
								"isServiceable" => $r->itemActiveStatus,
								"cuisineType" => $r->cuisineType,
								"isActive" => $r->isActive,
								"maxOrderQty" => $r->maxOrderQty,
								"itemPackagingPrice" => $r->itemPackagingPrice,
								"addons" => $addonArray,
								"choice" => $choiceArray,
							);
							$maincount++;
						}
					}

					$subCategoryItemArray = array();

					$tableName3 = "menusubcategory";
					$orderColumns3 = array("menusubcategory.*");
					$condition3 = array('menusubcategory.isActive' => array("=",1), "menusubcategory.menuCategoryCode" => array("=",$catCode));
					$orderBy3 = array('menusubcategory.id' => 'ASC');
					$subCateRecords = $this->model->selectQuery($orderColumns3, "menusubcategory", array(),$condition3, $orderBy3);
					if (!empty($subCateRecords)) {
						$subcount	= sizeof($subCateRecords);
						foreach ($subCateRecords as $subrow) {
							$subCategoryCode = $subrow->code;
							$subCategoryName = $subrow->menuSubCategoryName;

							$tableName4 = "restaurantitemmaster";
							$orderColumns4 = array("restaurantitemmaster.*","restaurant.entityName","restaurant.isServiceable as vendorIsServiceable");
							$condition4 = array('restaurantitemmaster.isActive' => array("=",1), "restaurant.cityCode" => array("=",$cityCode), "restaurantitemmaster.menuSubCategoryCode" => array("=",$subCategoryCode), "restaurantitemmaster.isAdminApproved" => array("=",1));
							$orderBy4 = array('restaurantitemmaster.id' => 'DESC');
							$join4 = array("restaurant" => array("restaurantitemmaster.restaurantCode","restaurant.code"), "menusubcategory" => array("restaurantitemmaster.menuSubCategoryCode","menusubcategory.code"));
							$extraCondition4 = " ((restaurantitemmaster.isDelete=0 OR restaurantitemmaster.isDelete IS NULL) or (restaurantitemmaster.menuSubCategoryCode is Null or restaurantitemmaster.menuSubCategoryCode=''))";
							$like4 = array('restaurantitemmaster.itemName' => $keyword);
							$Records = $this->model->selectQuery($orderColumns4, $tableName4,$join4, $condition4, $orderBy4,$like4,"","",$extraCondition4);
							if (!empty($Records)) {
								$itemArray = array();
								$count = sizeof($Records);
								foreach ($Records as $r) {
									$vendorItemCode = $r->code;
									$CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(),array('customizedcategory.isEnabled'=>array("=",1),'customizedcategory.restaurantItemCode' => array("=",$vendorItemCode), 'customizedcategory.categoryType' => array("=",'addon')));
									if (!empty($CCRecordsAddon)) {
										foreach ($CCRecordsAddon as $ccra) {
											$customizedCategoryCode = $ccra->code;
											$categoryTitle = $ccra->categoryTitle;
											$CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(),array('customizedcategorylineentries.isEnabled'=>array("=",1),'customizedcategorylineentries.customizedCategoryCode' => array("=",$customizedCategoryCode)));
											$addonCustomizedCategoryArray = array();
											if (!empty($CCRecordsAddonLine)) {
												foreach ($CCRecordsAddonLine as $ccraL) {
													$subCategoryTitle = $ccraL->subCategoryTitle;
													$price = $ccraL->price;
													$addonCustomizedCategoryArray[] = array(
														"lineCode" => $ccraL->code,
														"subCategoryTitle" => $subCategoryTitle,
														"price" => $price,
													);
												}
											}
											$addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
										}
									}

									$CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(),array('customizedcategory.isEnabled'=>array("=",1),'customizedcategory.restaurantitemCode' => array("=",$vendorItemCode), 'customizedcategory.categoryType' => array("=",'choice')));
									if (!empty($CCRecordsChoice)) {
										foreach ($CCRecordsChoice as $ccrc) {
											$customizedCategoryCode = $ccrc->code;
											$categoryTitle = $ccrc->categoryTitle;
											$CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(),array('customizedcategorylineentries.isEnabled'=>array("=",1),'customizedcategorylineentries.customizedCategoryCode' => array("=",$customizedCategoryCode)));
											$choiceCustomizedCategoryArray = array();
											if (!empty($CCRecordsChoiceLine)) {
												foreach ($CCRecordsChoiceLine as $ccrcL) {
													$subCategoryTitle = $ccrcL->subCategoryTitle;
													$price = $ccrcL->price;
													$choiceCustomizedCategoryArray[] = array(
														"lineCode" => $ccrcL->code,
														"subCategoryTitle" => $subCategoryTitle,
														"price" => $price,
													);
												}
											}
											$choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
										}
									} 
									$path = url('uploads/restaurent_no_image.png');
									if ($r->itemPhoto != "") {
										$filepath = url('uploads/restaurant/' . $r->restaurantCode . '/restaurantitemimage/' . $r->itemPhoto);
										if(file_exists($filepath))  $path =  $filepath;
									}
									$itemArray[] = array(
										"restaurantCode" => $r->restaurantCode,
										"itemCode" => $r->code,
										"itemName" => $r->itemName,
										"itemDescription" => $r->itemDescription,
										"salePrice" => $r->salePrice,
										"itemPhoto" => $path,
										"vendorName" => $r->entityName,
										"vendorIsServiceable"=>$r->vendorIsServiceable,
										"cuisineType" => $r->cuisineType,
										"isActive" => $r->isActive,
										"isServiceable" => $r->itemActiveStatus,
										"maxOrderQty" => $r->maxOrderQty,
										"itemPackagingPrice" => $r->itemPackagingPrice,
										"addons" => $addonArray,
										"choice" => $choiceArray,
									);
									$maincount++;
								}

								$subCategoryItemArray[] = array("subCategoryCode" => $subCategoryCode, "subCategoryName" => $subCategoryName, "count" => $count, "itemList" => $itemArray);
							}
						}
					}
					if ($maincount > 0) {
						$data[] = array("menuCategoryCode" => $catCode, "count" => $maincount, "menuCategoryName" => $catName, "itemList" => $mainitemArray, "subCategoryList" => $subCategoryItemArray);
					}
				}
			
			}
			return $data;
	}*/

    public function getDishesByKeyword(Request $req)
    {
        $dataValidate = $req->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'keyword' => 'required',
            'offset' => 'required'
        ]);

        DB::enableQueryLog();
        $restaurantCode = $req->restaurantCode;
        $cuisineType = $req->cuisineType;
        $keyword = $req->keyword;
        //	$cityCode = $req->cityCode;

        $data = array();
        $limit = 10;
        $geoLocation = ",ROUND( 6371 * acos( cos( radians(" . $req->latitude . ") ) * cos( radians( restaurant.latitude ) ) * cos( radians( restaurant.longitude ) - radians(" . $req->longitude . ") ) + sin( radians(" . $req->latitude . ") ) * sin(radians(restaurant.latitude)) ) ) AS distance ";
        $having = " HAVING distance <= 15";

        $query = "select `restaurantitemmaster`.*, `tagmaster`.`tagTitle`, `tagmaster`.`tagColor`, `restaurant`.`entityName`, `restaurant`.`isServiceable` as `vendorIsServiceable` $geoLocation from `restaurantitemmaster`";
        $query .= " inner join `restaurant` on `restaurantitemmaster`.`restaurantCode` = `restaurant`.`code` left join `tagmaster` on `tagmaster`.`code` = `restaurantitemmaster`.`tagCode`";
        $query .= " where `restaurantitemmaster`.`isActive` = 1  and `restaurantitemmaster`.`isAdminApproved` = 1 and  (restaurantitemmaster.isDelete=0 OR restaurantitemmaster.isDelete IS NULL) ";
        $query .= "  and (`restaurantitemmaster`.`itemName` LIKE '%$keyword%') $having order by `restaurantitemmaster`.`id` desc limit $limit offset $req->offset";
        // 		$orderColumns2 = array("restaurantitemmaster.* ","tagmaster.tagTitle","tagmaster.tagColor","restaurant.entityName","restaurant.isServiceable as vendorIsServiceable ");
        // 		$condition2 = array('restaurantitemmaster.isActive' => array("=",1), "restaurant.cityCode" => array("=",$cityCode),"restaurant.code" => array("=",$restaurantCode), "restaurantitemmaster.cuisineType" => array("=",$cuisineType),"restaurantitemmaster.isAdminApproved" => array("=",1));
        // 		$orderBy2 = array('restaurantitemmaster.id' => 'DESC');
        // 		$join2 = array("restaurant" => array("restaurantitemmaster.restaurantCode","restaurant.code"),'tagmaster'=>array('tagmaster.code','restaurantitemmaster.tagCode'));
        // 		$jointType2=array('restaurant'=>'inner','tagmaster'=>'left');
        // 		$extraCondition2 = " (restaurantitemmaster.isDelete=0 OR restaurantitemmaster.isDelete IS NULL)  ";
        // 		$like2 = array('restaurantitemmaster.itemName' => $keyword);
        // 		$limit=10;
        // 		$offset=$req->offset;

        // 		$itemRecords = $this->model->selectQuery($orderColumns2, "restaurantitemmaster",$join2, $condition2, $orderBy2,$like2,$limit,$offset,$extraCondition2,$jointType2);
        $itemRecords = DB::select($query);
        $mainitemArray = [];
        if ($keyword != '') {
            if (!empty($itemRecords)) {
                foreach ($itemRecords as $r) {
                    $addonArray = [];
                    $choiceArray = [];
                    $cuisinesList = "";
                    $cuisineRecords = DB::table("cuisinemaster")
                        ->select(DB::raw("ifnull(GROUP_CONCAT(cuisinemaster.cuisineName SEPARATOR ','),'') as `cuisines`"))
                        ->join('restaurantcuisinelineentries', 'restaurantcuisinelineentries.cuisineCode', 'cuisinemaster.code')
                        ->where('restaurantcuisinelineentries.vendorCode', $r->restaurantCode)
                        ->where('cuisinemaster.isActive', 1)
                        ->get();
                    if ($cuisineRecords && count($cuisineRecords) > 0) {
                        foreach ($cuisineRecords as $cu) {
                            $cuisinesList = $cu->cuisines;
                        }
                    }
                    $vendorItemCode = $r->code;
                    $CCRecordsAddon = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'addon')));
                    if (!empty($CCRecordsAddon)) {
                        foreach ($CCRecordsAddon as $ccra) {
                            $customizedCategoryCode = $ccra->code;
                            $categoryTitle = $ccra->categoryTitle;
                            $CCRecordsAddonLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                            $addonCustomizedCategoryArray = array();
                            if (!empty($CCRecordsAddonLine)) {
                                $addonCustomizedCategoryArray = array();
                                foreach ($CCRecordsAddonLine as $ccraL) {
                                    $subCategoryTitle = $ccraL->subCategoryTitle;
                                    $price = $ccraL->price;
                                    $addonCustomizedCategoryArray[] = array(
                                        "lineCode" => $ccraL->code,
                                        "subCategoryTitle" => $subCategoryTitle,
                                        "price" => $price,
                                    );
                                }
                            }
                            $addonArray[] = ['addonTitle' => $categoryTitle, 'addonCode' => $customizedCategoryCode, 'addonList' => $addonCustomizedCategoryArray];
                        }
                    }

                    $CCRecordsChoice = $this->model->selectQuery(array('customizedcategory.*'), 'customizedcategory', array(), array('customizedcategory.isEnabled' => array("=", 1), 'customizedcategory.restaurantItemCode' => array("=", $vendorItemCode), 'customizedcategory.categoryType' => array("=", 'choice')));
                    if (!empty($CCRecordsChoice)) {
                        foreach ($CCRecordsChoice as $ccrc) {
                            $customizedCategoryCode = $ccrc->code;
                            $categoryTitle = $ccrc->categoryTitle;
                            $CCRecordsChoiceLine = $this->model->selectQuery(array('customizedcategorylineentries.*'), 'customizedcategorylineentries', array(), array('customizedcategorylineentries.isEnabled' => array("=", 1), 'customizedcategorylineentries.customizedCategoryCode' => array("=", $customizedCategoryCode)));
                            $choiceCustomizedCategoryArray = array();
                            if (!empty($CCRecordsChoiceLine)) {
                                $choiceCustomizedCategoryArray = array();
                                foreach ($CCRecordsChoiceLine as $ccrcL) {
                                    $subCategoryTitle = $ccrcL->subCategoryTitle;
                                    $price = $ccrcL->price;
                                    $choiceCustomizedCategoryArray[] = array(
                                        "lineCode" => $ccrcL->code,
                                        "subCategoryTitle" => $subCategoryTitle,
                                        "price" => $price,
                                    );
                                }
                            }
                            $choiceArray[] = ['choiceTitle' => $categoryTitle, 'choiceCode' => $customizedCategoryCode, 'choiceList' => $choiceCustomizedCategoryArray];
                        }
                    }
                    $path = url('uploads/restaurent_no_image.png');
                    if ($r->itemPhoto != "") {
                        $filepath = url('uploads/restaurant/restaurantitemimage/' . $r->restaurantCode . '/' . $r->itemPhoto);
                        if (file_exists($filepath))  $path =  $filepath;
                    }
                    $tagTitle = '';
                    if ($r->tagTitle != null) {
                        $tagTitle = $r->tagTitle;
                    }
                    $tagColor = '';
                    if ($r->tagColor != null) {
                        $tagColor = $r->tagColor;
                    }
                    $sum = 0;
                    $count = 0;
                    $sum1 = 0;
                    $avgRating = 0;
                    $ratingfinalArray = [];
                    $orderColumns1 = array("rating.id", "clientmaster.name", "rating.clientCode", "rating.orderCode", "rating.restaurantCode", "rating.rating", "rating.review", "rating.addDate", "rating.isAccept");
                    $condition1 = array("rating.restaurantCode" => array("=", $r->restaurantCode), "rating.isAccept" => array("=", 1));
                    $join1 = array("clientmaster" => array("clientmaster.code", "rating.clientCode"));
                    $extraCondition1 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                    $ratingDetails = $this->model->selectQuery($orderColumns1, 'rating', $join1, $condition1, array(), array(), "", "", $extraCondition1);
                    if (!empty($ratingDetails) && count($ratingDetails) > 0) {
                        foreach ($ratingDetails as $rating) {
                            $sum = $sum + $rating->rating;
                            $count++;
                        }
                        $orderColumns2 = array(DB::raw("ifnull(sum(rating.rating),0) as rating"), DB::raw("ifnull(count(id),0) as count"));
                        $condition2 = array("rating.restaurantCode" => array("=", $r->restaurantCode), "rating.isAccept" => array("=", 1));
                        $extraCondition2 = " (rating.deliveryBoyCode='' or rating.deliveryBoyCode IS NULL)";
                        $groupBy2 = array("rating.rating");
                        $rating1 = $this->model->selectQuery($orderColumns2, 'rating', array(), $condition2, array(), array(), "", "", $extraCondition1, array(), $groupBy2);
                        if (!empty($rating1) && count($rating1) > 0) {
                            foreach ($rating1 as $ra) {
                                $sum1 = $sum1 + ($ra->rating * $ra->count);
                            }
                        }
                        $avgRating = round($sum1 / $sum, 1);
                    }

                    $mainitemArray[] = array(
                        "restaurantCode" => $r->restaurantCode,
                        "restaurantName" => $r->entityName,
                        "vendorIsServiceable" => $r->vendorIsServiceable,
                        "cuisinesList" => $cuisinesList,
                        "restaurantAvgRating" => strval($avgRating),
                        "itemCode" => $r->code,
                        "itemName" => $r->itemName,
                        "itemDescription" => $r->itemDescription,
                        "itemTag" => $tagTitle,
                        "itemTagColor" => $tagColor,
                        "salePrice" => $r->salePrice,
                        "itemPhoto" => $path,
                        "isServiceable" => $r->itemActiveStatus,
                        "cuisineType" => $r->cuisineType,
                        "isActive" => $r->isActive,
                        "maxOrderQty" => $r->maxOrderQty,
                        "itemPackagingPrice" => $r->itemPackagingPrice,
                        "addons" => $addonArray,
                        "choice" => $choiceArray,
                    );
                }
                return response()->json(["status" => 200,  "result" => $mainitemArray], 200);
            } else {
                return response()->json(["status" => 300, "message" => "Data not found."], 200);
            }
        } else {
            return response()->json(["status" => 200,  "result" => $mainitemArray], 200);
        }
    }

    public function checknotification(Request $r)
    {
        $dataValidate = $r->validate([
            'firebaseId' => 'required'
        ]);
        $orderCode = "123646";
        $random = "df543rrfe";
        $DeviceIdsArr[] = $dataValidate['firebaseId'];
        $dataNoti = array('title' => 'Checking', 'message' => 'Ringing test', 'unique_id' => $orderCode, 'random_id' => $random, 'type' => 'Order');

        $dataArr = array();
        $dataArr['device_id'] = $DeviceIdsArr;
        $dataArr['message'] = $dataNoti['message']; //Message which you want to send
        $dataArr['title'] = $dataNoti['title'];
        $dataArr['unique_id'] = $dataNoti['unique_id'];
        $dataArr['random_id'] = $dataNoti['random_id'];
        $dataArr['type'] = $dataNoti['type'];
        $notification['device_id'] = $DeviceIdsArr;
        $notification['message'] = $dataNoti['message']; //Message which you want to send
        $notification['title'] = $dataNoti['title'];
        $notification['unique_id'] = $dataNoti['unique_id'];
        $notification['random_id'] = $dataNoti['random_id'];
        $notification['type'] = $dataNoti['type'];
        $noti = new Notificationlibv_3;
        $result = $noti->sendNotification($dataArr, $notification);
        if ($result) {
            return response()->json(["status" => 200, "result" => $result], 200);
        } else {
            return response()->json(["status" => 300,  "result" => $result], 200);
        }
    }

    public function restaurantRefreshCode()
    {
        $firestoreAction = new FirestoreActions();
        $firestoreAction->update_refresh_code('RES_27');
    }
}
