<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Controllers\SendEmail\EmailController;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTAuthController extends Controller
{
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        try {
            if (!($token = JWTAuth::attempt($credentials))) {
                return response()->json(['error' => 'Email or password incorrect'], 401);
            }
        } catch(JWTException $e) {
            return response()->json(['msg' => 'Could not create token'], 500);
        }
        
        if(!Auth::user()->active_status) {
            return response()->json(['error' => 'Your account is currently locked!'], 401);
        }

        if(!Auth::user()->verified) {
            return response()->json(['error' => 'Check your email registration to verify!'], 401);
        }

        Auth::user()->role->name;
        Auth::user()->company;
        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'status' => 'success', 
            'expires' => auth('api')->factory()->getTTL() * 60, // time to expiration
            'user' => Auth::user()
        ], 200);
    }

    public function me(Request $request){
        $user = Auth::user();

        if ($user) {
            return response()->json($user, 200); 
        }

        return response()->json("Not found", 404);
    }

    public function refresh() {
        $token = Auth::guard('api')->refresh();
        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'status' => 'success', 
            'expires' => auth('api')->factory()->getTTL() * 60, // time to expiration
        ], 200);
    }

    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     */
    public function logout(Request $request) {
        // Get JWT Token from the request header key "Authorization"
        $token = $request->header('Authorization');
       
        // Invalidate the token
        try {
            JWTAuth::invalidate($token);
            return response()->json([
                'status' => 'success', 
                'message'=> "You have successfully logged out."
            ], 200);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
              'status' => 'error', 
              'message' => 'Failed to logout, please try again.'
            ], 500);
        }
    }

    //signupPersonal
    public function signupPersonal(Request $request){
        $rules = [
            'name'=> 'required',
            'email'=> 'required|email|unique:user',
            'password' => 'required|min:6',
        ];

        $this->validate($request, $rules);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role_id = User::ROLE_MEMBER;
        $user->verified = User::UNVERIFIED_USER;
        $user->company_id = Company::SYSTEM_COMPANY;
        $user->verification_token = User::genarateVerificationCode();
        $user->save(); 

        $dataSendEmail['email'] = $user['email'];
        $dataSendEmail['name'] = $user['name'];
        $dataSendEmail['verification_code'] = $user->verification_token;
        EmailController::sendEmailVerifyEmail($dataSendEmail);
        EmailController::sendEmailAdminVerifyEmail($dataSendEmail);

        return response()->json(['status'=> "success", 'message'=> 'Thanks for signing up! Please check your email to complete your registration.']);
    }

    //signupCompany
    public function signupCompany(Request $request){
        $rules = [
            'company_name'=> 'required|unique:company,name',
            'address'=> 'required',
            'phone'=> 'required|unique:company',
            'name'=> 'required',
            'email'=> 'required|email|unique:user',
            'password' => 'required|min:6',
        ];

        $this->validate($request, $rules);

        $company = new Company;
        $company->name = $request->company_name;
        $company->address = $request->address;
        $company->phone = $request->phone;
        $company->save();

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role_id = User::ROLE_COMPANY_ADMIN;
        $user->verified = User::UNVERIFIED_USER;
        $user->company_id = $company->id;
        $user->verification_token = User::genarateVerificationCode();
        $user->save(); 

        $dataSendEmail['company_name'] = $company['name'];
        $dataSendEmail['address'] = $company['address'];
        $dataSendEmail['phone'] = $company['phone'];
        $dataSendEmail['email'] = $user['email'];
        $dataSendEmail['password'] = $request->password;
        $dataSendEmail['name'] = $user['name'];
        $dataSendEmail['verification_code'] = $user->verification_token;
        EmailController::sendEmailVerifyCompanyRegister($dataSendEmail);
        EmailController::sendEmailAdminVerifyCompanyRegister($dataSendEmail);

        return response()->json(['status'=> "success", 'message'=> 'Thanks for signing up! Please check your email to complete your registration.']);
    }

    public function verifyUser($verification_code){
        $check = DB::table('user')->where('verification_token',$verification_code)->first();
        if(!is_null($check)) {
            $user = User::find($check->id);
            if($user->verified == User::VERIFIED_USER){
                return response()->json([
                    'success'=> true,
                    'message'=> 'Account already verified..'
                ]);
            } 

            $user->verified = User::VERIFIED_USER;
            $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
            $user->verification_token = null;
    
            $user->save();
            return view('verified', ['success' => true, 'message' => 'The account has been verified succesfully!']);
        }
        return view('verified', ['success' => false, 'message' => 'Verification code is invalid. Check your infomation!']); 
    }
}
