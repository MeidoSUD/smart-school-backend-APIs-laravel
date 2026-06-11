<?php

namespace Modules\Core\Services;

use Carbon\Carbon;
use Modules\Core\Entities\Setting;

class SchoolYearHelper
{
    public static function sessionDateRange(?Setting $setting = null): array
    {
        $setting = $setting ?? Setting::first();
        $startMonth = $setting ? (int) ($setting->start_month ?? 4) : 4;

        $currentYear = (int) date('Y');
        $start = Carbon::createFromDate($currentYear, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($currentYear, $startMonth, 1)->addYear()->subDay();

        if ((int) date('n') < $startMonth) {
            $start = $start->subYear();
            $end = $end->subYear();
        }

        return [
            'start' => $start->toDateString(),
            'end' => min($end->toDateString(), date('Y-m-d')),
        ];
    }
}
