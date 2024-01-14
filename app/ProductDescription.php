<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    protected $primaryKey = 'product_description_id';

    protected $fillable = ['product_id', 'description', 'care', 'tag', 'meta_title', 'meta_description', 'meta_keyword'];
}
