<?php

namespace App\Http\Controllers\Category;

use App\Category;
use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('admin');
    }
    /**
     * Get all category 
     */
    public function index()
    {
        $categories = Category::orderBy('id')->get();

        return $this->showAll($categories);
    } 

    /**
     * Store a new category. 
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:category',
            'description' => 'required'
        ];

        $this->validate($request, $rules);

        $data = $request->all(); 
        
        $category = Category::create($data);

        return $this->showOne($category, 201);
    }
 
    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];

        $this->validate($request, $rules);

        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return $this->showOne($category, 200);
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        try {
            $category->articles()->each(function ($article) {
                $article->delete();
            });
            $category->delete();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse("Deleted sucessfully");
    }
}
