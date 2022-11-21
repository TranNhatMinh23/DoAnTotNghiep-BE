<?php

namespace App;
use App\Question;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $table = 'answer'; 

    const CORRECT_ANSWER = 1;
    const INCORRECT_ANSWER = 0;

    protected $fillable = [
    'content',	
    'is_correct_flag',
    'question_id' 
    ];

    protected $casts = [
        'is_correct_flag' => 'boolean',
    ];

    protected $hidden = [ 
        'updated_at',
        'created_at', 
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'id', 'question_id');
    }
}
