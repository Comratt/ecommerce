<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    protected $primaryKey = 'promocodes_id';

    protected $fillable = ['promocode_name', 'promocode_price', 'promocode_prefix'];
}
