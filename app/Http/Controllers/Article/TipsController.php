<?php

namespace App\Http\Controllers\Article;

use App\Article;
use App\Category;
use App\Http\Controllers\Controller;

class TipsController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $tips = Article::where('category_id', Category::TIPS_ID)
            ->where('status', Article::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($tips, 200);
    }


    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $tip = Article::findOrFail($id);

        if (!$tip->status) {
            return response()->json(['error' => "Tips is blocking so you cannot access!"], 403);
        }

        if ($tip->category_id != Category::TIPS_ID) {
            return response()->json(['error' => "Not found!"], 404);
        }

        $relatedTips = Article::where('category_id', Category::TIPS_ID)
            ->where('id', '!=', $tip->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(); 
        
        $data['related'] = $relatedTips;
        $data['detail'] = $tip;

        return response()->json($data, 200);
    }
}
