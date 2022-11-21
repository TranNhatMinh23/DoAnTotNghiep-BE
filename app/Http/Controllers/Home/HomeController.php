<?php

namespace App\Http\Controllers\Home;

use App\Article;
use App\Company;
use App\Contact;
use App\Exam;
use App\ExamQuestion;
use App\Http\Controllers\ApiController;
use App\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $currentUser = Auth::user();
        if ($currentUser->company_id === Company::SYSTEM_COMPANY) {
            // Contact new
            $penddingontacts = Contact::where('status', Contact::PENDDING)->get();
            $processedContacts = Contact::where('status', Contact::PROCESSED)->get();
            $contacts = ['pendding' => count($penddingontacts), 'processed' => count($processedContacts)];
            //company
            $companies = Company::where('id', '<>', $currentUser->company_id)->get();
            //users
            $users = User::all();
            $userActive = User::where('active_status', User::ACTIVE_USER)->get();
            $usersResponse = ['total' => count($users), 'active' => count($userActive), 'block' => count($users) - count($userActive)];
            //Articles
            $articles = Article::all();
            $articleActive = Article::where('status', Article::ACTIVE)->get();
            $articlesResponse = ['total' => count($articles), 'active' => count($articleActive), 'block' => count($articles) - count($articleActive)];
            //SampleExam
            $sampleExam = Exam::where('company_id', $currentUser->company_id)->get();
            $sampleExamActive = Exam::where('company_id', $currentUser->company_id)->where('status', Exam::ONGOING)->get();
            $sampleExamResponse = ['total' => count($sampleExam), 'active' => count($sampleExamActive), 'block' => count($sampleExam) - count($sampleExamActive)];
            //ExamQuestion
            $examQuestions = ExamQuestion::where('company_id', $currentUser->company_id)->get();
            // Response
            $dataResponse['contacts'] = $contacts; 
            $dataResponse['companies'] = count($companies);
            $dataResponse['users'] = $usersResponse;
            $dataResponse['articles'] = $articlesResponse;
            $dataResponse['sampleExam'] = $sampleExamResponse;
            $dataResponse['examQuestions'] = count($examQuestions); 
        } else { 
            //Exam
            $exams = Exam::where('company_id', $currentUser->company_id)->get();
            $examsActive = Exam::where('company_id', $currentUser->company_id)->where('status', Exam::ONGOING)->get();
            $examsResponse = ['total' => count($exams), 'active' => count($examsActive), 'block' => count($exams) - count($examsActive)];
            //ExamQuestion
            $examQuestions = ExamQuestion::where('company_id', $currentUser->company_id)->get();
            // Response 
            $dataResponse['exams'] = $examsResponse;
            $dataResponse['examQuestions'] = count($examQuestions);
        }
        return $this->successResponse($dataResponse);
    }
}
