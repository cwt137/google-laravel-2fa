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
            $secret = Google2FA::generateSecretKey();
            
            $user = $request->user();
            
            //encrypt and then save secret
            $user->google2fa_secret = Crypt::encrypt($secret);
            $user->save();
            
            //generate URL for QR barcode
            $imageUrl = Google2FA::getQRCodeGoogleUrl(
                            $request->getHttpHost(),
                            $user->email,
                            $secret
                        );
            
            return view('2fa/generateSecret', ['imageUrl' => $imageUrl,
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
