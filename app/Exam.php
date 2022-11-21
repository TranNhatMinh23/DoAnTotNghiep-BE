<?php

namespace App;

use App\ExamQuestion;
use App\Company;  
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    //Dot thi
    protected $table = 'exam';

    const ONGOING = 1;
    const STOP = 0;

    const SAMPLE = 1;
    const NOT_SAMPLE = 0;

    const SHUFFLE_ANSWER = 1;
    const NOT_SHUFFLE_ANSWER = 0;

    const ALLOW_VIEW_ANSWERS = 1;
    const DENY_VIEW_ANSWERS = 0;

    protected $fillable = [
        'name',
        'description', 
        'status', 
        'image_preview',
        'company_id',
        'from_date',
        'to_date',
        'exam_question_id',
        'is_shuffle_answer',
        'is_allow_view_answer'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_shuffle_answer' => 'boolean',
        'is_allow_view_answer' => 'boolean'
    ];

    public function exam_question(){
        return $this->hasOne(ExamQuestion::class, 'id', 'exam_question_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'id', 'company_id');
    } 

    public function isOnGoing(){
        return $this->status == Exam::ONGOING;
    } 

    public function getBasicExamInfo(){
        $data['id'] = $this->id;
        $data['name'] = $this->name;
        $data['description'] = $this->description;
        $data['from_date'] = $this->from_date;
        $data['to_date'] = $this->to_date;

        return $data;
    }
}
