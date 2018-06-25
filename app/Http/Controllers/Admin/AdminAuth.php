<?php

namespace App\Http\Controllers\Admin;
//use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use Illuminate\Http\Request;
use App\Mail\AdminResetPassword;
use DB;
use Mail;
use Carbon\Carbon;
class AdminAuth extends Controller
{
    public function login(){
    	return view('admin.login');
    }

    public function dologin(){
    	$rememberme=request('rememberme')==1 ? true: false;
    	if (auth()->guard('admin')->attempt(['email'=>request('email'),'password'=>request('password')],$rememberme)) {
    		return redirect('admin');
    	}else{
    		session()->flash('error',trans('admin.incorrect_information_login'));
    		return redirect('admin/login');
    	}
    }

    public function logout(){
    	auth()->guard('admin')->logout();
    	return redirect('admin/login');
    }
    public function forget_password(){
        return view('admin.forget_password');
    }
    
    public function forget_password_post(){
        $admin=Admin::where('email',request('email'))->first();
        if (! empty($admin)) {
        	$token=app('auth.password.broker')->createToken($admin);
            $data=DB::table('password_resets')->insert([
            'email'=>$admin->email,
            'token'=>$token,
            'created_at'=>Carbon::now(),
            ]);
            Mail::to($admin->email)->send(new AdminResetPassword (['data'=>$admin,'token'=>$token]));
            session()->flash('success',trans('admin.the_link_reset_sent')); 
            return back();
        }
            return back();
    }
    
    public function reset_password_final($token){
    	$this->validate(request(),[
         'password'=>'required|confirmed',
         'password_confirmation'=>'required',
    	],[],[
          'password'=>'password',
          'password_confirmation'=>'confirmation password',
    	]);
    	$check_token=DB::table('password_resets')->where('token',$token)->where('created_at','>',Carbon::now()->subHours(2))->first();
    	if(! empty($check_token)){
          $admin=Admin::where('email',$check_token->email)->update(['email'=>$check_token->email,'password'=>bcrypt(request('password'))]);
          DB::table('password_resets')->where('email',request('email'))->delete();
          admin()->attempt(['email'=>$check_token->email,'password'=>request('password')],true);
          return redirect(aurl());
    	}else{
    		return redirect(aurl('forget/password'));
    	}
    }

    public function reset_password($token){
    	$check_token=DB::table('password_resets')->where('token',$token)->where('created_at','>',Carbon::now()->subHours(2))->first();
    	if(! empty($check_token)){
           return view('admin.reset_password',['data'=>$check_token]);

    	}else{
    		return redirect(aurl('forget/password'));
    	}
    }
    
}
