<?php

namespace App\Services;

use Modules\Core\Entities\Setting;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function getCurrencySymbol(): string
    {
        return Cache::remember('currency_symbol', 3600, function () {
            $settings = Setting::first();
            return $settings->currency_symbol ?? '$';
        });
    }

    public function getCurrencyCode(): string
    {
        return Cache::remember('currency_code', 3600, function () {
            $settings = Setting::first();
            return $settings->currency_code ?? 'USD';
        });
    }

    public function format(float $amount): string
    {
        $symbol = $this->getCurrencySymbol();
        $noOfDecimal = $this->getNoOfDecimal();
        return $symbol . number_format($amount, $noOfDecimal);
    }

    public function getNoOfDecimal(): int
    {
        return Cache::remember('currency_decimal', 3600, function () {
            $settings = Setting::first();
            return $settings->no_of_decimal ?? 2;
        });
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }
        return $amount;
    }
}
