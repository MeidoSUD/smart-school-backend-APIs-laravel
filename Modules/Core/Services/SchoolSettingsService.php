<?php

namespace Modules\Core\Services;

use Modules\Core\Entities\Setting;
use Modules\Core\Entities\Session;
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
        $startMonth = $setting ? ((int) ($setting->start_month ?? 4) ?: 4) : 4;

        // CI parity: custom_helper::sessionYearDetails($session, $start_month)
        // where $session is sessions.session e.g. "2024-25" joined via
        // sch_settings.session_id, NOT the current calendar year.
        $sessionStr = null;
        if ($setting && !empty($setting->session_id)) {
            try {
                $sessionRow = Session::find($setting->session_id);
                $sessionStr = $sessionRow?->session;
            } catch (\Throwable $e) {
                $sessionStr = null;
            }
        }

        if (is_string($sessionStr) && str_contains($sessionStr, '-')) {
            $parts = explode('-', $sessionStr);
            $currentYear = trim($parts[0]);
            $b = trim($parts[1] ?? '');
            $nextYear = strlen($b) == 2 ? substr($currentYear, 0, 2) . $b : $b;
            if (is_numeric($currentYear) && is_numeric($nextYear)) {
                $endMonth = $startMonth == 1 ? 12 : $startMonth - 1;
                $start = sprintf('%04d-%02d-01', (int) $currentYear, $startMonth);
                $endBase = sprintf('%04d-%02d-01', (int) $nextYear, $endMonth);
                try {
                    $end = \Carbon\Carbon::parse($endBase)->endOfMonth()->toDateString();
                } catch (\Throwable $e) {
                    $end = date('Y-m-t', strtotime($endBase));
                }
                return ['start' => $start, 'end' => $end];
            }
        }

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
