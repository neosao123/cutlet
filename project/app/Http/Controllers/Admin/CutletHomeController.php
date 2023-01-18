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


class CutletHomeController extends Controller
{
    public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }
	public function index(){
		return view('welcome');
	}
	
	public function about(){
		return view('about');
	}
	
	public function privacy(){
		return view('privacy');
	}
	public function terms(){
		return view('terms');
	}
	public function sendmail(Request $r){
		$name=$r->name;
		$email=$r->email;
		$phone=$r->phone;
		$subject=$r->subject;
		$message=$r->message; 
		$details = [
                'name' => $name,
                'email' => $email,
				'phone' => $phone,
				'subject' => $subject,
				'message'=>$message
            ];

        \Mail::to('seemashelar01@gmail.com')->send(new \App\Mail\SendMail($details));
       return redirect()->back()->with('status', ['message' => "Thank You for Your Interest!! Our Sales Representative will contact you shortly."]);
		
	}
}