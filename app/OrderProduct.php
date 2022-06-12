<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $primaryKey = 'order_product_id';

    protected $fillable = ['order_id', 'product_id', 'product_option_id', 'quantity', 'price', 'total', 'size', 'color'];
}
