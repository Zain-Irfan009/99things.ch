<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function has_partner(){
        return  $this->belongsTo('App\Models\Partner', 'partner_id', 'id');
    }
}
