<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public function has_custom_message(){
        return  $this->belongsTo('App\Models\CustomMessage', 'custom_message_id', 'id');
    }

    public function has_reminder_message(){
        return  $this->belongsTo('App\Models\CustomMessage', 'reminder_message_id', 'id');
    }


}
