<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\RequiredIf;
use App\Rules\ValidateReCaptcha;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest', 'checkAuthMode']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users', 'alpha_dash'],
            'email' => ['required', 'string', 'email', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:50', 'confirmed'],
            'terms' => ['accepted'],
            'g-recaptcha-response' => [new RequiredIf(getSetting('CAPTCHA_REGISTER_PAGE') == 'enabled'), new ValidateReCaptcha]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(60)
        ]);

        if (getSetting('VERIFY_USERS') == 'enabled') {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }

    //override the default register method
    public function showRegistrationForm(Request $request)
    {
        if (($request->server('HTTP_REFERER') == route('pricing') || $request->server('HTTP_REFERER') == route('home').'/') && $request->input('plan') > 1) 
        {
            $request->session()->put('plan_redirect', ['id' => $request->input('plan'), 'interval' => $request->input('interval')]);
        }
        return view('auth.register', [
            'page' => __('Register'),
        ]);
    }

    //redirected to add username page
    public function username(Request $request){
        if($request->session()->has('sociallogindata')){
            return view('auth.add-username', [
                'page' => __('Set Username'),
            ]);
        }else{
            return redirect()->route('home');
        }              
    }

    //validate username and store user information
    public function usernameVerify(Request $request){
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:20', 'unique:users', 'alpha_dash']
        ]);
        
        $data = $request->session()->get('sociallogindata');

        $user = new User();
        $user->username = $request->get('username');
        $user->email = $data->email;
        $user->password = Hash::make(Str::random(6));            
        $user->google_id = $data->social && $data->social == 'google' ? $data->id : null;
        $user->facebook_id = $data->social && $data->social == 'facebook' ? $data->id : null;
        $user->twitter_id = $data->social && $data->social == 'twitter' ? $data->id : null;
        $user->linkedin_id = $data->social && $data->social == 'linkedin' ? $data->id : null;
        if(isset($data->avatar) && $data->avatar != ''){
            $contents = file_get_contents($data->avatar);
            $name = substr($data->avatar, strrpos($data->avatar, '/') + 1).'.png';
            Storage::put('public/avatars/'.$name, $contents);
            $user->avatar = $name;
        }
        
        $user->save();

        Session::forget('sociallogindata');

        Auth::login($user);
        return redirect()->route('dashboard');
    }
}
