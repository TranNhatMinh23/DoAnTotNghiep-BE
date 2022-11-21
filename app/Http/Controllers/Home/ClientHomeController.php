<?php

namespace App\Http\Controllers\Home;

use App\Article;
use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientHomeController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        //Take 6 notifications
        $notification = Category::where('name', 'NOTIFICATIONS')->first();
        $listFiveNotifications = Article::where('category_id', $notification->id)
            ->where('status', Article::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->makeHidden(['status', 'image_url']);
        $notification['data'] = $listFiveNotifications ? $listFiveNotifications : [];

        //Take 5 news from another category (except tips and notifications)
        $news = Article::where('category_id', '<>', Category::TIPS_ID)
            ->where('category_id', '<>', Category::NOTIFICATION_ID)
            ->where('status', Article::ACTIVE)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        foreach ($news as $n) {
            $category = Category::where('id', $n->category_id)->first();
            $n['category_name'] = $category->name;
            $n->makeHidden(['category_id', 'status']);
        }

        $dataResponse['notifications'] = $notification;
        $dataResponse['news'] = $news ? $news : [];

        return response()->json($dataResponse, 200);
    }

    public function getAllArticles(Request $request)
    {
        $typeCategory = $request->type;
        if ($typeCategory === 'notifications') {
            $data = Article::where('category_id', '=', Category::NOTIFICATION_ID)
                ->where('status', Article::ACTIVE)
                ->orderBy('created_at')
                ->paginate(15);
        } else {
            $data = Article::where('category_id', '<>', Category::NOTIFICATION_ID)
                ->where('category_id', '<>', Category::TIPS_ID)
                ->where('status', Article::ACTIVE)
                ->orderBy('created_at')
                ->paginate(15);
        }

        foreach ($data as $n) {
            $category = Category::where('id', $n->category_id)->first();
            $n['category_name'] = $category->name;
            $n->makeHidden(['category_id', 'status']);
        }

        return response()->json($data, 200);
    }

    public function getDetailArticles($id){
        $data = Article::where('id', $id)
                    ->where('status', Article::ACTIVE)
                    ->first();
        if($data !== null) {
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Not found!'], 404);
        }
    }
}
