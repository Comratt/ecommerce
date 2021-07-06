<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryDescription extends Model
{
    protected $primaryKey = 'category_description_id';

    protected $fillable = [
        'category_id', 'description', 'tag', 'meta_title', 'meta_description', 'meta_keywords'
    ];
}
