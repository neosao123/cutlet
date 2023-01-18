<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurants;
use App\Models\GlobalModel;
use App\Classes\GeneralFunctions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;  
    }
	
    public function index()
    {
        return view('restaurant.auth.login');
    }
	
     public function login(Request $r)
    {
        if ($r->isMethod('post')) {
            $r->validate([
                'mobile' => 'required',
                'passwordR' => 'required',
            ]);
            $mobile = $r->input('mobile');
            $password = $r->input('passwordR');
            $remember = $r->input('rememberme') == true ? '1' : '0';
            $result = Restaurants::where(['ownerContact' => $mobile])->first();
            if (empty($result)) {
                $r->session()->flash('fail', 'Please Enter Valid Mobile Number & Password');
                return redirect('/');
            } else {
                if (Auth::guard('restaurant')->attempt(['ownerContact' => $mobile, 'password' => $password])) {
                    Auth::login($result);
                    $mobile = Auth::guard('restaurant')->user()->ownerContact;
                    $r->session()->put('RESTO_LOGIN', true);
                    if ($remember == '1') {
                        Cookie::queue('mobile', $mobile, time() + (10 * 365 * 24 * 60 * 60));
                        Cookie::queue('passwordR', $password, time() + (10 * 365 * 24 * 60 * 60));
                    } else {
                        if (Cookie::get('mobile')) Cookie::queue('mobile', '');
                        if (Cookie::get('passwordR')) Cookie::queue('passwordR', '');
                    }
                    return redirect('/recentorders');
                }
                $r->session()->flash('error', 'Invalid Mobile or password.');
                return redirect('/');
            }
        }
    }
	
	public function logout(Request $r)
    {
        Auth::guard('restaurant')->logout();
        session()->forget('RESTO_LOGIN');
        $r->session()->flash('success', 'Successfully Logout');
        return redirect('/'); 
    }
	
	 public function reset(Request $r)
    {
        return view('restaurant.auth.verify');
    }
	
	public function resetPassword(Request $request)
    {
        $email = $request->input('useremail');
        $result = $this->model->checkrecord_exists('email', $email, 'restaurant');
        if ($result) {
            $token = $this->model->randomCharacters(5);
            $token .= date('Hdm');
            $sendLink = url('/recoverPassword/' . $token);
            $details = [
                'title' => 'Mail from CUTLET',
                'link' => $sendLink,
            ];

            \Mail::to($email)->send(new \App\Mail\ForgotRestaurantEmail($details));

            $code = $result->code;
            $data = array('resetToken' => $token);
            $resultAfterMail = $this->model->doEditWithField($data, 'restaurant', 'code', $code);
            if ($resultAfterMail) {
                $request->session()->flash('success', 'Reset Link was sent to your email...');
                return redirect('/reset-password');
            } else {
                $request->session()->flash('error', 'Some Error is Occur');
                return redirect('/reset-password');
            }
        } else {
            $request->session()->flash('error', 'No users were found with the email address provided! Sorry cannot reset the password');
            return redirect('/reset-password');
        }
    }
	
	public function verifyTokenLink(Request $request)
    {
        $token = $request->token;
        $result = $this->model->checkrecord_exists('resetToken', $token, 'restaurant');
        if ($result) {
            return view('restaurant.auth.reset', compact('result'));
        } else {
            $request->session()->flash('message', 'Password Reset Link is Expired. Please Forgot Password Again to Continue.');
            return redirect('/');
        }
    }
	
	 //reset password
    public function updateMemberPassword(Request $request)
    {
        $token = $request->input('token');
        $code = $request->input('code');
        $result = $this->model->checkrecord_exists('resetToken', $token, 'restaurant');
        if ($result) {
            $rules = [
                'password'  =>  'min:6|confirmed|required',
                'password_confirmation' => 'min:6|required',
            ];

            $messages = [
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password is not matched'
            ];
            $this->validate($request, $rules, $messages);

            $data = array(
                'password' => Hash::make($request->input('password')),
                'resetToken' => null,
            );
            $result = $this->model->doEdit($data, 'restaurant', $code);
            if ($result) {
                $request->session()->flash('message', 'Password Reset Successfully.. Please Login to Continue');
                return redirect('/admin/restaurant');
            } else {
                $request->session()->flash('message', 'Problem During Reset Password.. Please Try Again');
                return redirect('/admin/restaurant');
            }
        } else {
            $request->session()->flash('message', 'Reset Link is broken! Please try again...');
            return redirect('/admin/restaurant');
        }
    }
}
