<?php

namespace Modules\Academic\Services;

use Modules\Academic\Entities\StudentAttendence;
use Modules\Core\Entities\Setting;
use Modules\Core\Services\SchoolYearHelper;

class AttendanceCalculator
{
    private const ABSENT_TYPE_ID = 4;

    public function percentageForSession(int $studentSessionId, ?Setting $setting = null): float
    {
        $range = SchoolYearHelper::sessionDateRange($setting);

        return $this->percentageForRange($studentSessionId, $range['start'], $range['end']);
    }

    public function percentageForRange(int $studentSessionId, string $start, string $end): float
    {
        $records = StudentAttendence::where('student_session_id', $studentSessionId)
            ->whereBetween('date', [$start, $end])
            ->get();

        if ($records->isEmpty()) {
            return -1.0;
        }

        $absents = $records->where('attendence_type_id', self::ABSENT_TYPE_ID)->count();
        $presents = $records->count() - $absents;

        return round(($presents * 100) / $records->count(), 2);
    }
}
