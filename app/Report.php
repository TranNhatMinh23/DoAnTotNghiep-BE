<?php

namespace App;

use App\User;
use App\Exam;
use App\Result;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'report';

    protected $fillable = [
        'exam_id',
        'user_id',
        'listening_score',
        'reading_score',
        'num_listening',
        'num_reading'
    ];

    protected $casts = [
        'listening_score' => 'integer',
        'reading_score' => 'integer',
        'num_listening' => 'integer',
        'num_reading' => 'integer'
    ];

    protected $hidden = [
        'exam_id', 
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam(){
        return $this->belongsTo(Exam::class, 'exam_id', 'id');
    } 
 

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
