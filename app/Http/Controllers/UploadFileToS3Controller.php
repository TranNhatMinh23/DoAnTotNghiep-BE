<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class UploadFileToS3Controller extends Controller
{
    public function index()
    {
        // $url = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $images = [];
        $files = Storage::disk('s3')->files('Emails image');
        foreach ($files as $file) {
            $images[] = [
                'name' => str_replace('Emails image/', '', $file),
                'src' => Storage::disk('s3')->url($file)
            ];
        }

        return response()->json($images); 
        /** GET URL FILE */
        // return response()->json(Storage::disk('s3')->url('/avatars/6.jpg')); 
        /**CHECK ALL DIRECTORIES */
        // return response()->json(Storage::disk('s3')->allDirectories('')); 
        /**GET ALL FILES */
        // return response()->json(Storage::disk('s3')->allFiles('')); 
    }


    /**
     * Store a newly created resource in storage. 
     */
    public static function store($files, $directories, $fileName)
    { 
        $data = "";
        if ($files) {  
            $filePath = $directories . '/' . $fileName;
            Storage::disk('s3')->put($filePath, fopen($files, 'r+'));
            $data = $filePath; 
        }
        return $data;
    }


    /**
     * Remove the specified resource from storage. 
     */
    public static function destroy($filePath)
    {
        if($filePath){
            Storage::disk('s3')->delete($filePath);
            return response('Files was deleted successfully', 200); 
        } else {
            return response()->json("Cannot delete file!", 500);
        }
    }

    public static function exist($filePath) {
        return Storage::disk('s3')->exists($filePath);
    }
}