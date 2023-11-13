<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ProductController;
use App\Models\CustomLog;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class DenyProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600;
    public $product_ids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_ids)
    {
        $this->product_ids=$product_ids;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop=User::where('name',env('SHOP_NAME'))->first();
        $setting=Setting::where('shop_id',$shop->id)->first();
       $product_ids=explode(',',$this->product_ids);

       foreach ($product_ids as $product_id){

               $product=Product::find($product_id);

               if($product->shopify_id)
               {
                   $result = $shop->api()->rest('delete', '/admin/products/'.$product->shopify_id.'.json');
                   if($result['errors']==false) {
                       Product::where('id', $product_id)->update(['app_status' => '3', 'shopify_status' => 'Pending', 'shopify_id' => null, 'approve_date' => Carbon::now()]);

                   }
               }

       }
    }
}
