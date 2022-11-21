<?php

namespace App;

use App\Exam;
use App\Part;
use App\ExamQuestionScore;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $table = 'exam_question';

    const COMPLETED = 'COMPLETED';
    const UNCOMPLETED = 'UNCOMPLETED';

    const OLD_FORMAT = 1;
    const NEW_FORMAT = 2;

    protected $fillable = [
        'name',
        'listening_desc',
        'reading_desc',
        'audio',
        'status',
        'for_system',
        'company_id',
        'exam_question_score_id'
    ];

    protected $casts = [ 
        'for_system' => 'boolean'
    ];

    public function parts() {
        return $this->hasMany(Part::class);
    }

    public function exam_question_score() {
        return $this->belongsTo(ExamQuestionScore::class);
    }

    public function exams(){
        return $this->belongsToMany(Exam::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function isCompleted(){
        return $this->status == ExamQuestion::COMPLETED;
    }
}
