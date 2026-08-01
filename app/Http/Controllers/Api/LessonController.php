<?php

namespace App\Http\Controllers\Api;

use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class LessonController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('LessonController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = DB::table('lesson')
            ->join('class_sections', 'class_sections.id', '=', 'lesson.class_section_id')
            ->join('subjects', 'subjects.id', '=', 'lesson.subject_id')
            ->select('lesson.*', 'subjects.name as subject_name');

        if ($request->filled('session_id')) {
            $query->where('lesson.session_id', $request->session_id);
        }

        if ($request->filled('subject_group_subject_id')) {
            $query->where('lesson.subject_id', $request->subject_group_subject_id);
        }

        if ($request->filled('subject_group_class_section_id')) {
            $query->where('lesson.class_section_id', $request->subject_group_class_section_id);
        }

        $lessons = $query->get();

        return $this->successResponse(['lessons' => $lessons]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'                    => 'required',
            'subject_group_subject_id'      => 'required|exists:subjects,id',
            'subject_group_class_section_id' => 'required|exists:class_sections,id',
            'name'                          => 'required|string|max:255',
            'date'                          => 'nullable|date',
            'time_from'                     => 'nullable|string',
            'time_to'                       => 'nullable|string',
            'topic'                         => 'nullable|string',
            'sub_topic'                     => 'nullable|string',
            'teaching_method'               => 'nullable|string',
            'note'                          => 'nullable|string',
        ]);

        $id = DB::table('lesson')->insertGetId([
            'session_id'       => $validated['session_id'],
            'subject_id'       => $validated['subject_group_subject_id'],
            'class_section_id' => $validated['subject_group_class_section_id'],
            'title'            => $validated['name'],
            'date'             => $validated['date'] ?? null,
            'time_from'        => $validated['time_from'] ?? null,
            'time_to'          => $validated['time_to'] ?? null,
            'topic'            => $validated['topic'] ?? null,
            'sub_topic'        => $validated['sub_topic'] ?? null,
            'teaching_method'  => $validated['teaching_method'] ?? null,
            'note'             => $validated['note'] ?? null,
            'created_by'       => $request->user()->id ?? null,
        ]);

        $lesson = DB::table('lesson')
            ->join('subjects', 'subjects.id', '=', 'lesson.subject_id')
            ->join('class_sections', 'class_sections.id', '=', 'lesson.class_section_id')
            ->where('lesson.id', $id)
            ->select('lesson.*', 'subjects.name as subject_name')
            ->first();

        return $this->successResponse(['lesson' => $lesson], 'Lesson created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $lesson = DB::table('lesson')
            ->join('subjects', 'subjects.id', '=', 'lesson.subject_id')
            ->join('class_sections', 'class_sections.id', '=', 'lesson.class_section_id')
            ->where('lesson.id', $id)
            ->select('lesson.*', 'subjects.name as subject_name')
            ->first();

        if (!$lesson) {
            return $this->errorResponse('Lesson not found', null, 404);
        }

        return $this->successResponse(['lesson' => $lesson]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $existing = DB::table('lesson')->where('id', $id)->first();

        if (!$existing) {
            return $this->errorResponse('Lesson not found', null, 404);
        }

        $validated = $request->validate([
            'session_id'                    => 'required',
            'subject_group_subject_id'      => 'required|exists:subjects,id',
            'subject_group_class_section_id' => 'required|exists:class_sections,id',
            'name'                          => 'required|string|max:255',
            'date'                          => 'nullable|date',
            'time_from'                     => 'nullable|string',
            'time_to'                       => 'nullable|string',
            'topic'                         => 'nullable|string',
            'sub_topic'                     => 'nullable|string',
            'teaching_method'               => 'nullable|string',
            'note'                          => 'nullable|string',
        ]);

        DB::table('lesson')->where('id', $id)->update([
            'session_id'       => $validated['session_id'],
            'subject_id'       => $validated['subject_group_subject_id'],
            'class_section_id' => $validated['subject_group_class_section_id'],
            'title'            => $validated['name'],
            'date'             => $validated['date'] ?? null,
            'time_from'        => $validated['time_from'] ?? null,
            'time_to'          => $validated['time_to'] ?? null,
            'topic'            => $validated['topic'] ?? null,
            'sub_topic'        => $validated['sub_topic'] ?? null,
            'teaching_method'  => $validated['teaching_method'] ?? null,
            'note'             => $validated['note'] ?? null,
        ]);

        $lesson = DB::table('lesson')
            ->join('subjects', 'subjects.id', '=', 'lesson.subject_id')
            ->join('class_sections', 'class_sections.id', '=', 'lesson.class_section_id')
            ->where('lesson.id', $id)
            ->select('lesson.*', 'subjects.name as subject_name')
            ->first();

        return $this->successResponse(['lesson' => $lesson], 'Lesson updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $lesson = DB::table('lesson')->where('id', $id)->first();

        if (!$lesson) {
            return $this->errorResponse('Lesson not found', null, 404);
        }

        DB::table('lesson')->where('id', $id)->delete();

        return $this->successResponse(null, 'Lesson deleted successfully');
    }
}
