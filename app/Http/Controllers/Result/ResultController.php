<?php

namespace App\Http\Controllers\Result;

use App\Answer;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Report;
use App\Result;
use App\User;
use Illuminate\Support\Facades\Auth;

class ResultController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $report = Report::findOrFail($id);
        $userHasReport = $report->user_id;
        $userHasReportInfo = User::findOrFail($userHasReport);
        if (Auth::user()->id != $userHasReport && Auth::user()->role_id != User::ROLE_COMPANY_ADMIN && Auth::user()->role_id != User::ROLE_ADMIN) {
            return $this->errorResponse("You don't have permission to access!", 403);
        } else {
            $examId = $report->exam_id;
            $exam = Exam::findOrFail($examId); 
            if(!$exam->is_allow_view_answer) {
                return $this->errorResponse("You cannot view answer of this exam, because exam is going!", 403);
            }
            //get exam-question used in exam
            $examQuestion = $exam->exam_question()->first();
            //get all parts of exam-question
            $parts = $examQuestion->parts()->orderBy('part_no')->get();
            //get all question of each part in exam-question
            foreach ($parts as $part) {
                $questions = $part->questions()->orderBy('question_no')->get(); 
                foreach ($questions as $ques) {
                    $answers = Answer::where('question_id', '=', $ques->id)
                        ->orderBy('id')
                        ->get()
                        ->toArray();
                    $ques['answers'] = $answers;
                } 
                $part['questions'] = $questions;
            }

            $examQuestion['parts'] = $parts;
            $examQuestion['exam_name'] = $exam->name;
            $examQuestion['exam_id'] = $exam->id;
            $examQuestion['userHasReport'] = $userHasReportInfo;
            $examQuestion['timeTaken'] = $report->created_at;
            //get yourAnswer 
            $results = Result::where('report_id', $report->id)->get()->makeHidden(['created_at', 'updated_at']);
            $examQuestion['results'] = $results;
            return $this->showOne($examQuestion);
        }
    }
}
