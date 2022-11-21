<?php

namespace App\Http\Controllers\Slide;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use App\Slide;
use Illuminate\Http\Request;
use Validator;

class SlideController extends ApiController
{
    public function __construct(){
        parent::__construct(); 
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     * 
     */
    public function index()
    {
        $slides = Slide::orderBy('id')->get();

        return $this->showAll($slides);
    }
 
    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request)
    {
        $rules = [ 
            'description' => 'required' 
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        if ($validator->passes()) {
            $data['description'] = $dataFromClient['description']; 
            $data['status'] = Slide::ACTIVE;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "slides";
                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }

                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);
                $data['url'] = $urlToImage;
            }

            $slide = Slide::create($data);

            return $this->showOne($slide, 201);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $slide = Slide::findOrFail($id);
        return $this->showOne($slide, 200);
    } 

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $slide = Slide::findOrFail($id);

        $rules = [ 
            'description' => 'required' 
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        if ($validator->passes()) {
            $slide->description = $dataFromClient['description']; 
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "slides";

                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName); 
                if ($slide->url != null) {
                    UploadFileToS3Controller::destroy($slide->url); 
                } 
                $slide->url = $urlToImage;
            }

            $slide->save();

            return $this->showOne($slide, 201);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        } 
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $slide = Slide::findOrFail($id);
        if ($slide->url != null) { 
            UploadFileToS3Controller::destroy($slide->url); 
        }
        $slide->delete();
        return $this->successResponse(["status" => "Delete successfully!"], 200); 
    }

     //updateStatus
     public function updateStatus($slideId){
        $slide = Slide::findOrFail($slideId);

        $status = $slide->status;
        if ($status) {
            $slide->status = Slide::DISABLED;
        } else {
            $slide->status = Slide::ACTIVE;
        } 

        $slide->save();
        return $this->successResponse("Updated status successfully", 200);
    }
}
