<?php

namespace App;

use App\Article;
use Illuminate\Database\Eloquent\Model; 

class Category extends Model
{ 
    const TIPS_ID = 1;
    const NOTIFICATION_ID = 2;
    
    protected $table = 'category'; 

    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [ 
        'updated_at',
        'created_at', 
    ];
  
    public function articles(){
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}
