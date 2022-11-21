<?php

namespace App\Http\Controllers\Part;

use App\Answer;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use Illuminate\Http\Request;
use App\Part;

class PartController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $part = Part::findOrFail($id);
        $questions = $part->questions()->orderBy('question_no')->get();

        foreach ($questions as $ques) {
            $answers = Answer::where('question_id', '=', $ques->id)
                ->orderBy('id')
                ->get()
                ->toArray();  
            $ques['answers'] = $answers;
        } 
        
        $part['questions'] = $questions;

        return $this->showOne($part, 200);
    }

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $part = Part::findOrFail($id);

        if ($request->has('ex_answer_key')) {
            $part->ex_answer_key = $request->ex_answer_key;
        }
        if ($request->has('ex_answera')) {
            $part->ex_answera = $request->ex_answera;
        }
        if ($request->has('ex_answerb')) {
            $part->ex_answerb = $request->ex_answerb;
        }
        if ($request->has('ex_answerc')) {
            $part->ex_answerc = $request->ex_answerc;
        }
        if ($request->has('ex_answerd')) {
            $part->ex_answerd = $request->ex_answerd;
        }

        $part->save();

        return $this->successResponse(["success"=> true, "message" => "Update answer example successfully"], 200);
    }

    public function uploadExamImage(Request $request, $id)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = strtolower($file->getClientOriginalExtension());

            if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                return $this->errorResponse('fail file_extension', 400);
            }

            $part = Part::findOrFail($id);
            $fileDirectories = "parts";
            $name = $file->getClientOriginalName();
            $fileName = time() . "_" . str_random(4) . "_" . $name;
            while (UploadFileToS3Controller::exist($fileName)) {
                $fileName = time() . "_" . str_random(4) . "_" . $name;
            }
            $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);
            if ($part->ex_question_image != null) {
                UploadFileToS3Controller::destroy($part->ex_question_image); 
            }
            $part->ex_question_image = $urlToImage;
            $part->save();
            return $this->successResponse(['url' => $urlToImage], 200);
        }
        return $this->errorResponse('No files ', 400);
    }

    public function deleteExamImage($id)
    {
        $part = Part::findOrFail($id);
        if ($part->ex_question_image != null) { 
            UploadFileToS3Controller::destroy($part->ex_question_image); 
            $part->ex_question_image = null;
            $part->save();
            return $this->successResponse("Successfully!", 200);
        }

        return $this->errorResponse("Cannot delete this image", 400);
    }
}
