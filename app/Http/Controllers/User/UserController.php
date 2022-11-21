<?php

namespace App\Http\Controllers\User;

use App\Company;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;

class UserController extends ApiController
{

    public function __construct(){
        parent::__construct(); 
        $this->middleware('admin', ['except' => ['update', 'show', 'updateAvatar']]);
    }
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $users = User::with('role')
                    ->with('company')    
                    ->get(); 
        return response()->json($users, 200);
    }

    
    /**
     * Store a newly created resource in storage 
     */
    public function store(Request $request)
    {
        $rules = [
            'name'=> 'required',
            'gender' => 'required',
            'email'=> 'required|email|unique:user',
            'role_id' => 'required',
            'password' => 'required|min:8',
        ];

        $this->validate($request, $rules);

        $data = [];
        $data['name'] = $request->name;
        $data['gender'] = $request->gender;
        $data['email'] = $request->email;
        $data['role_id'] = $request->role_id;
        $data['verified'] = User::VERIFIED_USER;
        $data['email_verified_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $user['role'] = $user->role_id; 
        $user->company;
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->id != $id && Auth::user()->role->name != "Admin") {
            return $this->errorResponse("You can only edit your information!", 403);
        }

        $user = User::findOrFail($id);
        
        if($request->has('name')){
            $user->name = $request->name;
        }

        if($request->has('birthday')){
            $user->birthday = $request->birthday;
        }

        if($request->has('gender')){
            $user->gender = $request->gender;
        }

        if($request->has('role_id')){
            $user->role_id = $request->role_id;
            $user->company_id = Company::SYSTEM_COMPANY;
        }

        if($request->has('password')){
            $credentials = ['email' => Auth::user()->email, 'password' => $request->oldpassword];

            try {
                if (!($token = JWTAuth::attempt($credentials))) {
                    return $this->errorResponse("Old password doesn't match!", 400);
                }
            } catch(JWTException $e) {
                return $this->errorResponse("Server error!", 500);
            }
            
            $user->password = bcrypt($request->password);
        }

        // if(!$user->isDirty()){
        //     return $this->errorResponse('You need to specify a different value to update', 422);                         
        // }

        $user->save();
        
        return $this->showOne($user);
    }

    public function updateAvatar(Request $request, $id){
        $currentUser = Auth::user();
        
        if($currentUser->id != $id) {
            return $this->errorResponse("You can only edit your information!", 403);
        }

        if($request->hasFile('image')){
            $file = $request->file('image');
            $file_extension = strtolower($file->getClientOriginalExtension());

            if($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg'){
                return $this->errorResponse('fail file_extension', 400);
            }

            $name = $file->getClientOriginalName();
            $avatar = time()."_".str_random(4)."_".$name;

            $urlToAvatar = "upload/avatar/".$avatar;
            while (file_exists($urlToAvatar)) {
                $avatar = time()."_".str_random(4)."_".$name;
            }

            $fileDirectories = "avatars";
            $fileName = "user_".$currentUser->id.'.'.$file_extension;
            $urlToAvatar = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);  
            if ($currentUser->avatar_url != null) {
                UploadFileToS3Controller::destroy($currentUser->avatar_url); 
            }
            $currentUser->avatar_url = $urlToAvatar;
            $currentUser->save();
            return $this->successResponse(['avatar_url' => $urlToAvatar], 200);
        } 
        return $this->errorResponse('No files ', 400);
    }

    public function updateStatus($id) {
        $user = User::findOrFail($id);

        $status = $user->active_status;
        if($status){
            $user->active_status = User::BLOCK_USER;
        } else {
            $user->active_status = User::ACTIVE_USER;
        }

        $user->save();
        $role = $user->role()->first();
        $user['role'] = $role;

        return $this->showOne($user, 200);
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if($user->role_id === User::ROLE_COMPANY_ADMIN) {
            return $this->errorResponse('This is the management account of a company, you only have the right to lock!', 403);
        }

        $user->delete();
        return $this->showOne($user, 200); 
    }
}
