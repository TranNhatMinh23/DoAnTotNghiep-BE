<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participant';

    const IS_REGREX = 1;
    const NOT_REGREX = 0;

    protected $fillable = [
        'exam_id',
        'email',
        'regrex' 
    ];

    protected $casts = [
        'regrex' => 'boolean',
        'status' => 'boolean'
    ];  

    protected $hidden = [  
        // 'exam_id', 
    ];

    public function exam() {
        return $this->belongsTo(Exam::class, 'exam_id','id');
    }

    public function ongoingExam() {
        return $this->belongsTo(Exam::class, 'exam_id', 'id')->where('status', Exam::ONGOING); 
    }

    public function company() {
        return $this->belongsTo(Company::class, 'company_id','id');
    }
}
