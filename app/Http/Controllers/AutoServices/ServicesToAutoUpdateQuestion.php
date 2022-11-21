<?php

namespace App\Http\Controllers\AutoServices;

use App\Answer;
use App\ExamQuestion;
use App\Http\Controllers\ApiController;
use App\Part;
use App\Question;
use Illuminate\Http\Request;
use Exception;

class ServicesToAutoUpdateQuestion extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }

    /**
     * Get all part in exam_question
     * $id: is exam_question_id
     */
    public function show($id)
    {
        $exam_question = ExamQuestion::findOrFail($id);
        $allPartOfExamQuestion = Part::select('id', 'part_no', 'amount')
            ->where('exam_question_id', $exam_question->id)
            ->get();

        return $this->successResponse($allPartOfExamQuestion);
    }

    /**
     * Update all question in part
     * 
     */
    public function update(Request $request, $part_id)
    {
        $allQuestionInPart = Question::where('part_id', $part_id)->get();
        $dataFromClient = $request->data; 
        try {
            foreach ($allQuestionInPart as $question) {
                $dataInput = $this->getQuestionFromListDataByQuestionNo($dataFromClient, $question->question_no);
                $question->question_text = $dataInput->question_text; 
                $question->group_desc = $dataInput->group_desc;
                $question->save();
                $arrAnswersOfQuestion = Answer::where('question_id', $question->id)->get();
                $this->updateAnswersForQuestion($arrAnswersOfQuestion, $dataInput->list_answers);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse("Done!");
    }

    public function getQuestionFromListDataByQuestionNo($arrQuestion, $questionNo)
    {
        $item = null;
        foreach ($arrQuestion as $struct) {
            if ($questionNo == $struct['question_no']) {
                $item = $struct;
                break;
            }
        }
        return (object) $item;
    }

    public function updateAnswersForQuestion($arrAnswers, $data)
    {
        foreach ($arrAnswers as $key => $answer) {
            $answer['content'] = $data['position_' . ($key + 1)];
            if($key == 3) {
                $answer['is_correct_flag'] = Answer::CORRECT_ANSWER;
            }
            $answer->save();
        }
    }
}
