<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminController;
use Illuminate\Console\Command;

class SyncStripeDetailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-stripe-detail:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $adminController=new AdminController();
        $adminController->SyncStripeDetail();
    }
}
