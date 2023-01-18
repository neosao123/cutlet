<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\GlobalModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
	public function __construct(GlobalModel $model)
    {
        $this->model = $model;
    }

    public function profile(Request $request, $id = '')
    {
        $code = $request->id;
        $table = 'usermaster';
        $condition = array('usermaster.code' => $code);
        $details = $this->model->read_user_information($table, $condition);
        if ($details != false) {
            return view('admin.profile.edit', compact('details'));
        }
    }

    public function show(Request $request, $id = '')
    {
		$code = $request->id;
        $table = 'usermaster';
        $condition = array('usermaster.code' => $code);
        $details = $this->model->read_user_information($table, $condition);
        if ($details != false) {
            return view('admin.profile.viewprofile', compact('details'));
        }
    }

    public function updateprofile(Request $request, $id)
    {
        $ip = $_SERVER['REMOTE_ADDR'];  
        $currentdate = Carbon::now();
        if (!empty($request->input('password')) || !empty($request->input('password_confirmation'))) {
            $rules = [
                'userEmail' => ['required', Rule::unique('usermaster')->ignore(Auth::user()->id),],
                'password'  => 'min:6|confirmed',
                'name' => 'required',
            ];
        } else {
            $rules = [
                'userEmail' => ['required', Rule::unique('usermaster')->ignore(Auth::user()->id)], 
            ];
        }
        $messages = [
            'userEmail.required' => 'Email is required',
            'password.confirmed' => 'Password does not match',
            'name.required' => 'Username is required',
            
        ];
        $this->validate($request, $rules, $messages);
        $code = $request->input('code');
        $table = 'usermaster';
        if (!empty($request->input('password')) || !empty($request->input('password_confirmation'))) {
            $data = [
                'name' => $request->input('name'),
				'userEmail' => $request->input('userEmail'),
                'password' => Hash::make($request->input('password')),
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => $code
            ];
        } else {
            $data = [
                'name' => $request->input('name'),
                'userEmail' => $request->input('userEmail'),
                'editIP' => $ip,
                'editDate' => $currentdate->toDateTimeString(),
                'editID' => $code
            ];
        }

        $result = $this->model->doEdit($data, $table, $code);

        if ($filenew = $request->file('profilephoto')) {
            $imagename = $filenew->getClientOriginalName();
            $filenew->move('assets/images/profileimages', $imagename);
            $image_data = ['profilePhoto' => $imagename];
            $image_update = $this->model->doEdit($image_data, $table, $code);
        }

        if ($result == true || $image_update == true) {
            return redirect()->back()->with('status', ['message' => "Profile Updated Successfully"]);
        } else {
            return redirect()->back()->with('status', ['message' => "Something went to wrong"]);
        }
    }
}
