<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public function has_customer(){
        return  $this->belongsTo('App\Models\Customer', 'customer_id', 'id');
    }
}
