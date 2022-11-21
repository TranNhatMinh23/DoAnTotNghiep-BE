<?php

namespace App;

use App\ExamQuestion;
use App\ScoreMapping;
use App\Company;
use Illuminate\Database\Eloquent\Model;

class ExamQuestionScore extends Model
{
    protected $table = 'exam_question_score';

    protected $fillable = [
        'name',
        'description',
        'company_id'
    ];

    public function exam_questions(){
        return $this->hasMany(ExamQuestion::class);
    }

    public function score_mappings(){
        return $this->hasMany(ScoreMapping::class);
    }

    public function company(){
        return $this->belongsTo(Company::class, 'id', 'company_id');
    }
}
