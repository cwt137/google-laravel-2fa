<?php

namespace App\Http\Requests;

use Cache;
use Crypt;
use Google2FA;
use App\User;
use App\Http\Requests\Request;
use Illuminate\Validation\Factory;

class ValidateSecretRequest extends Request
{
    private $user;
    
    /**
     * Create a new FormRequest instance.
     *
     * @return void
     */
    public function __construct(Factory $factory)
    {
        $factory->extend(
            'valid_token', 
            function($attribute, $value, $parameters, $validator) 
            {
                $secret = Crypt::decrypt($this->user->google2fa_secret);
                $valid  = Google2FA::verifyKey($secret, $value);

                return $valid;
            },
            'Not a valid token'
        );
        
        $factory->extend(
            'used_token',
            function ($attribute, $value, $parameters, $validator)
            {
                $key = $this->user->id . ':' . $value;

                return !Cache::has($key);
            },
            'Cannot resue token'
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isAuthorized = true;
        
        try {
            $this->user = User::findOrFail(
                session('2fa:user:id')
            );
        } catch (Exception $exc) {
            $isAuthorized = false;
        }
        
        return $isAuthorized;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'totp' => 'bail|required|digits:6|valid_token|used_token',
        ];
    }
}
