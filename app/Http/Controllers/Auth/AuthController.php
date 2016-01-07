<?php

namespace App\Http\Controllers\Auth;

use Crypt;
use App\User;
use Google2FA;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
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
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    
    /**
     * Send the post-authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, Authenticatable $user)
    {
        if ($user->google2fa_secret) {
            Auth::logout();
            
            $request->session()->put('2fa:user:id', $user->id);
            
            return redirect('2fa/validate');
        }
        
        return redirect()->intended($this->redirectTo);
    }
    
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function getValidateSecret()
    {
        if(session('2fa:user:id')) {
            return view('2fa/validate');
        } else {
            return redirect('login');
        }
    }
    /**
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postValidateSecret(Request $request)
    {
        $this->validate($request, ['totp' => 'required|digits:6']);
        
        if (!session('2fa:user:id')) {
            return redirect('login');
        }
        
        $user = (new User)->findOrFail(
                    $request->session()->pull('2fa:user:id')
                );
        
        $secret = Crypt::decrypt($user->google2fa_secret);
        
        $valid = Google2FA::verifyKey($secret, $request->totp);
        
        if ($valid) {
            Auth::login($user);
            return redirect()->intended($this->redirectTo);
        } else {
            return back();
        }
    }

}
