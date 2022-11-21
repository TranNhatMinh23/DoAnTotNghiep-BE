<?php

namespace App;

use App\Question;
use App\ExamQuestion;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $table = 'part';

    protected $fillable = [
        'part_no',
        'amount',
        'directions',
        'ex_question_text',
        'ex_question_image',
        'ex_answera',
        'ex_answerb',
        'ex_answerc',
        'ex_answerd',
        'ex_answer_key',
        'exam_question_id'
    ];

    public function exam_question(){
        return $this->belongsTo(ExamQuestion::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }
}
