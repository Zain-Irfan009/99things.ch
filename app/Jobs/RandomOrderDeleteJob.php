<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RandomOrderDeleteJob implements ShouldQueue
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
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop = User::where('name','awakewater-earth.myshopify.com')->first();
        $orders=Order::orderBy('id', 'DESC')->take(200)->get();
        foreach ($orders as $order){
            $cancel = $shop->api()->rest('post', '/admin/orders/'.$order->shopify_order_id.'/cancel.json',[
                'order'=>[
                ]
            ]);
            $delete = $shop->api()->rest('delete', '/admin/orders/'.$order->shopify_order_id.'.json');
        }
    }
}
