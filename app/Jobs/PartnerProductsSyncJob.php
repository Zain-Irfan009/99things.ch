<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\PartnerController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PartnerProductsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600;
    public $partner;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($partner)
    {
        $this->partner=$partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $partnerController=new PartnerController();
        $partnerController->SyncPartnerProducts($this->partner);
    }
}
