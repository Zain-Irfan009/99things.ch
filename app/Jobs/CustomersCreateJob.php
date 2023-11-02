<?php namespace App\Jobs;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\mainController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

class CustomersCreateJob implements ShouldQueue
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

        try {
            $shop= User::where('name',$this->shopDomain)->first();
            if($shop != null){
                $createcustomers=$this->data;
                $customers=new CustomerController();
                $customers->CreateUpdateCustomer($createcustomers,$shop);
            }else{
                $msg = new ErrorMessage();
                $msg->message = 'no shop found';
                $msg->save();
            }

        }catch (\Exception $exception)
        {
            $msg = new ErrorMessage();
            $msg->message = json_encode($exception->getMessage());
            $msg->save();
        }

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Convert domain
//        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);



        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }
}
