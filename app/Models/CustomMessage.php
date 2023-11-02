<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomMessage extends Model
{
    public function has_product(){
        return  $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function has_products(){
        return  $this->hasMany('App\Models\CustomProducts', 'custom_message_id', 'id');
    }
}
