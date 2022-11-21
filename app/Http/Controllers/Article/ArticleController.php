<?php

namespace App\Http\Controllers\Article;

use App\Article;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UploadFileToS3Controller;
use Illuminate\Http\Request;
use Validator;

class ArticleController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('admin', ['except' => ['show']]);
    }

    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')
            ->with('category')
            ->get();

        return $this->showAll($articles);
    }


    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required'
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        if ($validator->passes()) {
            $data['title'] = $dataFromClient['title'];
            $data['description'] = $dataFromClient['description'];
            $data['content'] = $dataFromClient['content'];
            $data['category_id'] = $dataFromClient['category_id'];
            $data['status'] = Article::ACTIVE;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "articles";
                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);
                $data['image_url'] = $urlToImage;
            }

            $article = Article::create($data);

            return $this->showOne($article, 201);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        return $this->showOne($article);
    }

    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $rules = [
            'title' => 'required',
            'description' => 'required'
        ];

        $dataFromClient = json_decode($request->get('data'), true);

        $validator = Validator::make($dataFromClient, $rules);

        if ($validator->passes()) {
            $article->title = $dataFromClient['title'];
            $article->description = $dataFromClient['description'];
            $article->content = $dataFromClient['content'];
            $article->category_id = $dataFromClient['category_id'];
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_extension = strtolower($file->getClientOriginalExtension());

                if ($file_extension != 'jpg' && $file_extension != 'png' && $file_extension != 'jpeg') {
                    return $this->errorResponse('fail file_extension', 400);
                }

                $name = $file->getClientOriginalName();
                $fileName = time() . "_" . str_random(4) . "_" . $name;
                $fileDirectories = "articles";
                while (UploadFileToS3Controller::exist($fileName)) {
                    $fileName = time() . "_" . str_random(4) . "_" . $name;
                }
                $urlToImage = UploadFileToS3Controller::store($file, $fileDirectories, $fileName);
                if ($article->image_url != null) {
                    UploadFileToS3Controller::destroy($article->image_url);
                }
                $article->image_url = $urlToImage;
            }

            $article->save();

            return $this->showOne($article, 200);
        } else {
            return $this->errorResponse($validator->errors()->first(), 400);
        }
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        if ($article->image_url != null) {
            UploadFileToS3Controller::destroy($article->image_url);
        }
        $article->delete();
        return $this->successResponse("Deleted sucessfull!");
    }

    // Update active status
    public function updateStatus($id)
    {
        $article = Article::findOrFail($id);

        $status = $article->status;
        if ($status) {
            $article->status = Article::DISABLE;
        } else {
            $article->status = Article::ACTIVE;
        }

        $article->save();

        return $this->showOne($article, 200);
    }
}
