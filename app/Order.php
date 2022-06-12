<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';

    protected $fillable = ['status_id', 'first_name', 'last_name', 'phone', 'email', 'shipping_city', 'shipping_address', 'comment'];
}
