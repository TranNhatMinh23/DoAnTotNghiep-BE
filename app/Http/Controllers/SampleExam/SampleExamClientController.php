<?php

namespace App\Http\Controllers\SampleExam;

use App\Answer;
use App\Company;
use App\Exam;
use App\Http\Controllers\ApiController;

class SampleExamClientController extends ApiController
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $currentSampleExam = Exam::where('company_id', '=', Company::SYSTEM_COMPANY)
            ->where('status', '=', Exam::ONGOING)
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        return $this->successResponse($currentSampleExam);
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $sampleExam = Exam::findOrFail($id);
        if ($sampleExam->status == Exam::STOP) {
            return $this->errorResponse("Cannot access to the exam", 404);
        }

        //get exam-question used in exam
        $examQuestion = $sampleExam->exam_question()->first();
        //get all parts of exam-question
        $parts = $examQuestion->parts()->orderBy('part_no')->get();
        //get all question of each part in exam-question
        foreach ($parts as $part) {
            $questions = $part->questions()->orderBy('question_no')->get(); 
            foreach ($questions as $ques) {
                $answers = Answer::where('question_id', '=', $ques->id)
                    ->orderBy('id')
                    ->get()
                    ->makeHidden(['is_correct_flag'])
                    ->toArray();
                //check de shuffle cau tra loi o day
                if($sampleExam->is_shuffle_answer) {
                    $arrayCheck = [5, 6, 7];
                    if(in_array($part->part_no, $arrayCheck)) {
                        shuffle($answers);
                    }
                }
                $ques['answers'] = $answers;
            } 
            $part['questions'] = $questions;
        } 

        $examQuestion['parts'] = $parts;
        $examQuestion['exam_name'] = $sampleExam->name;
        $examQuestion['exam_id'] = $sampleExam->id;
        return $this->showOne($examQuestion);
    }

    public function getSampleExamBeforeTaken($id)
    {
        $sampleExam = Exam::findOrFail($id);
        if ($sampleExam->status == Exam::STOP) {
            return $this->errorResponse("Cannot access to the exam", 404);
        }
        return $this->showOne($sampleExam);
    }
}
