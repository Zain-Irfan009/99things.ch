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

class  UpdateApprovedProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600;
    public $product_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_id)
    {
        $this->product_id=$product_id;

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
        $log=new CustomLog();
        $log->logs=$this->product_id;
        $log->save();
        $product=Product::find($this->product_id);
        $log=new CustomLog();
        $log->logs=json_encode($product);
        $log->save();

        $partner = Partner::find($product->partner_id);

        if($partner && $partner->price_multiplier){
            $price_multiplier=$partner->price_multiplier;

        }else{
            $price_multiplier=$setting->base_price_multiplier;

        }
        if($partner && $partner->compare_at_price_multiplier){

            $compare_at_price_multiplier=$partner->compare_at_price_multiplier;
        }else{
            $compare_at_price_multiplier=$setting->base_compare_at_price_multiplier;
        }


        if($product->shopify_id) {
            $product_variants =ProductVariant::where('partner_shopify_product_id',$product->partner_shopify_id)->where('partner_id',$partner->id)->get();

            foreach($product_variants as $index=> $product_variant) {
                if($product_variant->inventory_policy){
                    $inventory_policy=$product_variant->inventory_policy;
                }else{
                    $inventory_policy='continue';
                }
                $variants[]=array(
                    "title" => $product_variant->title,
                    "option1" => $product_variant->option1,
                    "option2" => $product_variant->option2,
                    "option3" => $product_variant->option3,
                    "sku"     => $product_variant->sku,
                    "price"   => $product_variant->price*$price_multiplier,
                    "compare_at_price" =>$product_variant->compare_at_price*$compare_at_price_multiplier,
                    "grams"   => $product_variant->grams,
                    "taxable" => $product_variant->taxable,
                    "inventory_management" => $product_variant->inventory_management,
                    "inventory_policy" => $inventory_policy,
                    "barcode" => $product_variant->barcode,
                    "inventory_quantity" => $product_variant->stock
                );
            }
            $products_array = array(
                "product" => array(
                    "title" => $product->title,
                    "body_html" => $product->description,
                    "variants"     =>$variants,
                    "product_type" => $product->type,
                    "tags" => explode(",", $product->tags),

                )
            );


            $result = $shop->api()->rest('put', '/admin/products/' . $product->shopify_id . '.json', $products_array);
            $result = json_decode(json_encode($result));

            $log=new CustomLog();
            $log->logs=json_encode($result);
            $log->save();
        }


    }
}
