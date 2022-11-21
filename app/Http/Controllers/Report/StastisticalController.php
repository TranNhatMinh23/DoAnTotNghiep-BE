<?php

namespace App\Http\Controllers\Report;

use App\Exam;
use App\Http\Controllers\ApiController; 
use App\Report;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth; 

class StastisticalController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }

    /** GET all statistical of self company */
    public function index()
    {
        try {
            $companyId = Auth::user()->company_id;
            $allExamOfCompany = Exam::where('company_id', $companyId)->pluck('id')->all();

            $allReportsWithExamOfCompany = Report::whereIn('exam_id', $allExamOfCompany)->latest()->get();
            $allsss = $allReportsWithExamOfCompany->groupBy('user_id');
            $dataResponse = [];
            foreach ($allsss as $key => $value) {
                $user = User::findOrFail($key)->makeHidden([
                    'email_verified_at',
                    'active_status',
                    'verified',
                    'created_at',
                    'updated_at'
                ]);
                $user['exam_reports'] = $value;
                array_push($dataResponse, $user);
            }
            return $this->successResponse($dataResponse);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /** Show detail statistical of each user
     * id: user_id
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            $companyId = Auth::user()->company_id;
            $allExamOfCompany = Exam::where('company_id', $companyId)->pluck('id')->all();
            $allReportsOfUser = Report::whereIn('exam_id', $allExamOfCompany)
                ->where('user_id', $id)
                ->latest()
                ->get();
            foreach ($allReportsOfUser as $item) {
                $a = Exam::findOrFail($item->exam_id);
                $item['exams'] = $a->getBasicExamInfo();
            }

            $dataResponse['user'] = $user;
            $dataResponse['detail_statistical'] = $allReportsOfUser;
            
            return $this->successResponse($dataResponse);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
