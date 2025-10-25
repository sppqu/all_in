<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserAddon;
use App\Models\Addon;

class CheckAddonStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addon:check-status {user_id?} {addon_slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix addon status for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $addonSlug = $this->argument('addon_slug');

        if ($userId && $addonSlug) {
            $this->checkSpecificAddon($userId, $addonSlug);
        } else {
            $this->checkAllAddons();
        }
    }

    private function checkSpecificAddon($userId, $addonSlug)
    {
        $addon = Addon::where('slug', $addonSlug)->first();
        
        if (!$addon) {
            $this->error("Addon with slug '{$addonSlug}' not found!");
            return;
        }

        $userAddon = UserAddon::where('user_id', $userId)
            ->where('addon_id', $addon->id)
            ->first();

        if (!$userAddon) {
            $this->warn("User {$userId} doesn't have addon '{$addonSlug}'");
            return;
        }

        $this->info("User {$userId} - Addon: {$addon->name}");
        $this->info("Current status: {$userAddon->status}");
        $this->info("Transaction ID: {$userAddon->transaction_id}");
        $this->info("Purchased at: {$userAddon->purchased_at}");
        
        if ($this->confirm('Do you want to activate this addon?')) {
            $userAddon->update(['status' => 'active']);
            $this->info("Addon activated successfully!");
        }
    }

    private function checkAllAddons()
    {
        $userAddons = UserAddon::with(['user', 'addon'])->get();
        
        $this->info("Found " . $userAddons->count() . " user addons:");
        
        foreach ($userAddons as $userAddon) {
            $status = $userAddon->status === 'active' ? '✅' : '❌';
            $this->line("{$status} User: {$userAddon->user->name} - Addon: {$userAddon->addon->name} - Status: {$userAddon->status}");
        }

        $pendingAddons = $userAddons->where('status', 'pending');
        if ($pendingAddons->count() > 0) {
            $this->warn("\nFound {$pendingAddons->count()} pending addons:");
            foreach ($pendingAddons as $userAddon) {
                $this->line("- User: {$userAddon->user->name} - Addon: {$userAddon->addon->name}");
            }
            
            if ($this->confirm('Do you want to activate all pending addons?')) {
                foreach ($pendingAddons as $userAddon) {
                    $userAddon->update(['status' => 'active']);
                }
                $this->info("All pending addons activated!");
            }
        }
    }
}
