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

class OrdersUpdateJob implements ShouldQueue
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

        $custom_log=new CustomLogs();
        $custom_log->logs=$shopDomain;
        $custom_log->id_check='order update';
        $custom_log->save();


        $this->shopDomain = $shopDomain;
        $this->data = $data;

        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $shop = User::where('name', $this->shopDomain->toNative())->first();
        $order = json_decode(json_encode($this->data), false);
        $orderController = new OrderController();
        $orderController->singleOrder($order, $shop);
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