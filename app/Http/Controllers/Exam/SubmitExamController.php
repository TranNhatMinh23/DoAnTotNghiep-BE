<?php

namespace App\Http\Controllers\Exam;

use App\Answer;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\SendEmail\EmailController;
use App\Report;
use App\Result;
use App\ScoreMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitExamController extends ApiController
{
    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);
        if ($exam->status == Exam::STOP) {
            return $this->errorResponse("Exam is stopping, You cannot access!", 403);
        }

        $user = Auth::user();

        //Create Report
        $report['user_id'] = $user->id;
        $report['exam_id'] = $exam->id;

        $report = Report::create($report);

        //get all answer client
        $listExamAnswerClient = $request->data;
        //Save answer of user
        foreach ($listExamAnswerClient as $answer) {
            $result['report_id'] = $report->id;
            $result['question_id'] = $answer['questionId'];
            $result['your_answer'] = $answer['yourAnswer'];
            $result['your_answer_code'] = $answer['yourAnswerCode'];
            $result['position_1'] = $answer['position_1'];
            $result['position_2'] = $answer['position_2'];
            $result['position_3'] = $answer['position_3'];
            $result['position_4'] = $answer['position_4'];
            Result::create($result);
        }

        //get exam-question used by exam
        $examQuestion = $exam->exam_question()->first();
        //get score
        $allResults = $report->results()->get();
        $numOfListeningCorrectAnswer = $this->getListeningScore($allResults);
        $numOfReadingCorrectAnswer = $this->getReadingScore($allResults);

        $scoreListening = ScoreMapping::where('exam_question_score_id', $examQuestion->exam_question_score_id)
            ->where('num_of_question', $numOfListeningCorrectAnswer)
            ->first();
        $scoreReading = ScoreMapping::where('exam_question_score_id', $examQuestion->exam_question_score_id)
            ->where('num_of_question', $numOfReadingCorrectAnswer)
            ->first();
        // //Response results
        $listeningScore = 0;
        $readingScore = 0;
        if ($scoreReading && $scoreListening) {
            $listeningScore = $scoreListening->listening_score ? $scoreListening->listening_score : 0;
            $readingScore = $scoreReading->reading_score ? $scoreReading->reading_score : 0;
        } 
        $report['listening_score'] = $listeningScore;
        $report['reading_score'] = $readingScore;
        $report['num_listening'] = $numOfListeningCorrectAnswer;
        $report['num_reading'] = $numOfReadingCorrectAnswer;
        $report->save();

        $dataResponse['listening_score'] = $listeningScore;
        $dataResponse['reading_score'] = $readingScore;
        $dataResponse['num_listening'] = $numOfListeningCorrectAnswer;
        $dataResponse['num_reading'] = $numOfReadingCorrectAnswer;
        $dataResponse['total_score'] = $listeningScore + $readingScore;
        $dataResponse['exam'] = $exam;
        $dataResponse['date'] = $report->created_at;
        $dataResponse['report'] = $report->id; 

        EmailController::sendEmailResult($dataResponse, $user);
        return $this->successResponse($dataResponse); 
    }

    public function getListeningScore($results)
    {
        $count = 0;
        foreach ($results as $result) {
            $question = $result->question()->first();
            if ($question->question_no <= 100 && $result->your_answer_code != null && $result->your_answer != null) {
                $checkIsCorrectAnswer = Answer::where('id', '=', $result->your_answer_code)->first();
                if ($checkIsCorrectAnswer && $checkIsCorrectAnswer->is_correct_flag) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function getReadingScore($results)
    {
        $count = 0;
        foreach ($results as $result) {
            $question = $result->question()->first();
            if ($question->question_no > 100 && $question->question_no <= 200 && $result->your_answer_code != null && $result->your_answer != null) {
                $checkIsCorrectAnswer = Answer::where('id', '=', $result->your_answer_code)->first();
                if ($checkIsCorrectAnswer && $checkIsCorrectAnswer->is_correct_flag) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Display the specified resource. 
     */
    public function show($reportId)
    {
        $report = Report::findOrFail($reportId);

        if (Auth::user()->id != $report->user_id) {
            return $this->errorResponse("You do not have access!", 403);
        }

        $exam = Exam::find($report->exam_id);
        $report['total_score'] = $report->reading_score + $report->listening_score;
        $report['exam'] = $exam;

        return $this->showOne($report);
    }
}
