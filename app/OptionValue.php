<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    protected $primaryKey = 'option_value_id';

    protected $fillable = ['option_id', 'image', 'name_value', 'description'];
}
