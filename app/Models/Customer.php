<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function latestMessage()
    {
        return $this->hasOne(Message::class,'customer_id')->latest();
    }


    public function scopeShopusers($query) {
        return $query->selectRaw(" customers.*, (SELECT MAX(created_at) from messages WHERE messages.customer_id=customers.id ) as latest_message_on")
            ->orderBy("latest_message_on", "DESC")->newQuery();
    }

    public function has_group(){
        return  $this->belongsTo('App\Models\CustomerGroup', 'customer_group_id', 'id');
    }
}
