<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model class for post.
 * It has a foreign key to Category.
 */
class Post extends Model {

    // Declare factory.
    use HasFactory;

    // Suppress default timestamp fields, external posts have date
    public $timestamps = false;

    // Declare fillable fields. Records will keep external Id for PK
    protected $fillable = [
        'id',
        'title',
        'content',
        'author',
        'date',
        'category_id',
    ];

    // Declare relationship with Category.
    public function category() {
        return $this->belongsTo(Category::class);
    } 
}
