<?php

namespace App\Http\Controllers\Contact;

use App\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactClientController extends Controller
{

    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request)
    {
        $data['username'] = $request->name;
        $data['email'] = $request->email;
        $data['content'] = $request->content;
        $data['status'] = Contact::PENDDING;

        Contact::create($data);

        return response()->json("Sent success", 200);
    } 
}
