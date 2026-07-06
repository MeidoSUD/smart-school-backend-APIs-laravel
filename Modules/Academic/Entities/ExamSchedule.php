<?php

namespace Modules\Academic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamSchedule extends Model
{
    protected $table = 'exam_schedules';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'session_id', 'exam_id', 'class_id', 'teacher_subject_id', 'date_of_exam',
        'start_to', 'end_from', 'room_no', 'full_marks', 'passing_marks', 'note', 'is_active',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public static function getExamsByClassAndSection(int $classId, int $sectionId, int $sessionId): Collection
    {
        return DB::table('exam_schedules')
            ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
            ->join('class_sections', 'class_sections.id', '=', 'teacher_subjects.class_section_id')
            ->join('exams', 'exams.id', '=', 'exam_schedules.exam_id')
            ->where('class_sections.class_id', $classId)
            ->where('class_sections.section_id', $sectionId)
            ->where('teacher_subjects.session_id', $sessionId)
            ->where('exam_schedules.session_id', $sessionId)
            ->select('exams.*', 'exam_schedules.exam_id')
            ->distinct()
            ->get();
    }

    public static function getResultsByStudentAndExam(int $examId, int $studentId, int $sessionId): Collection
    {
        return DB::table('exam_schedules')
            ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
            ->join('exam_results', 'exam_results.exam_schedule_id', '=', 'exam_schedules.id')
            ->join('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('exam_schedules.exam_id', $examId)
            ->where('teacher_subjects.session_id', $sessionId)
            ->where('exam_results.student_id', $studentId)
            ->select(
                'exam_schedules.id as exam_schedule_id',
                'exam_schedules.full_marks',
                'exam_schedules.exam_id',
                'exam_schedules.passing_marks',
                'exam_results.attendence',
                'exam_results.get_marks',
                'exam_results.note',
                'subjects.name',
                'subjects.code',
                'subjects.type'
            )
            ->get();
    }
}
