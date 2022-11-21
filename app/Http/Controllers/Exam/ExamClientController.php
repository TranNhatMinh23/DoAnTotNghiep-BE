<?php

namespace App\Http\Controllers\Exam;

use App\Answer;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Participant;
use App\Report;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ExamClientController extends ApiController
{
    /**
     * Display a listing of the resource. 
     */
    public function index(Request $request)
    {
        $currentEmailUser = Auth::user()->email;
        $regrexEmail = strstr($currentEmailUser, "@");
        $searchBy = $request->by;
        $currentDate = new DateTime('today');

        if ($searchBy === "all") {
            $listExams = Participant::where(function ($query) use ($currentEmailUser, $regrexEmail) {
                $query->where('email', $currentEmailUser)
                    ->orWhere('email', $regrexEmail);
            })
                ->join('exam', 'participant.exam_id', '=', 'exam.id')
                ->where('exam.status', Exam::ONGOING)
                ->orderBy('exam.created_at', 'desc')
                ->with('company')
                ->paginate(8);
        } else if ($searchBy === "ongoing") {
            $listExams = Participant::where(function ($query) use ($currentEmailUser, $regrexEmail) {
                $query->where('email', $currentEmailUser)
                    ->orWhere('email', $regrexEmail);
            })
                ->join('exam', 'participant.exam_id', '=', 'exam.id')
                ->where('exam.status', Exam::ONGOING)
                ->where('from_date', '<=', $currentDate)
                ->where('to_date', '>=', $currentDate)
                ->orderBy('exam.created_at', 'desc')
                ->with('company')
                ->paginate(8);
        } else if ($searchBy === "expired") {
            $listExams = Participant::where(function ($query) use ($currentEmailUser, $regrexEmail) {
                $query->where('email', $currentEmailUser)
                    ->orWhere('email', $regrexEmail);
            })
                ->join('exam', 'participant.exam_id', '=', 'exam.id')
                ->where('exam.status', Exam::ONGOING)
                ->where('to_date', '<', $currentDate)
                ->orderBy('exam.created_at', 'desc')
                ->with('company')
                ->paginate(8);
        } else if ($searchBy === "upcomming") {
            $listExams = Participant::where(function ($query) use ($currentEmailUser, $regrexEmail) {
                $query->where('email', $currentEmailUser)
                    ->orWhere('email', $regrexEmail);
            })
                ->join('exam', 'participant.exam_id', '=', 'exam.id')
                ->where('exam.status', Exam::ONGOING)
                ->where('from_date', '>', $currentDate)
                ->orderBy('exam.created_at', 'desc')
                ->with('company')
                ->paginate(8);
        } else {
            $listExams['data'] = [];
        }

        if (!empty($listExams['data'])) {
            foreach ($listExams as $item) {
                $item->makeHidden([
                    'email',
                    'regrex',
                    'company_id',
                    'is_allow_view_answer',
                    'is_shuffle_answer',
                    'exam_question_id'
                ]);
            }
        }

        return $this->successResponse($listExams);
    }

    /**
     * Get an exam
     */
    public function show($id)
    {
        $exam = Exam::findOrFail($id);
        if ($exam->status == Exam::STOP) {
            return $this->errorResponse("Exam is stopping, You cannot access!", 403);
        }

        //check is running exam
        $fromDate = new DateTime($exam->from_date);
        $toDate = new DateTime($exam->to_date);
        $nowDate = new DateTime();
        $notExpiredExam = $fromDate <= $nowDate && $toDate >= $nowDate;

        if ($nowDate < $fromDate) {
            $rangeTimeTakeExam = $fromDate->format('d-m-Y') . " to " . $toDate->format('d-m-Y');
            $note = "It is not time for this exam yet. The exam will start from the date of " . $rangeTimeTakeExam;
            return $this->errorResponse($note, 404);
        }

        if (!$notExpiredExam) {
            return $this->errorResponse("Exam has expired!", 404);
        } else {
            //Tim trong report xem user da thuc hien bai test chua, neu roi se bao loi, con ko thi chekc tiep
            $currentUser = Auth::user();
            $checkTakenExamOrNot = Report::where('user_id', $currentUser->id)
                ->where('exam_id', $exam->id)
                ->get();

            if (!$checkTakenExamOrNot->isEmpty()) {
                return $this->errorResponse("You have already taken this exam!", 403);
            }

            //Tim trong participant xem user co quyen truy cap khong
            $currentEmailUser = $currentUser->email;
            $regrexEmail = strstr($currentEmailUser, "@");

            $participant = Participant::where('exam_id', $exam->id)
                ->where(function ($query) use ($regrexEmail, $currentEmailUser) {
                    return $query->where('email', '=', $regrexEmail)
                        ->orWhere('email', '=', $currentEmailUser);
                })->get();

            if ($participant->isEmpty()) {
                return $this->errorResponse("You haven't permisson to access this exam!", 403);
            } else {
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
                            ->makeHidden(['is_correct_flag'])
                            ->toArray();
                        // check de shuffle cau tra loi o day 
                        if ($exam->is_shuffle_answer) {
                            $arrayCheck = [5, 6, 7];
                            if (in_array($part->part_no, $arrayCheck)) {
                                shuffle($answers);
                            }
                        }
                        $ques['answers'] = $answers;
                    }
                    $part['questions'] = $questions;
                }

                $examQuestion['parts'] = $parts;
                $examQuestion['exam_name'] = $exam->name;
                $examQuestion['exam_id'] = $exam->id;
                return $this->showOne($examQuestion);
            }
        }
    }

    public function getExamBeforeTaken($id)
    {
        $exam = Exam::findOrFail($id);
        if ($exam->status == Exam::STOP) {
            return $this->errorResponse("Exam is stopping, You cannot access!", 403);
        }
        //Tim trong report xem user da thuc hien bai test chua, neu roi se bao loi, con ko thi check tiep
        $currentUser = Auth::user();
        $checkTakenExamOrNot = Report::where('user_id', $currentUser->id)
            ->where('exam_id', $exam->id)
            ->get();
        if (!$checkTakenExamOrNot->isEmpty()) {
            return $this->errorResponse("You have already taken this exam!", 403);
        }

        return $this->showOne($exam);
    }
}
