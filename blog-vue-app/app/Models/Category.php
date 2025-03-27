<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model class for post category.
 * It has a 1:M relationship with Post.
 */
class Category extends Model {

    // Declare factory.
    use HasFactory;

    // Declare fillable fields
    protected $fillable = [
        'name',
    ];

    // Declare 1:M relationship with Post.
    public function posts() {
        return $this->hasMany(Post::class);        
    }
}
