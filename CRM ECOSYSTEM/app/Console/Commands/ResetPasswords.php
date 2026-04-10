<?php

namespace App\Console\Commands;

use App\Enums\AgentType;
use App\Enums\UserRole;
use App\Models\Agent;
use Illuminate\Console\Command;

class ResetPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all agents passwords';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Agent::whereHas('user', function($query) {
            $query->where('role', UserRole::AGENT);
        })->update([
            'password_reset' => []
        ]);
        return Command::SUCCESS;
    }
}
