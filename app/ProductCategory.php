<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $primaryKey = 'product_category_id';

    protected $fillable = ['product_id', 'category_id'];
}
