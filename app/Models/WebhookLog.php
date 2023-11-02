<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    public function has_name(){
        return  $this->belongsTo('App\Models\Customer', 'from', 'phone');
    }


}
