<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'title',
        'content',
        'date',
        'category_id',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    } 
}
