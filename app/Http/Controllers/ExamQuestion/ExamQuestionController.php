<?php

namespace App\Http\Controllers\ExamQuestion;

use App\Answer;
use App\Company;
use App\Enums\AmountAnswerInQuestion;
use App\Part;
use App\Question;
use App\ExamQuestion;
use Illuminate\Http\Request;
use App\Enums\PartAmountEnum;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use Illuminate\Support\Facades\Auth;
use Exception;

class ExamQuestionController extends ApiController
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
        //GET ALL EXAM_QUESTION
        // get company_id from Auth =>  get list exam questions has company_id
        $companyId = Auth::user()->company_id;
        $listExamQuestions = ExamQuestion::where('company_id', '=', $companyId)
            ->orderBy('id')
            ->with('exam_question_score')
            ->get();
        return $this->showAll($listExamQuestions);
    }

    /**
     * Store a newly created resource in storage. 
     */

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:exam_question'
        ];

        $this->validate($request, $rules);

        try {
            $data = $request->all();
            $data['company_id'] = Auth::user()->company_id;
            $data['status'] = ExamQuestion::UNCOMPLETED;
            $examQuestion = ExamQuestion::create($data);

            // auto create new empty parts and questions
            if ($data['examquestionsFormat'] == ExamQuestion::NEW_FORMAT) {
                $this->createPart(1, 1, PartAmountEnum::getValue('PART_1_NEW_FORMAT'), $examQuestion);
                $this->createPart(2, 7, PartAmountEnum::getValue('PART_2_NEW_FORMAT'), $examQuestion, AmountAnswerInQuestion::getValue('THREE_ANSWER'));
                $this->createPart(3, 32, PartAmountEnum::getValue('PART_3_NEW_FORMAT'), $examQuestion);
                $this->createPart(4, 71, PartAmountEnum::getValue('PART_4_NEW_FORMAT'), $examQuestion);
                $this->createPart(5, 101, PartAmountEnum::getValue('PART_5_NEW_FORMAT'), $examQuestion);
                $this->createPart(6, 131, PartAmountEnum::getValue('PART_6_NEW_FORMAT'), $examQuestion);
                $this->createPart(7, 147, PartAmountEnum::getValue('PART_7_NEW_FORMAT'), $examQuestion);
            } else {
                $this->createPart(1, 1, PartAmountEnum::getValue('PART_1_OLD_FORMAT'), $examQuestion);
                $this->createPart(2, 11, PartAmountEnum::getValue('PART_2_OLD_FORMAT'), $examQuestion, AmountAnswerInQuestion::getValue('THREE_ANSWER'));
                $this->createPart(3, 41, PartAmountEnum::getValue('PART_3_OLD_FORMAT'), $examQuestion);
                $this->createPart(4, 71, PartAmountEnum::getValue('PART_4_OLD_FORMAT'), $examQuestion);
                $this->createPart(5, 101, PartAmountEnum::getValue('PART_5_OLD_FORMAT'), $examQuestion);
                $this->createPart(6, 141, PartAmountEnum::getValue('PART_6_OLD_FORMAT'), $examQuestion);
                $this->createPart(7, 153, PartAmountEnum::getValue('PART_7_OLD_FORMAT'), $examQuestion);
            }
            return $this->showOne($examQuestion, 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function createPart(int $partNo, int $questionNo, int $partAmount, ExamQuestion $examQuestion, $numberAnswerInQuestion = 4)
    {
        $part['part_no'] = $partNo;
        $part['amount'] = $partAmount;
        $part['exam_question_id'] = $examQuestion->id;
        $newPart = Part::create($part);

        for ($i = $questionNo; $i < ($questionNo + $partAmount); $i++) {
            $this->createQuestion($i, $newPart, $numberAnswerInQuestion);
        }
    }

    public function createQuestion(int $questionNo, Part $part, $numberAnswerInQuestion)
    {
        $question['question_no'] = $questionNo;
        $question['part_id'] = $part->id;

        $newQuestion = Question::create($question);
        $this->createAnswerForQuestion($newQuestion, $numberAnswerInQuestion);
    }

    public function createAnswerForQuestion(Question $question, $numberAnswerInQuestion)
    {
        $answer['question_id'] = $question->id;
        for ($i = 0; $i < $numberAnswerInQuestion; $i++) {
            Answer::create($answer);
        }
    }


    /**
     * Display the specified resource.
     * ======
     * Show detail examQuestion with parts and without questions
     * ====== 
     */
    public function show($id)
    {
        $examQuestion = ExamQuestion::findOrFail($id);
        if ($examQuestion->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only get your company's exam question", 403);
        }
        $parts = $examQuestion->parts()->orderBy('part_no')->get();

        // foreach ($parts as $key => $part) {
        //     $questions = $part->questions()->get();
        //     // $part['numOfCompletedQuestions'] = 2;
        //     $part['questions'] = $questions;
        // }

        //HIDEEN ANSWER_KEY
        // return User::all()->each(function ($user) {
        //     $user->addHidden([.........]);
        // });

        $examQuestion['parts'] = $parts;
        return $this->showOne($examQuestion, 200);
    }

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $examQuestion = ExamQuestion::findOrFail($id);

        if ($examQuestion->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only update your company's exam question", 403);
        }

        if ($request->has('name')) {
            $examQuestion->name = $request->name;
        }

        if ($request->has('exam_question_score_id')) {
            $examQuestion->exam_question_score_id = $request->exam_question_score_id;
        }

        $examQuestion->save();

        return $this->showOne($examQuestion, 200);
    }

    /**
     * Remove the examquestion with remove all part, question, answer related. 
     */
    public function destroy($id)
    {
        $examQuestion = ExamQuestion::findOrFail($id); 
        try {
            if ($examQuestion->company_id !== Auth::user()->company_id) {
                return $this->errorResponse("You can only delete your company's exam question", 403);
            } 
            $examQuestion->parts()->each(function ($part) {
                $part->questions()->each(function ($question) {
                    $question->answers()->each(function ($answer) {
                        $answer->delete();
                    });
                    $question->delete();
                });
                $part->delete();
            }); 
            $examQuestion->delete(); 
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse(["status" => "Delete successfully!"], 200);
    }

    public function uploadAudio(Request $request, $id)
    {
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');

            $examQuestion = ExamQuestion::findOrFail($id);
            $name = $file->getClientOriginalName();
            $fileDirectories = "audio";
            $fileName = time() . "_" . str_random(4) . "_" . $name;
            while (UploadFileToS3Controller::exist($fileName)) {
                $fileName = time() . "_" . str_random(4) . "_" . $name;
            }
            $urlToAudio = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
            if ($examQuestion->audio != null) {
                UploadFileToS3Controller::destroy($examQuestion->audio); 
            }
            $examQuestion->audio = $urlToAudio;

            $examQuestion->save();
            return $this->successResponse(['url' => $urlToAudio], 200);
        }
        return $this->errorResponse('No files ', 400);
    }

    public function getExamQuestionForCompany()
    {
        // GET ALL EXAM_QUESTION OF SYSTEM FOR COMPANY 
        $listExamQuestions = ExamQuestion::where('company_id', Company::SYSTEM_COMPANY)
            ->where('for_system', false)
            ->where('exam_question_score_id', '!=', null)
            ->orderBy('id')
            ->get();
        return $this->showAll($listExamQuestions);
    }
}
