<?php

namespace App\Jobs;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class AfterAuthenticateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Request $request)
    {
        if($request->input('shop')) {
            $shop = User::where('name', $request->shop)->first();
            $result =$shop->api()->rest('get', '/admin/shop.json');
            $result = json_decode(json_encode($result));
            if($result->errors==false) {
                $result = $result->body->shop;
                $shop->country_name=$result->country_name;
                $shop->country_code=$result->country_code;
                $shop->currency=$result->currency;
                $shop->weight_unit=$result->weight_unit;
                $shop->money_format=$result->money_format;
                $shop->money_with_currency_format=$result->money_with_currency_format;
                $shop->language_id=1;
                $shop->save();

                $setting=new Setting();
                $setting->base_price_multiplier=1;
                $setting->base_compare_at_price_multiplier=1;
                $setting->shop_id=$shop->id;
                $setting->save();
            }

        }

    }
}
