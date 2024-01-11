<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ProductController;
use App\Models\CustomLog;
use App\Models\Lineitem;
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
use Osiset\BasicShopifyAPI\BasicShopifyAPI;
use Osiset\BasicShopifyAPI\Options;
use Osiset\BasicShopifyAPI\Session;

class  AutoPushOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600;
    public $order_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id=$order_id;

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
        $order=Order::find($this->order_id);
        $partner_ids = Lineitem::where('order_id', $order->id)
            ->where('is_pushed', 0)
            ->pluck('partner_id')
            ->unique()
            ->toArray();
        $options = new Options();
        $options->setVersion('2023-01');
        $api = new BasicShopifyAPI($options);

            foreach ($partner_ids as $partner_id){

                $partner=Partner::find($partner_id);
                if($partner && $partner->autopush_orders==1) {

                    $shop_name=$partner->shop_name;
                    $access_token=$partner->shopify_token;
                    $api->setSession(new Session($shop_name, $access_token));

                    $line_items = Lineitem::where('partner_id', $partner_id)->where('order_id', $order->id)->get();
                    $line_item_array = array();
                    $line_item_ids = array();
                    $total_weight = 0;
                    foreach ($line_items as $line_item) {
                        array_push($line_item_ids, $line_item->id);

                        $product_variant = ProductVariant::where('shopify_id', $line_item->shopify_variant_id)->first();
                        array_push($line_item_array, [
                            "variant_id" => $product_variant->partner_shopify_id,
                            'name' => $line_item->title,
                            'title' => $line_item->title,
                            'price' => $product_variant->price,
                            'product_id' => $product_variant->partner_shopify_product_id,
                            'quantity' => $line_item->quantity,
                            "grams" => $product_variant->grams

                        ]);
                        $total_weight += $product_variant->grams * $line_item->quantity;
                    }


                    $result = $api->rest('POST', '/admin/orders.json', [
                        "order" => [
                            "email" => $order->email,
                            "financial_status" => "pending",
//                        "tags" => (isset($order->tags) !="" ?$order->tags : null),
                            "line_items" => $line_item_array,
                            'total_weight' => $total_weight,
                            "note" => $order->note,
                            "shipping_address" => [
                                "first_name" => $order->first_name,
                                "last_name" => $order->last_name,
                                "address1" => $order->address,
                                "address2" => (isset($order->address2) ? $order->address2 : ""),
                                "phone" => $order->phone,
                                "city" => $order->city,
                                "province" => $order->state,
                                "country" => $order->country,
                                "zip" => $order->zip
                            ],
                        ]

                    ]);

                    $result = json_decode(json_encode($result));
                    if ($result->errors == false) {
                        Lineitem::whereIn('id', $line_item_ids)->update(['is_pushed' => 1]);

                    }
                }

            }


    }
}
