<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lineitem extends Model
{
    public function product_varient(){
        return $this->hasOne(ProductVariant::class, 'shopify_id', 'shopify_variant_id');
    }
}
