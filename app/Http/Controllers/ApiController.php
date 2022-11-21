<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser; 

class ApiController extends Controller
{
    use ApiResponser;

    public function __construct(){
        $this->middleware(['jwt.auth', 'activeUser']);
    }
}
