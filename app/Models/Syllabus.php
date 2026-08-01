<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Syllabus extends Model
{
    protected $table = 'subject_syllabus';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'topic_id',
        'session_id',
        'created_by',
        'created_for',
        'date',
        'time_from',
        'time_to',
        'presentation',
        'attachment',
        'lacture_youtube_url',
        'lacture_video',
        'sub_topic',
        'teaching_method',
        'general_objectives',
        'previous_knowledge',
        'comprehensive_questions',
        'status',
    ];

    public static function getMySubjects(int $classId, int $sectionId, int $sessionId): Collection
    {
        return DB::table('class_sections')
            ->join('subject_group_class_sections', 'subject_group_class_sections.class_section_id', '=', 'class_sections.id')
            ->join('subject_group_subjects', 'subject_group_subjects.subject_group_id', '=', 'subject_group_class_sections.subject_group_id')
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->where('subject_group_class_sections.session_id', $sessionId)
            ->where('class_sections.class_id', $classId)
            ->where('class_sections.section_id', $sectionId)
            ->select(
                'subject_group_subjects.id as subject_group_subjects_id',
                'subject_group_class_sections.id as subject_group_class_sections_id',
                'subjects.name',
                'subjects.code',
                'subjects.id as subject_id'
            )
            ->get();
    }

    public static function getSubjectStatus(int $subjectGroupSubjectId, int $subjectGroupClassSectionsId): ?object
    {
        return DB::table('lesson')
            ->join('topic', 'lesson.id', '=', 'topic.lesson_id')
            ->where('lesson.subject_group_class_sections_id', $subjectGroupClassSectionsId)
            ->where('lesson.subject_group_subject_id', $subjectGroupSubjectId)
            ->selectRaw('COUNT(CASE WHEN topic.status = 0 THEN 1 END) as incomplete, COUNT(CASE WHEN topic.status = 1 THEN 1 END) as complete, COUNT(*) as total')
            ->first();
    }

    public static function getSubjectSyllabusReport(int $subjectGroupSubjectId, int $subjectGroupClassSectionsId): Collection
    {
        return DB::table('lesson')
            ->where('subject_group_subject_id', $subjectGroupSubjectId)
            ->where('subject_group_class_sections_id', $subjectGroupClassSectionsId)
            ->select('id', 'name')
            ->get();
    }

    public static function getTopicsByLessonId(int $lessonId): Collection
    {
        return DB::table('topic')
            ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->where('topic.lesson_id', $lessonId)
            ->select('topic.id', 'topic.name', 'topic.status', 'topic.complete_date')
            ->get();
    }
}
