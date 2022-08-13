<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $primaryKey = 'order_history_id';
    protected $table = 'order_history';

    protected $fillable = ['order_id', 'notify_customer', 'history_comment', 'history_status', 'manager_id'];
}
