<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', 'JWTAuthController@login');
Route::post('signup/personal', 'JWTAuthController@signupPersonal');
Route::post('signup/company', 'JWTAuthController@signupCompany');
Route::get('verify/{verification_code}', 'JWTAuthController@verifyUser');
Route::group(['middleware' => 'jwt.auth'], function () {
  Route::get('me', 'JWTAuthController@me');
  Route::get('refresh', 'JWTAuthController@refresh');
  Route::post('logout', 'JWTAuthController@logout');
});
/**
 * Users
 */
Route::resource('users', 'User\UserController', ['except'=>['create', 'edit']]);
Route::post('users/{id}/avatar', 'User\UserController@updateAvatar');
Route::put('users/{id}/status', 'User\UserController@updateStatus');

/**
 * ExamQuestion 
 */
Route::resource('exam_questions', 'ExamQuestion\ExamQuestionController', ['except'=>['create', 'edit']]);
Route::post('exam_questions/{id}/audio', 'ExamQuestion\ExamQuestionController@uploadAudio');
Route::get('exam_question/for_company', 'ExamQuestion\ExamQuestionController@getExamQuestionForCompany');
/**
 * Part
 */
Route::resource('parts', 'Part\PartController', ['only'=>['show', 'update']]);
Route::post('parts/{id}/upload', 'Part\PartController@uploadExamImage');
Route::delete('parts/image/{id}', 'Part\PartController@deleteExamImage');
/**
 * Question
 */
Route::resource('questions', 'Question\QuestionController', ['only'=>['show', 'update']]);
Route::post('questions/{id}/image', 'Question\QuestionController@uploadQuestionImage');
Route::delete('questions/{id}/image', 'Question\QuestionController@deleteQuestionImage');
Route::post('questions/{id}/paragraph-image/{paragraphNo}', 'Question\QuestionController@uploadParagraphImage');
Route::delete('questions/{id}/paragraph-image/{paragraphNo}', 'Question\QuestionController@deleteParagraphImage');

/**
 * ExamQuestion SCORE
 */
Route::resource('exam_question_scores', 'ExamQuestionScore\ExamQuestionScoreController', ['except'=>['create', 'edit']]);
Route::put('exam_question_scores/{id}/score_mapping', 'ExamQuestionScore\ExamQuestionScoreController@updateDetailScoreMapping');
Route::get('exam_question_scores/export/{id}', 'ExamQuestionScore\ExamQuestionScoreController@exportDetailScoreMapping');
Route::post('exam_question_scores/import/{id}', 'ExamQuestionScore\ExamQuestionScoreController@importDetailScoreMapping');

/**
 * Exam Schedule
 */
Route::resource('exam_schedules', 'Exam\ExamController', ['except'=>['create', 'edit']]); 
Route::put('exam_schedules/{id}/status', 'Exam\ExamController@updateStatus');
Route::put('exam_schedules/{id}/is_view_answers', 'Exam\ExamController@updateAllowViewAnswer');
Route::delete('exam_schedules/email_invitation/{id}', 'Exam\ExamController@deleteEmailInvitation');
Route::post('exam_schedules/import_email_invitation/{id}', 'Exam\ExamController@importEmailInvitationFile');

/**
 * Sample Exam
 */
Route::resource('sample_exams', 'SampleExam\SampleExamController', ['except'=>['create', 'edit']]); 
Route::put('sample_exams/{id}/status', 'SampleExam\SampleExamController@updateStatus');
Route::put('sample_exams/{id}/is_view_answers', 'SampleExam\SampleExamController@updateAllowViewAnswer');

/**
 * Exam Client (get exam and take a test)
 */
Route::resource('exams', 'Exam\ExamClientController', ['only'=>['index', 'show']]);
Route::get('exams/{id}/before_test', 'Exam\ExamClientController@getExamBeforeTaken');

Route::post('submit_answers/{examId}', 'Exam\SubmitExamController@store');
Route::get('submit_answers/{reportId}', 'Exam\SubmitExamController@show');

/**
 * Samples Exam Client (get sample exam and take a test)
 */
Route::resource('client/samples', 'SampleExam\SampleExamClientController', ['only'=>['index', 'show']]); 
Route::get('client/samples/{id}/before_test', 'SampleExam\SampleExamClientController@getSampleExamBeforeTaken');

/**
 * Reports
 */
Route::resource('reports', 'Report\ReportController', ['only'=>['index', 'show']]); 
Route::get('reports/export/{examId}', 'Report\ReportController@exportDetailResult');

/**
 * Reports Client
 */
Route::resource('my_reports', 'Report\ClientReportController', ['only'=>['index']]); 

/**
 * Statistical
 */
Route::resource('statisticals', 'Report\StastisticalController', ['only'=>['index', 'show']]); 

/**
 * Results
 */
Route::resource('results', 'Result\ResultController', ['only'=>['show']]); 

/**
 * Contacts
 */
Route::resource('contacts', 'Contact\ContactController', ['only'=>['index', 'destroy']]); 
Route::put('contacts/{contactId}/status', 'Contact\ContactController@updateStatus');
Route::resource('contacts/sent', 'Contact\ContactClientController', ['only'=>['store']]);  

/**
 * Slides
 */
Route::resource('slides', 'Slide\SlideController', ['except'=>['create', 'edit']]); 
Route::put('slides/{slideId}/status', 'Slide\SlideController@updateStatus');

/**
 * Categories
 */
Route::resource('categories', 'Category\CategoryController', ['except'=>['create', 'edit', 'show']]); 

/**
 * Articles
 */
Route::resource('articles', 'Article\ArticleController', ['except'=>['create', 'edit']]); 
Route::put('articles/{articleId}/status', 'Article\ArticleController@updateStatus');

/**
 * TIPS
 */
Route::resource('tips', 'Article\TipsController', ['only'=>['index', 'show']]); 

/**
 * HOME
 */
Route::resource('homes', 'Home\HomeController', ['only'=>['index']]); 

/**
 * COMPANY
 */
Route::resource('companies', 'Company\CompanyController', ['only'=>['index', 'update']]); 

/**
 * HOME CLIENT
 */
Route::resource('client/homes', 'Home\ClientHomeController', ['only'=>['index']]); 
Route::get('client/articles', 'Home\ClientHomeController@getAllArticles'); 
Route::get('client/articles/{id}', 'Home\ClientHomeController@getDetailArticles');

/**
 * Services Automation
 */
Route::resource('services', 'AutoServices\ServicesToAutoUpdateQuestion', ['only'=>['show', 'update']]); 