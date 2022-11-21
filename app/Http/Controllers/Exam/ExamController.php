<?php

namespace App\Http\Controllers\Exam;

use Excel;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use App\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ExamController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('manager');
    }

    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $examSchedules = Exam::where('company_id', '=', $companyId)
            ->with('exam_question')
            ->orderBy('id')
            ->get()
            ->makeHidden(['exam_question_id']);

        return $this->showAll($examSchedules);
    }

    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:exam',
            'description' => 'required',
            'exam_question_id' => 'required',
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        $companyId = Auth::user()->company_id;

        if ($validator->passes()) {
            $data['name'] = $dataFromClient['name'];
            $data['description'] = $dataFromClient['description'];
            $data['exam_question_id'] = $dataFromClient['exam_question_id'];
            $data['is_shuffle_answer'] = $dataFromClient['is_shuffle_answer'] ? 1 : 0;
            if ($dataFromClient['from_date']) {
                $data['from_date'] = $dataFromClient['from_date'];
            }
            if ($dataFromClient['to_date']) {
                $data['to_date'] = $dataFromClient['to_date'];
            }
            $data['company_id'] = $companyId;
            $data['status'] = Exam::ONGOING;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "exams";

                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
                $data['image_preview'] = $urlToImage;
            }

            $exam = Exam::create($data);
            $regrexEmail = $dataFromClient['regrexEmail'];
            $listEmails =  $dataFromClient['listsEmail'];
            if ($regrexEmail || count($listEmails) > 0) {
                $this->storePaticipants($exam, $regrexEmail, $listEmails);
            }
            return $this->showOne($exam, 201);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    public function storePaticipants(Exam $exam, $regrexEmail, $listEmails)
    {
        $examId = $exam->id;
        if ($regrexEmail) {
            $regrexData['email'] = $regrexEmail;
            $regrexData['regrex'] = Participant::IS_REGREX;
            $regrexData['exam_id'] = $examId;
            Participant::create($regrexData);
        }
        if (count($listEmails) > 0) {
            foreach ($listEmails as $email) {
                $regrexData['email'] = $email;
                $regrexData['regrex'] = Participant::NOT_REGREX;
                $regrexData['exam_id'] = $examId;
                Participant::create($regrexData);
            }
        }
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $exam = Exam::findOrFail($id);
        if (!$this->checkExamBelongToCompany($exam)) {
            return $this->errorResponse("You can only get your company's exam", 403);
        }
        $listEmailInvited = Participant::where('exam_id', $exam->id)
            ->where('regrex', Participant::NOT_REGREX)
            ->orderBy('created_at', 'desc')
            ->get();
        $participants = Participant::where('exam_id', $exam->id)->get();
        $listEmails = [];
        $regrexEmail = '';
        foreach ($participants as $item) {
            if ($item->regrex) {
                $regrexEmail = $item->email;
            } else {
                array_push($listEmails, $item->email);
            }
        }
        $exam['regrex'] = $regrexEmail;
        $exam['listEmails'] = $listEmails;
        $exam['listEmailInvited'] = $listEmailInvited;
        return $this->showOne($exam);
    }

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrfail($id);
        if (!$this->checkExamBelongToCompany($exam)) {
            return $this->errorResponse("You can only update your company's exam", 403);
        }

        $dataFromClient = json_decode($request->get('data'), true);
        $exam->name = $dataFromClient['name'];
        $exam->description = $dataFromClient['description'];
        $exam->exam_question_id = $dataFromClient['exam_question_id'];
        $exam->is_shuffle_answer = $dataFromClient['is_shuffle_answer'] ? 1 : 0;
        if ($dataFromClient['from_date']) {
            $exam->from_date = $dataFromClient['from_date'];
        }
        if ($dataFromClient['to_date']) {
            $exam->to_date = $dataFromClient['to_date'];
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = strtolower($file->getClientOriginalExtension());

            if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                return $this->errorResponse('fail file_extension', 400);
            }

           $name = $file->getClientOriginalName();
           $fileName = time() . "_" . str_random(4) . "_" . $name;
           $fileDirectories = "exams";

            while (UploadFileToS3Controller::exist($fileName)) {
                $fileName = time() . "_" . str_random(4) . "_" . $name;
            }
            $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
            if ($exam->image_preview != null) {
                UploadFileToS3Controller::destroy($exam->image_preview); 
            }
            $exam->image_preview = $urlToImage;
        }

        //Participant
        $regrexEmail = $dataFromClient['regrexEmail'];
        $listEmailsClient =  $dataFromClient['listsEmail'];

        //Delete email not in array emails from client update
        Participant::where('exam_id', $exam->id)
            ->where('regrex', Participant::NOT_REGREX)
            ->whereNotIn('email', $listEmailsClient)
            ->delete();

        //Create list new participants not in DB from client update
        if (count($listEmailsClient) > 0) {
            foreach ($listEmailsClient as $email) {
                Participant::updateOrCreate(
                    ['exam_id' => $exam->id, 'email' => $email, 'regrex' => Participant::NOT_REGREX],
                    ['email' => $email]
                );
            }
        }

        //Update or Create Regrex
        Participant::updateOrCreate(
            ['exam_id' => $exam->id, 'regrex' => Participant::IS_REGREX],
            ['email' => $regrexEmail]
        );

        $exam->save();

        return $this->show($id);
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $examSchedule = Exam::findOrFail($id);
        if (!$this->checkExamBelongToCompany($examSchedule)) {
            return $this->errorResponse("You can only delete your company's exam", 403);
        }

        if ($examSchedule->status) {
            return $this->errorResponse("You cannot delete an ongoing exam", 403);
        }

        if ($examSchedule->image_preview != null) {
            UploadFileToS3Controller::destroy($examSchedule->image_preview);  
        }

        Participant::where('exam_id', $examSchedule->id)->delete();
        $examSchedule->delete();
        return $this->successResponse(["status" => "Delete successfully!"], 200);
    }

    public function updateStatus($id)
    {
        $exam = Exam::findOrFail($id);
        if (!$this->checkExamBelongToCompany($exam)) {
            return $this->errorResponse("You can only update status on your company's exam", 403);
        }

        $status = $exam->status;
        if ($status) {
            $exam->status = Exam::STOP;
        } else {
            $exam->status = Exam::ONGOING;
        }

        $exam->save();

        return $this->showOne($exam, 200);
    }

    public function updateAllowViewAnswer($id)
    {
        $exam = Exam::findOrFail($id);
        if (!$this->checkExamBelongToCompany($exam)) {
            return $this->errorResponse("You can only update status on your company's exam", 403);
        }

        $is_allow_view_answer = $exam->is_allow_view_answer;
        if ($is_allow_view_answer) {
            $exam->is_allow_view_answer = Exam::DENY_VIEW_ANSWERS;
        } else {
            $exam->is_allow_view_answer = Exam::ALLOW_VIEW_ANSWERS;
        }

        $exam->save();

        return $this->showOne($exam, 200);
    }

    //Check exam belong to Company or No
    public function checkExamBelongToCompany(Exam $exam)
    {
        $companyId = Auth::user()->company_id;

        if ($exam->company_id !== $companyId) {
            return false;
        } else {
            return true;
        }
    }

    //Deleted email invitation
    public function deleteEmailInvitation($id)
    {
        $emailInvited = Participant::findOrFail($id);
        $emailInvited->delete();

        return $this->successResponse("Deleted email invitation successfully");
    }

    //importEmailInvitationFile
    public function importEmailInvitationFile($examsId, Request $request)
    {
        if (!$request->file("files")) {
            return response()->json(["error" => "Error, No File!"], 400);
        }

        $exams = Exam::findOrFail($examsId);

        if ($exams->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only update your company's exams", 403);
        }

        $extensions = array("xls", "xlsx");
        $resultCheckType = array($request->file('files')->getClientOriginalExtension());

        if (in_array($resultCheckType[0], $extensions)) {
            $path = $request->file('files')->getRealPath();
            $data = Excel::load($path)->get();

            $torarray = $data->toArray();
            $line0 = $torarray[0];
            $headers = array_keys($line0);

            if (!$this->checkHeaderExcelFile($headers)) {
                return response()->json(["error" => "Wrong exams invitation file content format!"], 400);
            }

            if ($data->count()) {
                $listEmailInvitation = [];
                foreach ($data as $item) {
                    if (filter_var($item->email, FILTER_VALIDATE_EMAIL)) {
                        array_push($listEmailInvitation, $item->email);
                    }
                }

                //Delete email not in array emails from client update
                Participant::where('exam_id', $exams->id)
                    ->where('regrex', Participant::NOT_REGREX)
                    ->whereNotIn('email', $listEmailInvitation)
                    ->delete();

                //Create list new participants not in DB from client update
                if (count($listEmailInvitation) > 0) {
                    foreach ($listEmailInvitation as $email) {
                        Participant::updateOrCreate(
                            ['exam_id' => $exams->id, 'email' => $email, 'regrex' => Participant::NOT_REGREX],
                            ['email' => $email]
                        );
                    }
                }
                return $this->show($examsId);
            } else {
                return response()->json(["error" => "File error!"], 400);
            }
        } else {
            return response()->json(["error" => "Must be excel file, it has .xls or .xlsx extensions!"], 400);
        }

        return response()->json(["success" => true], 200);
    }

    public function checkHeaderExcelFile($listHeaderFromFile)
    {
        $checkHeaderCollumns = array("stt", "email");
        if (count($listHeaderFromFile) !== 2) {
            return false;
        } else {
            for ($i = 0; $i < 2; $i++) {
                if (!in_array($listHeaderFromFile[$i], $checkHeaderCollumns)) return false;
            }
            return true;
        }
    }
}
