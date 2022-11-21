<?php

namespace App\Http\Controllers\Company;

use App\Company;
use App\Http\Controllers\ApiController;
use App\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        if (Auth::user()->role_id !== User::ROLE_ADMIN) {
            return response()->json(['error' => "You don't have an admin role!"], 403);
        }
        $companies = Company::where('id', '<>', Company::SYSTEM_COMPANY)->orderBy('id')->get();
        foreach ($companies as $comp) {
            $manager = User::where('company_id', $comp->id)->first()->makeHidden(['role_id', 'company_id']);
            $comp['manager'] = $manager;
        }
        return $this->showAll($companies);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        if (Auth::user()->company_id !== $company->id) {
            return response()->json(['error' => "You can only update your company's infomation!"], 403);
        }
        $rules = [
            'name' => 'required',
            'phone' => 'required',
            "address" => 'required'
        ]; 
        
        if ($request->has('name')) {
            $company->name = $request->name;
        }
        if ($request->has('address')) {
            $company->address = $request->address;
        }
        if ($request->has('phone')) {
            $company->phone = $request->phone;
        }
        $company->save();
        return $this->showOne($company, 200);
    }
}
