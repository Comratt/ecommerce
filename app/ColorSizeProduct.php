<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColorSizeProduct extends Model
{
    protected $table = 'color_size_product';
    protected $primaryKey = 'color_size_product_id';

    protected $fillable = ['product_id', 'color_id', 'size_id', 'quantity', 'created_at', 'updated_at'];
}
