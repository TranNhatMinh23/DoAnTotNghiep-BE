<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait ApiResponser {
  protected function successResponse($data, $code = 200){
    return response()->json($data, $code);
  }

  protected function errorResponse($message, $code = 400){
    return response()->json(['error'=> $message, 'code'=>$code], $code);
  }

  protected function showAll(Collection $collection, $code = 200){
    return $this->successResponse($collection, $code);
  }

  protected function showOne(Model $instance, $code = 200){ 
    return $this->successResponse($instance, $code);
  }
  
}