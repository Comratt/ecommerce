<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';

    protected $fillable = ['status_id', 'first_name', 'last_name', 'phone', 'email', 'shipping_city', 'shipping_area', 'shipping_city_ref', 'shipping_address_ref', 'novaposhta_ttn_ref', 'shipping_address', 'promocode_id', 'promocode_discount', 'comment', 'manager_id'];
}
