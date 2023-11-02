<?php namespace App\Jobs;

use App\Http\Controllers\OrderController;
use App\Models\CustomLogs;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

class OrderDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;

        $custom=new CustomLogs();
        $custom->logs='2';
        $custom->save();
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $custom=new CustomLogs();
        $custom->logs=json_encode( $this->shopDomain);
        $custom->save();
        $shop = User::where('name', $this->shopDomain->toNative())->first();
        $order = json_decode(json_encode($this->data), false);
        $ordercontroller = new OrderController();
        $ordercontroller->DeleteOrder($order, $shop);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Convert domain

        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }
}
