<?php namespace App\Jobs;

use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

class ProductsDeleteJob implements ShouldQueue
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
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $shop = User::where('name', $this->shopDomain->toNative())->first();
        $product = json_decode(json_encode($this->data), false);
        $productcontroller = new ProductController();
        $productcontroller->DeleteProduct($product, $shop);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

    }
}
