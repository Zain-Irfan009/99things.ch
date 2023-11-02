<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RandomOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $timeout = 3600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop = User::where('name','awakewater-earth.myshopify.com')->first();
//        $orders=Order::whereNotNull('customer_id')->get();
        $customers=Customer::orderBy('id', 'DESC')->take(200)->get();
        $variant_id=[
            '39745555955812',
            '39755920539748'
        ];
        foreach ($customers as $customer){
            $k = array_rand($variant_id);
            $v_id = $variant_id[$k];
            $variant=ProductVariant::where('shopify_id',$v_id)->first();
            $quantity = rand(1, 6);
            $line_items = [];
            array_push($line_items, [
                "variant_id" => $v_id,
                "quantity" => $quantity,
                "grams"=>$variant->grams
            ]);

            $total_weight=$variant->grams*$quantity;
            $get = $shop->api()->rest('post', '/admin/orders.json', [
                "order" => [
                    "line_items" => $line_items,
                    'total_weight'=>$total_weight,
                    "customer" => [
                        "id" => $customer->shopify_id,
                        "email" => $customer->email,
                    ],
                    'shipping_address' => [
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'address1' => $customer->address,
                        'city' => $customer->city,
                        'province' => $customer->province,
                        'country' => $customer->country,
                        'zip' => $customer->zip,
                        'phone' => $customer->phone,

                    ],
                ]
            ]);
        }
    }
}
