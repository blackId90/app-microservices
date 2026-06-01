<?php

namespace App\Console\Commands;

use App\Models\AuthUser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-old-auth-users-to-control-center')]
#[Description('Send old data auth users to Control Center via Redis')]
class SyncOldAuthUsersToControlCenter extends Command {

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('Starting the synchronization process...');

        //* Menggunakan chunk agar tidak memakan memori jika user ada jutaan
        AuthUser::chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->sync('upsert');
            }

            $this->info('Successfully sent data to queue...');
        });

        $this->info('All old data auth user has been queued in Redis.');
    }
}
