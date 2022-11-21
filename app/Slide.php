<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slide';

    const ACTIVE = 1;
    const DISABLED = 0;

    protected $fillable = [
        'description',
        'url',
        'status'
    ]; 

    protected $casts = [ 
        'status' => 'boolean' 
    ];

}
