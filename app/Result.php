<?php

namespace App;

use App\Report;
use App\Question;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'result';
    protected $fillable = [
        'your_answer',
        'report_id',
        'question_id',
        'your_answer_code',
        'position_1',
        'position_2',
        'position_3',
        'position_4'
    ];
    
    public function report() {
        return $this->belongsTo(Report::class);
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }
}
