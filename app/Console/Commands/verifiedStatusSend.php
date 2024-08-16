<?php

namespace App\Console\Commands;

use App\Jobs\verificarStatusEnvio;
use Illuminate\Console\Command;

class verifiedStatusSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verified-status-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        verificarStatusEnvio::dispatch();
    }
}
