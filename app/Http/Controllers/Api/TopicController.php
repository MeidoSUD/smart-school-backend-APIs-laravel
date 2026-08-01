<?php

namespace App\Http\Controllers\Api;

use App\Models\LessonPlanTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class TopicController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('TopicController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = DB::table('topic')
            ->leftJoin('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->select('topic.*', 'lesson.title as lesson_title');

        if ($request->filled('lesson_id')) {
            $query->where('topic.lesson_id', $request->lesson_id);
        }

        $topics = $query->get();

        return $this->successResponse(['topics' => $topics]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required',
            'lesson_id'  => 'required|exists:lesson,id',
            'name'       => 'required|string|max:255',
            'status'     => 'nullable|string',
        ]);

        $id = DB::table('topic')->insertGetId([
            'session_id'   => $validated['session_id'],
            'lesson_id'    => $validated['lesson_id'],
            'name'         => $validated['name'],
            'status'       => $validated['status'] ?? null,
            'complete_date' => null,
        ]);

        $topic = DB::table('topic')
            ->leftJoin('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->where('topic.id', $id)
            ->select('topic.*', 'lesson.title as lesson_title')
            ->first();

        return $this->successResponse(['topic' => $topic], 'Topic created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $topic = DB::table('topic')
            ->leftJoin('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->where('topic.id', $id)
            ->select('topic.*', 'lesson.title as lesson_title')
            ->first();

        if (!$topic) {
            return $this->errorResponse('Topic not found', null, 404);
        }

        return $this->successResponse(['topic' => $topic]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $existing = DB::table('topic')->where('id', $id)->first();

        if (!$existing) {
            return $this->errorResponse('Topic not found', null, 404);
        }

        $validated = $request->validate([
            'session_id' => 'required',
            'lesson_id'  => 'required|exists:lesson,id',
            'name'       => 'required|string|max:255',
            'status'     => 'nullable|string',
        ]);

        DB::table('topic')->where('id', $id)->update([
            'session_id'    => $validated['session_id'],
            'lesson_id'     => $validated['lesson_id'],
            'name'          => $validated['name'],
            'status'        => $validated['status'] ?? null,
            'complete_date' => $validated['status'] === 'complete' ? now() : $existing->complete_date,
        ]);

        $topic = DB::table('topic')
            ->leftJoin('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->where('topic.id', $id)
            ->select('topic.*', 'lesson.title as lesson_title')
            ->first();

        return $this->successResponse(['topic' => $topic], 'Topic updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $topic = DB::table('topic')->where('id', $id)->first();

        if (!$topic) {
            return $this->errorResponse('Topic not found', null, 404);
        }

        DB::table('topic')->where('id', $id)->delete();

        return $this->successResponse(null, 'Topic deleted successfully');
    }
}
