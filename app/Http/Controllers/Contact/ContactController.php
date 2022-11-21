<?php

namespace App\Http\Controllers\Contact;

use App\Contact;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request; 

class ContactController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $contacts = Contact::all();

        return $this->showAll($contacts);
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return $this->successResponse("Deleted successfully", 200);
    }

    //updateStatus
    public function updateStatus($contactId){
        $contact = Contact::findOrFail($contactId);
        $contact->status = Contact::PROCESSED;

        $contact->save();
        return $this->successResponse("Updated status successfully", 200);
    }
}
