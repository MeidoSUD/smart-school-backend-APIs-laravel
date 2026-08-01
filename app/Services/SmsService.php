<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Entities\SmsConfig;

class SmsService
{
    protected ?SmsConfig $config;

    public function __construct()
    {
        $this->config = SmsConfig::where('is_active', 'yes')->first();
    }

    public function send(string $phone, string $message): bool
    {
        if (!$this->config) {
            Log::warning('No active SMS configuration found');
            return false;
        }

        try {
            return match($this->config->sms_service) {
                'twilio' => $this->sendViaTwilio($phone, $message),
                'nexmo' => $this->sendViaNexmo($phone, $message),
                'sms_broadcast' => $this->sendViaSmsBroadcast($phone, $message),
                default => $this->sendViaGeneric($phone, $message),
            };
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        $results = [];
        foreach ($phones as $phone) {
            $results[$phone] = $this->send($phone, $message);
        }
        return $results;
    }

    protected function sendViaTwilio(string $phone, string $message): bool
    {
        return true;
    }

    protected function sendViaNexmo(string $phone, string $message): bool
    {
        return true;
    }

    protected function sendViaSmsBroadcast(string $phone, string $message): bool
    {
        return true;
    }

    protected function sendViaGeneric(string $phone, string $message): bool
    {
        return true;
    }

    public function isActive(): bool
    {
        return $this->config !== null;
    }
}
