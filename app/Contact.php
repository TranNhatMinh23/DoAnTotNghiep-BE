<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{ 
    const PENDDING = 1;
    const PROCESSED = 0;

    protected $fillable = [
        'username',
        'email',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $table = 'contact'; 
}
