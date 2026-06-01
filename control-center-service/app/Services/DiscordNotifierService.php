<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiscordNotifierService {
    public static function sendEmbed(array $payload): void {
        $webhook = config('services.discord.webhook_url');
        if (!$webhook) {
            Log::warning('Discord webhook URL not configured.');
            return;
        }

        // $isoTime = $payload['date_now']->toISOString();
        $level = strtolower($payload['level'] ?? 'error');
        $color = self::getColorByLevel($level);
        $contentTitle = self::configureGenerateContent($payload['status'], $payload['request_id'], $payload['log_id'], $payload['date_now']);

        $response = Http::post($webhook, [
            'content' => $contentTitle['content'],
            'embeds' => [[
                'color' => $color,
                'title' => $contentTitle['title'],
                'description' => "**File** " . PHP_EOL . " ```$payload[file]:$payload[line]``` " . PHP_EOL . " **Error** " . PHP_EOL . " ```$payload[code_name]```",
                'fields' => [
                    ['name' => 'Level', 'value' => ucwords($level), 'inline' => true],
                    ['name' => 'Status', 'value' => $payload['status'], 'inline' => true],
                    ['name' => 'Environment', 'value' => ucfirst(app()->environment())],
                    ['name' => 'Host', 'value' => request()->getSchemeAndHttpHost(), 'inline' => true],
                    ['name' => 'IP Address', 'value' => "{$payload['ip']}", 'inline' => true],
                    ['name' => 'Access', 'value' => "[{$payload['method']}] {$payload['url']}"],
                    ['name' => 'User', 'value' => $payload['user_id']],
                    ['name' => 'User Agent', 'value' => "{$payload['user_agent']}"],
                    ['name' => 'Service', 'value' => Str::studly(config('app.name')), 'inline' => true],
                    ['name' => 'Request Source', 'value' => request()->header('X-Request-Source', 'N/A'), 'inline' => true],
                ],
                'footer' => [
                    'text' => config('app.name')
                ],
                'timestamp' => $payload['date_now']->toISOString()
            ]]
        ]);

        //* Failed Send the webhook
        if ($response->status() >= 400)
            Log::warning("Failed to send Discord message. Error log reference: <{$payload['request_id']}>");
    }

    /*
    public function getSeverity(): string {
        return match ($this) {
            self::InvalidToken => 'warning',
            self::UserNotFoundFromToken => 'info',
            self::BannedToken => 'error',
        };
    }
    */

    protected static function getColorByLevel(string $level): int {
        return match ($level) {
            'success' => 3066993, // ✅ Hijau (success)
            'warning' => 15105570, // ⚠️ Kuning (warning)
            'error' => 16711680, // ❌ Merah (error)
            default => 3447003 // ℹ️ Biru (info)
        };
    }

    protected static function configureGenerateContent(?int $exceptionCode, ?string $exceptionId = null, ?string $logId = null, ?Carbon $timestamp = null): array {
        $isoTime = $timestamp?->toISOString() ?? now()->toISOString();

        return match (true) {
            $exceptionCode >= 200 && $exceptionCode < 300 => [
                'content' => 'Your request was successfully!',
                'title' => "✅ **[SUCCESS] [$exceptionCode] [$isoTime]**"
            ],
            $exceptionCode >= 400 && $exceptionCode < 500 => [
                'content' => "Oops...invalid request, the request couldn't be processed! Error log reference:\n```$exceptionId```\n```$logId```",
                'title' => "⚠️ **[WARNING] [$exceptionCode] [$isoTime]**"
            ],
            $exceptionCode >= 500 => [
                'content' => "Oops...an error occurred, the request couldn't be processed! Error log reference:\n```$exceptionId```\n```$logId```",
                'title' => "🔴 **[ERROR] [$exceptionCode] [$isoTime]**"
            ],
            default => [
                'content' => "Heads up...this is just for your information. No action is required. Reference ID:\n```$exceptionId```\n```$logId```",
                'title' => "ℹ️ **[INFO] [$exceptionCode] [$isoTime]**"
            ],
        };
    }
}
