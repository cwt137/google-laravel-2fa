<?php

namespace App\Http\Controllers;

use Crypt;
use Google2FA;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

class Google2FAController extends Controller
{
    use ValidatesRequests;
    
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
    }
    
    public function generateSecret(Request $request) 
    {   
        //generate new secret
        $randomBytes = str_split(openssl_random_pseudo_bytes(16));
        $secret      = collect($randomBytes)->map(function($item) {
            return ord($item) % 32;
        })->reduce(function($carry, $item) {
            $b32 = "234567QWERTYUIOPASDFGHJKLZXCVBNM";
            return $carry .$b32[$item];
        });
        
        $user = $request->user();
        
        //encrypt and then save secret
        $user->google2fa_secret = Crypt::encrypt($secret);
        $user->save();
        
        //generate image for QR barcode
        $imageDataUri = Google2FA::getQRCodeInline(
            $request->getHttpHost(),
            $user->email,
            $secret,
            200
        );
        
        return view('2fa/generateSecret', ['image' => $imageDataUri,
            'secret' => $secret]);
    }
    
    public function removeSecret(Request $request) 
    {
        $user = $request->user();
        
        //make secret column blank
        $user->google2fa_secret = NULL;
        $user->save();
        
        return view('2fa/removeSecret');
    }
    
}
