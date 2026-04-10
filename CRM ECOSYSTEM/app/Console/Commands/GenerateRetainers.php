<?php

namespace App\Console\Commands;

use App\Http\Controllers\RetainerController;
use App\Models\Lead;
use Illuminate\Console\Command;

class GenerateRetainers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:retainers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $leads = Lead::whereDoesntHave('retainers', function($query) {
            $query->whereIn('visa_id', [10, 11, 12, 13]);
        })
        ->take(500)
        ->get();

        foreach($leads as $lead) {
            (new RetainerController)->updateVisa($lead, 11);
        }
    }
}
