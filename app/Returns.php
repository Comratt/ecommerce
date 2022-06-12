<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    protected $primaryKey = 'return_id';

    protected $fillable = ['order_id', 'return_price', 'return_comment'];
}
