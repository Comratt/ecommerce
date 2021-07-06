<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $primaryKey = 'product_option_id';

    protected $fillable = ['product_id', 'option_id', 'option_value_id', 'quantity'];
}
