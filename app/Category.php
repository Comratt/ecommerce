<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'category_id';

    public function description()
    {
        return $this->hasOne('App\CategoryDescription', 'category_id', 'category_id');
    }
}
