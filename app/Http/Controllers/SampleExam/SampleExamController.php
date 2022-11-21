<?php

namespace App\Http\Controllers\SampleExam;

use App\Company;
use App\Exam;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class SampleExamController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $sampleExams = Exam::where('company_id', '=', Company::SYSTEM_COMPANY)
            ->with('exam_question')
            ->orderBy('created_at', 'desc')
            ->get()
            ->makeHidden(['exam_question_id']);

        return $this->showAll($sampleExams);
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

        if ($validator->passes()) { 
            $data['name'] = $dataFromClient['name'];
            $data['description'] = $dataFromClient['description'];
            $data['exam_question_id'] = $dataFromClient['exam_question_id'];
            $data['is_shuffle_answer'] = $dataFromClient['is_shuffle_answer'] ? 1 : 0;
            $data['is_allow_view_answer'] = Exam::ALLOW_VIEW_ANSWERS;
            $data['company_id'] = Company::SYSTEM_COMPANY;
            $data['status'] = Exam::ONGOING;
            if ($dataFromClient['from_date']) {
                $data['from_date'] = $dataFromClient['from_date'];
            }
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "sample_exams";
                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
                $data['image_preview'] = $urlToImage;
            }

            $sampleExam = Exam::create($data);

            return $this->showOne($sampleExam, 201);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $sampleExam = Exam::findOrFail($id);

        return $this->showOne($sampleExam);
    }


    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $sampleExam = Exam::findOrfail($id);

        $rules = [
            'name' => 'required',
            'description' => 'required',
            'exam_question_id' => 'required',
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        if ($validator->passes()) {
            $sampleExam->name = $dataFromClient['name'];
            $sampleExam->description = $dataFromClient['description'];
            $sampleExam->exam_question_id = $dataFromClient['exam_question_id'];
            $sampleExam->is_shuffle_answer = $dataFromClient['is_shuffle_answer'] ? 1 : 0;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileDirectories = "sample_exams";
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
                if ($sampleExam->image_preview != null) {
                    UploadFileToS3Controller::destroy($sampleExam->image_preview); 
                }
                $sampleExam->image_preview = $urlToImage;
            }

            $sampleExam->save();

            return $this->showOne($sampleExam, 200);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $sampleExam = Exam::findOrFail($id);

        if (!$this->checkExamBelongToCompany($sampleExam)) {
            return $this->errorResponse("You can only delete your company's exam", 403);
        }

        if($sampleExam->status) {
            return $this->errorResponse("You cannot delete an ongoing exam", 403);
        }

        if ($sampleExam->image_preview != null) {
            UploadFileToS3Controller::destroy($sampleExam->image_preview);  
        }
        $sampleExam->delete();
        return $this->successResponse(["status" => "Delete successfully!"], 200);
    }

    // Update active status
    public function updateStatus($id)
    {
        $sampleExam = Exam::findOrFail($id);

        $status = $sampleExam->status;
        if ($status) {
            $sampleExam->status = Exam::STOP;
        } else {
            $sampleExam->status = Exam::ONGOING;
        }

        $sampleExam->save();

        return $this->showOne($sampleExam, 200);
    }

    //Update allow view answer 
    public function updateAllowViewAnswer($id)
    {
        $sampleExam = Exam::findOrFail($id);

        $is_allow_view_answer = $sampleExam->is_allow_view_answer;
        if ($is_allow_view_answer) {
            $sampleExam->is_allow_view_answer = Exam::DENY_VIEW_ANSWERS;
        } else {
            $sampleExam->is_allow_view_answer = Exam::ALLOW_VIEW_ANSWERS;
        }

        $sampleExam->save();

        return $this->showOne($sampleExam, 200);
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
}
