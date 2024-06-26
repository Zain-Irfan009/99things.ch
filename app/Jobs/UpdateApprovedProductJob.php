<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ProductController;
use App\Models\CustomLog;
use App\Models\Log;
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
                    "options"     =>  json_decode($product->options),
//                    "tags" => explode(",", $product->tags),

                )
            );


            $result = $shop->api()->rest('put', '/admin/products/' . $product->shopify_id . '.json', $products_array);
            $result = json_decode(json_encode($result));

            $product->shopify_updated_at=Carbon::now();
            $product->save();
            $log=new CustomLog();
            $log->logs=json_encode($result);
            $log->save();
        }else{

            if($partner && $partner->autopush_products==1){

                $log = new Log();
                $currentTime = now();
                $log->name = 'Auto-Push Product to Shopify ('.$partner->name. ')';
                $log->date = $currentTime->format('F j, Y');
                $log->start_time = $currentTime->toTimeString();
                $log->status = 'In-Progress';
                $log->save();
                $log_id=$log->id;

                    $product->shopify_status='In-Progress';
                    $product->save();

                    $variants=[];
                    $product_variants =ProductVariant::where('partner_shopify_product_id',$product->partner_shopify_id)->where('partner_id',$partner->id)->get();
                    $variant_image_ids_array = array();
                    foreach($product_variants as $index=> $product_variant)
                    {
                        array_push($variant_image_ids_array,$product_variant->image_id);
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
                            "title"        => $product->title,
                            "body_html"    => $product->description,
                            "vendor"       =>  $product->vendor,
                            "status"=>$product->status,
                            "product_type" => $product->type,
                            "published"    => true ,
//                            "tags"         => explode(",",$product->tags),
                            "variants"     =>$variants,
                            "options"     =>  json_decode($product->options),

                        )
                    );


                    $result = $shop->api()->rest('post', '/admin/products.json',$products_array);
                    $result = json_decode(json_encode($result));
                    if($result->errors==false) {

                        $shopify_product_id = $result->body->product->id;

                        Product::where('id', $product->id)->update(['shopify_id' => $shopify_product_id, 'app_status' => '1', 'approve_date' => Carbon::now(),'shopify_updated_at'=>Carbon::now()]);

                        foreach ($result->body->product->variants as $prd) {
                            ProductVariant::where('sku', $prd->sku)->update(['inventory_item_id' => $prd->inventory_item_id, 'shopify_id' => $prd->id, 'shopify_product_id' => $shopify_product_id]);
                        }

                        $product_images = ProductImage::where('product_id', $product->partner_shopify_id)->get();

                        foreach ($product_images as $index => $img_val) {

                            $product_variant = ProductVariant::where('image_id', $img_val->image_id)->first();

                            if ($product_variant) {

                                $data = array(
                                    'src' => $img_val->image,
                                    'alt' => $img_val->alt,
                                    'variant_ids' => [$product_variant->shopify_id]

                                );
                            } else {
                                $data = array(
                                    'src' => $img_val->image,
                                    'alt' => $img_val->alt,


                                );
                            }


                            $result = $shop->api()->rest('post', '/admin/products/' . $shopify_product_id . '/images.json', [
                                'image' => $data
                            ]);

                        }


                        $product->shopify_status = 'Complete';
                        $product->save();

                        $currentTime = now();
                        $log->date = $currentTime->format('F j, Y');
                        $log->end_time = $currentTime->toTimeString();
                        $log->status = 'Complete';
                        $log->save();
                    }else{
                        $product->shopify_status='Failed';
                        $product->save();
                        $currentTime = now();
                        $log->end_time = $currentTime->toTimeString();
                        $log->status = 'Failed';
                        $log->save();

                    }

            }
        }



    }
}
