<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Users;
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
        return view('admin.auth.login');
    }
	
	public function login(Request $r)
    {
        if ($r->isMethod('post')) {
            $r->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            $email = $r->input('email');
            $password = $r->input('password');
            $remember = $r->input('rememberme') == true ? '1' : '0';
            $result = Users::where(['userEmail' => $email])->first();
            if (empty($result)) {
                $r->session()->flash('fail', 'Please Enter Valid Email & Password');
                return redirect('/login');
            } else {
                if (Auth::guard('admin')->attempt(['userEmail' => $email, 'password' => $password])) {
                    Auth::login($result);
                    $email = Auth::guard('admin')->user()->userEmail;
					$role = Auth::guard('admin')->user()->role;
					$code = Auth::guard('admin')->user()->code;
					if($role=='DBOY'){
						 $r->session()->flash('fail', "You don't have access to use this site..Please contact administrator");
						return redirect('/login');
					}else{
						$filename= 'assets/init_site/rights/'.$code.'.json'; 
						if(file_exists($filename)){
							$r->session()->put('USER_LOGIN', true);
							if ($remember == '1') {
								Cookie::queue('email', $email, time() + (10 * 365 * 24 * 60 * 60));
								Cookie::queue('password', $password, time() + (10 * 365 * 24 * 60 * 60));
							} else {
								if (Cookie::get('email')) Cookie::queue('email', '');
								if (Cookie::get('password')) Cookie::queue('password', '');
							}
							return redirect('/dashboard');
						}else{
							 $r->session()->flash('error', 'Rights not yet assigned..Please contact administrator');
							return redirect('/login');
						}
					}
                }
                $r->session()->flash('error', 'Invalid Email or password.');
                return redirect('/login');
            }
        }
    }
	
	
	public function updatePassword()
    {
        $r=Users::find(1);
        $r->password=Hash::make('123456'); 
        $r->save();
    }
	
    public function reset(Request $r)
    {
        return view('admin.auth.verify');
    }
	
	public function resetPassword(Request $request)
    {
        $email = $request->input('useremail');
        $result = $this->model->checkrecord_exists('userEmail', $email, 'usermaster');
        if ($result) {
            $token = $this->model->randomCharacters(5);
            $token .= date('Hdm');
            $sendLink = url('/recoverPassword/' . $token);
            $details = [
                'title' => 'Mail from CUTLET',
                'link' => $sendLink,
            ];

            \Mail::to($email)->send(new \App\Mail\ForgotAdminEmail($details));

            $code = $result->code;
            $data = array('resetToken' => $token);
            $resultAfterMail = $this->model->doEditWithField($data, 'usermaster', 'code', $code);
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
        $result = $this->model->checkrecord_exists('resetToken', $token, 'usermaster');
        if ($result) {
            return view('admin.auth.reset', compact('result'));
        } else {
            $request->session()->flash('message', 'Password Reset Link is Expired. Please Forgot Password Again to Continue.');
            return redirect('/login');
        }
    }

    //reset password
    public function updateMemberPassword(Request $request)
    {
        $token = $request->input('token');
        $code = $request->input('code');
        $result = $this->model->checkrecord_exists('resetToken', $token, 'usermaster');
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
            $result = $this->model->doEdit($data, 'usermaster', $code);
            if ($result) {
                $request->session()->flash('message', 'Password Reset Successfully.. Please Login to Continue');
                return redirect('/login');
            } else {
                $request->session()->flash('message', 'Problem During Reset Password.. Please Try Again');
                return redirect('/login');
            }
        } else {
            $request->session()->flash('message', 'Reset Link is broken! Please try again...');
            return redirect('/login');
        }
    }
	
	public function logout(Request $r)
    {
        Auth::guard('admin')->logout();
        session()->forget('USER_LOGIN');
        $r->session()->flash('success', 'Successfully Logout');
        return redirect('/login'); 
    }

}
