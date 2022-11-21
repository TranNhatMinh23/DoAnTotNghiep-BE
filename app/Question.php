<?php

namespace App;

use App\Part;
use App\Answer;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';
    protected $fillable = [
    'question_no',	
    'question_text',
    'question_image',	
    'group_desc',	
    'paragraph_image1',	
    'paragraph_image2',	
    'paragraph_image3',	
    'paragraph_text1',
    'paragraph_text2',
    'paragraph_text3',
    'part_id',
    'audio'
    ];

    public function part() {
        return $this->belongsTo(Part::class);
    }

    public function answers(){
        return $this->hasMany(Answer::class, 'question_id', 'id');
    } 
}
