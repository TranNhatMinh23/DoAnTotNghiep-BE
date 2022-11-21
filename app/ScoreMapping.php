<?php

namespace App;

use App\ExamQuestionScore;
use Illuminate\Database\Eloquent\Model;

class ScoreMapping extends Model
{
    protected $table = 'score_mapping';

    protected $fillable = [
        'num_of_question',
        'listening_score',
        'reading_score',
        'exam_question_score_id',
    ];

    protected $casts = [
        'listening_score' => 'integer',
        'reading_score' => 'integer',
    ];

    public function exam_question_score() {
        return $this->belongsTo(ExamQuestionScore::class);
    }

}
