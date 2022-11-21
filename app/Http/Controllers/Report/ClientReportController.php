<?php

namespace App\Http\Controllers\Report;

use App\Company;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Report;
use Illuminate\Support\Facades\Auth;

class ClientReportController extends ApiController
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $currentUser = Auth::user();

        $reports = Report::where('user_id', $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $sampleExamReport = [];
        $examReport = [];
        foreach ($reports as $report) {
            $exam = Exam::find($report->exam_id)
                ->makeHidden(['exam_question_id', 'image_preview', 'status']);
            $report['total_score'] = $report->listening_score + $report->reading_score;
            $report['exam'] = $exam;

            if ($exam->company_id === Company::SYSTEM_COMPANY) {
                array_push($sampleExamReport, $report);
            } else {
                array_push($examReport, $report);
            }
        }
        $dataResponse['sample_exams'] = $sampleExamReport;
        $dataResponse['exams'] = $examReport;
        return response()->json($dataResponse, 200);
    }
}
