<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $primaryKey = 'banner_id';

    protected $fillable = ['title', 'description', 'link', 'image', 'image_mobile', 'sort_order'];
}
