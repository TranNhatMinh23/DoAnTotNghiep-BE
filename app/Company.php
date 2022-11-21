<?php

namespace App;

use App\Exam;
use App\User;
use App\ExamQuestion;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //Cong ty
    protected $table = 'company'; 

    const SYSTEM_COMPANY = 1;

    protected $fillable = [
        'name',
        'address', 
        'phone' 
    ];

    public function exams(){
        return $this->hasMany(Exam::class);
    }

    public function exam_questions(){
        return $this->hasMany(ExamQuestion::class);
    }

    public function company_admins(){
        return $this->hasMany(User::class);
    }
}
