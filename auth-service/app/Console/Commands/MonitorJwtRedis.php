<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\JwtRedisService;

class MonitorJwtRedis extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor JWT Redis statistics';

    /**
     * Execute the console command.
     */
    public function handle(JwtRedisService $jwtRedis) {
        $this->info('JWT Redis Statistics');
        $this->line('------------------------');

        $bannedTokens = $jwtRedis->getAllBannedTokens();
        $this->info('Banned Tokens: ' . count($bannedTokens));

        $this->table(
            ['Token ID', 'Expires In (seconds)'],
            collect($bannedTokens)->map(fn($t) => [$t['token_id'], $t['expires_in']])
        );
    }
}
