<?php

namespace Modules\Academic\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyllabusService
{
    public function buildSubjectStatusData(Collection $subjects): array
    {
        if ($subjects->isEmpty()) {
            return [];
        }

        // Build batched WHERE conditions for (subject_group_subject_id, subject_group_class_sections_id) pairs
        $pairs = $subjects->map(fn ($s) => [
            $s->subject_group_subjects_id,
            $s->subject_group_class_sections_id,
        ]);

        // 1. Batch-load all lessons for all subjects — 1 query
        $allLessons = $this->getLessonsBatch($pairs);
        $lessonsBySubject = $allLessons->groupBy(
            fn ($l) => $l->subject_group_subject_id . '_' . $l->subject_group_class_sections_id
        );

        // 2. Batch-load all topics for all lessons — 1 query
        $allTopics = $this->getTopicsBatch($allLessons->pluck('id'));
        $topicsByLesson = $allTopics->groupBy('lesson_id');

        // 3. Batch-load status aggregates — 1 query
        $allStatuses = $this->getStatusesBatch($pairs);
        $statusByKey = $allStatuses->keyBy(
            fn ($s) => $s->subject_group_subject_id . '_' . $s->subject_group_class_sections_id
        );

        $subjectsData = [];

        foreach ($subjects as $subject) {
            $key = $subject->subject_group_subjects_id . '_' . $subject->subject_group_class_sections_id;

            $status = $statusByKey->get($key);
            $total = $status->total ?? 0;
            $completePercent = 0;
            $incompletePercent = 0;

            if ($total > 0) {
                $completePercent = round(($status->complete / $total) * 100);
                $incompletePercent = round(($status->incomplete / $total) * 100);
            }

            $label = $subject->code ? ' (' . $subject->code . ')' : '';

            $lessonSummary = [];
            $subjectLessons = $lessonsBySubject->get($key, collect());

            foreach ($subjectLessons as $lesson) {
                $lessonTopics = $topicsByLesson->get($lesson->id, collect());
                $topicComplete = $lessonTopics->where('status', 1)->count();
                $totalTopics = $lessonTopics->count();

                $lessonSummary[] = [
                    'name' => $lesson->name,
                    'topics' => $lessonTopics->map(fn ($topic) => [
                        'name' => $topic->name,
                        'status' => $topic->status,
                        'complete_date' => $topic->complete_date,
                    ])->values(),
                    'incomplete_percent' => $totalTopics > 0
                        ? round((($totalTopics - $topicComplete) / $totalTopics) * 100) : 0,
                    'complete_percent' => $totalTopics > 0
                        ? round(($topicComplete / $totalTopics) * 100) : 0,
                ];
            }

            $subjectsData[$subject->subject_group_subjects_id] = [
                'lebel' => $subject->name . $label,
                'complete' => $completePercent,
                'incomplete' => $incompletePercent,
                'id' => $subject->subject_group_subjects_id . '_' . $subject->code,
                'total' => $total,
                'name' => $subject->name,
                'graph_id' => $subject->subject_group_subjects_id . time(),
                'lesson_summary' => $lessonSummary,
            ];
        }

        return $subjectsData;
    }

    private function getLessonsBatch(Collection $pairs): Collection
    {
        $query = DB::table('lesson');
        $this->applyOrWherePairs($query, $pairs, 'subject_group_subject_id', 'subject_group_class_sections_id');

        return $query
            ->select('id', 'name', 'subject_group_subject_id', 'subject_group_class_sections_id')
            ->get();
    }

    private function getTopicsBatch(Collection $lessonIds): Collection
    {
        if ($lessonIds->isEmpty()) {
            return collect();
        }

        return DB::table('topic')
            ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->whereIn('topic.lesson_id', $lessonIds)
            ->select('topic.id', 'topic.name', 'topic.status', 'topic.complete_date', 'topic.lesson_id')
            ->get();
    }

    private function getStatusesBatch(Collection $pairs): Collection
    {
        $query = DB::table('topic as t')
            ->join('lesson as l', 'l.id', '=', 't.lesson_id');

        $this->applyOrWherePairs($query, $pairs, 'l.subject_group_subject_id', 'l.subject_group_class_sections_id');

        return $query
            ->selectRaw('
                l.subject_group_subject_id,
                l.subject_group_class_sections_id,
                COUNT(CASE WHEN t.status = 0 THEN 1 END) as incomplete,
                COUNT(CASE WHEN t.status = 1 THEN 1 END) as complete,
                COUNT(*) as total
            ')
            ->groupBy('l.subject_group_subject_id', 'l.subject_group_class_sections_id')
            ->get();
    }

    private function applyOrWherePairs($query, Collection $pairs, string $col1, string $col2): void
    {
        $query->where(function ($q) use ($pairs, $col1, $col2) {
            $first = true;
            foreach ($pairs as [$val1, $val2]) {
                $method = $first ? 'where' : 'orWhere';
                $q->$method(fn ($qq) => $qq->where($col1, $val1)->where($col2, $val2));
                $first = false;
            }
        });
    }
}
