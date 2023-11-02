<?php

namespace App\Console\Commands;

use App\Http\Controllers\MessageController;
use Illuminate\Console\Command;

class CampaignCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:cron';

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
        $messagecontroller=new MessageController();
        $messagecontroller->CampaignScheduleMessage();
    }
}
