<?php

namespace Modules\Core\Services;

use Modules\Core\Entities\Setting;
use Illuminate\Support\Facades\Cache;

class SchoolSettingsService
{
    public function getSettings(): Setting
    {
        $cacheKey = 'school_settings';

        return Cache::remember($cacheKey, 86400, function () {
            return Setting::first();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('school_settings');
    }

    public function lowAttendanceLimit(): int
    {
        $setting = $this->getSettings();
        return (int) ($setting->low_attendance_limit ?? 75);
    }

    public function sessionDates(): array
    {
        $setting = $this->getSettings();
        $startMonth = $setting ? ($setting->start_month ?? 4) : 4;
        $currentYear = date('Y');
        $start = \Carbon\Carbon::createFromDate($currentYear, $startMonth, 1)->startOfMonth();
        $end = \Carbon\Carbon::createFromDate($currentYear, $startMonth, 1)->addYear()->endOfMonth();

        if (date('n') < $startMonth) {
            $start = $start->subYear();
            $end = $end->subYear();
        }

        return [
            'start' => $start->toDateString(),
            'end' => min($end->toDateString(), date('Y-m-d')),
        ];
    }
}
