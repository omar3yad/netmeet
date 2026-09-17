<?php
namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SocialLoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        config(
            [
                'services.google' => [
                    'client_id' => getSetting('GOOGLE_CLIENT_ID'),
                    'client_secret' => getSetting('GOOGLE_CLIENT_SECRET'),
                    'redirect' => '/login/google/callback'
                ],
                'services.facebook'=> [
                    'client_id' => getSetting('FACEBOOK_CLIENT_ID'),
                    'client_secret' => getSetting('FACEBOOK_CLIENT_SECRET'),
                    'redirect' => '/login/facebook/callback'
                ],
                'services.twitter'=> [
                    'client_id' => getSetting('TWITTER_CLIENT_ID'),
                    'client_secret' => getSetting('TWITTER_CLIENT_SECRET'),
                    'redirect' => '/login/twitter/callback'
                ],
                'services.linkedin'=> [
                    'client_id' => getSetting('LINKEDIN_CLIENT_ID'),
                    'client_secret' => getSetting('LINKEDIN_CLIENT_SECRET'),
                    'redirect' => '/login/linkedin/callback'
                ]
            ]
        );
    }

    //Google Login
    public function redirectToGoogle(){
        try {
            return Socialite::driver('google')->stateless()->redirect();
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }
    
    //Google callback  
    public function handleGoogleCallback(){
        try {
            $data = Socialite::driver('google')->stateless()->user();
            $data->social = 'google';
            $user = User::where('email',$data->email)->first();
            if(!$user){
                Session::put('sociallogindata', $data);
                return redirect()->route('username.add');
            }else{
                Auth::login($user);
            }
            return redirect()->route('home');
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    //Facebook login
    public function redirectToFacebook(){
        try {
            return Socialite::driver('facebook')->stateless()->redirect();
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    //Facebook callback  
    public function handleFacebookCallback(){
        try {
            $data = Socialite::driver('facebook')->stateless()->user();
            $data->social = 'facebook';
            $user = User::where('email',$data->email)->first();
            if(!$user){
                Session::put('sociallogindata', $data);
                return redirect()->route('username.add');
            }else{
                Auth::login($user);
            }
            return redirect()->route('home');
        }catch (Exception $e) {
            echo $e->getMessage();
            die;
            return back()->with('error', $e->getMessage());
        }
    }

    //Linkedin login
    public function redirectToLinkedin(){
        try {
            return Socialite::driver('linkedin')->stateless()->redirect();
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    //Linkedin callback  
    public function handleLinkedinCallback(){
        try {
            $data = Socialite::driver('linkedin')->stateless()->user();
            $data->social = 'linkedin';
            $user = User::where('email',$data->email)->first();
            if(!$user){
                Session::put('sociallogindata', $data);
                return redirect()->route('username.add');
            }else{
                Auth::login($user);
            }
            return redirect()->route('home');
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    //Twitter login
    public function redirectToTwitter(){
        try {
            return Socialite::driver('twitter')->redirect();
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    //Twitter callback  
    public function handleTwitterCallback(){
        try {
            $data = Socialite::driver('twitter')->user();
            $data->social = 'twitter';
            $user = User::where('email',$data->email)->first();
            if(!$user){
                Session::put('sociallogindata', $data);
                return redirect()->route('username.add');
            }else{
                Auth::login($user);
            }
            return redirect()->route('home');
        }catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
