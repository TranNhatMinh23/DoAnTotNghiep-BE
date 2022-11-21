<?php

namespace App;

use App\Category;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    const ACTIVE = 1;
    const DISABLE = 0;

    protected $table = 'article';

    protected $fillable = [
        'title',
        'image_url',
        'description',
        'category_id',
        'content',
        'status',
    ];

    protected $casts = [ 
        'status' => 'boolean' 
    ];


    public function category(){
        return $this->belongsTo(Category::class);
    }
}
