<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserAddon;

class CleanupOrphanedUserAddons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addons:cleanup-orphaned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned UserAddon records that don\'t have valid addon relationships';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for orphaned UserAddon records...');
        
        $orphanedCount = UserAddon::whereDoesntHave('addon')->count();
        
        if ($orphanedCount > 0) {
            $this->warn("Found {$orphanedCount} orphaned UserAddon records.");
            
            if ($this->confirm('Do you want to delete these orphaned records?')) {
                UserAddon::whereDoesntHave('addon')->delete();
                $this->info('Orphaned records have been cleaned up successfully.');
            } else {
                $this->info('Cleanup cancelled.');
            }
        } else {
            $this->info('No orphaned UserAddon records found.');
        }
        
        return 0;
    }
}
