<?php

namespace App\Http\Controllers\Question;

use App\Answer;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use App\Question;
use Illuminate\Http\Request;

class QuestionController extends ApiController
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
        $question = Question::findOrFail($id);

        $answers = Answer::where('question_id', '=', $question->id)
            ->orderBy('id')
            ->get()
            ->toArray();
        $question['answers'] = $answers;

        return $this->showOne($question, 200);
    }


    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrfail($id);

        $answerOfQuestion = Answer::where('question_id', '=', $id)
            ->pluck('id')
            ->toArray();
        $dataFromClient = $request->data ? $request->data : [];

        if($request->has('group_desc')){
            $question['group_desc'] = $request['group_desc'];
            $question->save();
        } 
        if($request->has('question_text')){
            $question['question_text'] = $request['question_text'];
            $question->save();
        }
        foreach ($dataFromClient as $item) {
            if (in_array($item['id'], $answerOfQuestion)) {
                $answer = Answer::findOrFail($item['id']);
                $answer['content'] = $item['content'];
                $answer['is_correct_flag'] = $item['is_correct_flag'];
                $answer->save();
            }
        }

        //Response data to client 
        $answersInQuestion = Answer::where('question_id', '=', $id)
            ->orderBy('id')
            ->get()
            ->toArray();
        $question['answers'] = $answersInQuestion;
        return $this->showOne($question, 200);
    }


    public function uploadQuestionImage(Request $request, $id)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = strtolower($file->getClientOriginalExtension());

            if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                return $this->errorResponse('fail file_extension', 400);
            }

            $question = Question::findOrFail($id);
            $name = $file->getClientOriginalName();
            $fileName = time() . "_" . str_random(4) . "_" . $name;
            $fileDirectories = "questions";

            while (UploadFileToS3Controller::exist($fileName)) {
                $fileName = time() . "_" . str_random(4) . "_" . $name;
            }
            $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);
            if ($question->question_image != null) {
                UploadFileToS3Controller::destroy($question->question_image);
            }
            $question->question_image = $urlToImage;
            $question->save();
            return $this->successResponse(['url' => $urlToImage], 200);
        }
        return $this->errorResponse('No files ', 400);
    }

    public function deleteQuestionImage($id)
    {
        $question = Question::findOrFail($id);
        if ($question->question_image != null) { 
            UploadFileToS3Controller::destroy($question->question_image);
            $question->question_image = null;
            $question->save();
            return $this->successResponse("Successfully!", 200);
        }

        return $this->errorResponse("Cannot delete this image", 400);
    }

    public function uploadParagraphImage(Request $request, $id, $paragraphNo)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = strtolower($file->getClientOriginalExtension());

            if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                return $this->errorResponse('fail file_extension', 400);
            }

            $question = Question::findOrFail($id); 

            $name = $file->getClientOriginalName();
            $fileDirectories = "question_paragraphs";
            $fileName = time() . "_" . str_random(4) . "_" . $name;
            while (UploadFileToS3Controller::exist($fileName)) {
                $fileName = time() . "_" . str_random(4) . "_" . $name;
            }
            $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 

            if ($paragraphNo == 1 && $question->paragraph_image1 != null) {
                UploadFileToS3Controller::destroy($question->paragraph_image1);
            } elseif ($paragraphNo == 2 && $question->paragraph_image2 != null) {
                UploadFileToS3Controller::destroy($question->paragraph_image2);
            } elseif ($paragraphNo == 3 && $question->paragraph_image3 != null) {
                UploadFileToS3Controller::destroy($question->paragraph_image3);
            }

            if ($paragraphNo == 1) {
                $question->paragraph_image1 = $urlToImage;
            } elseif ($paragraphNo == 2) {
                $question->paragraph_image2 = $urlToImage;
            } elseif ($paragraphNo == 3) {
                $question->paragraph_image3 = $urlToImage;
            }

            $question->save();
            return $this->successResponse(['url' => $urlToImage], 200);
        }
        return $this->errorResponse('No files ', 400);
    }

    public function deleteParagraphImage($id, $paragraphNo)
    {
        $question = Question::findOrFail($id);
        if ($paragraphNo == 1 && $question->paragraph_image1 != null) { 
            UploadFileToS3Controller::destroy($question->paragraph_image1);
            $question->paragraph_image1 = null;
        } elseif ($paragraphNo == 2 && $question->paragraph_image2 != null) { 
            UploadFileToS3Controller::destroy($question->paragraph_image2);
            $question->paragraph_image2 = null;
        } elseif ($paragraphNo == 3 && $question->paragraph_image3 != null) { 
            UploadFileToS3Controller::destroy($question->paragraph_image3);
            $question->paragraph_image3 = null;
        }
        $question->save();
        return $this->successResponse("Successfully!", 200);
    }
}
