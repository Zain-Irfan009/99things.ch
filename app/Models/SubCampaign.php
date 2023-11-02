<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCampaign extends Model
{
    public function has_webhook_logs(){
        return  $this->hasMany(WebhookLog::class, 'sub_campaign_id', 'id');
    }
    public function has_logs(){
        return  $this->hasMany(WebhookLog::class, 'sub_campaign_id', 'id')->count();
    }

    public function has_order_from_first_sms(){
        return  $this->hasMany(WebhookLog::class, 'sub_campaign_id', 'id')->where('status','Passed')->where('is_reminder',0)->count();
    }

    public function has_order_from_reminder_sms(){
        return  $this->hasMany(WebhookLog::class, 'sub_campaign_id', 'id')->where('status','Passed')->where('is_reminder',1)->count();
    }

}
