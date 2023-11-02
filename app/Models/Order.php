<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function has_items(){
        return  $this->hasMany(Lineitem::class, 'shopify_order_id', 'shopify_order_id');
    }

    public function has_order_history(){
        return  $this->hasMany(OrderHistory::class, 'order_shopify_id', 'shopify_order_id');
    }
}
