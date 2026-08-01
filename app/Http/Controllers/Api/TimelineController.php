<?php

namespace App\Http\Controllers\Api;

use App\Models\StudentTimeline;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimelineController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('TimelineController');
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'timeline_title' => 'required|string|max:255',
            'timeline_date' => 'required',
            'student_id' => 'required',
        ]);

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        if (!$studentId) {
            return $this->errorResponse('Unauthorized');
        }

        if ((int) $request->student_id !== (int) $studentId) {
            return $this->errorResponse('You can only add timeline for yourself', null, 403);
        }

        $document = null;
        if ($request->hasFile('timeline_doc')) {
            $file = $request->file('timeline_doc');
            $document = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/student_timeline', $document, 'local');
        }

        $timeline = StudentTimeline::create([
            'title' => $request->timeline_title,
            'description' => $request->timeline_desc ?? '',
            'timeline_date' => Carbon::parse($request->timeline_date)->format('Y-m-d'),
            'status' => true,
            'date' => now()->format('Y-m-d'),
            'student_id' => $request->student_id,
            'document' => $document,
            'created_student_id' => $studentId,
        ]);

        return $this->successResponse($timeline, 'Timeline added successfully');
    }

    public function getstudentsingletimeline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:student_timelines,id',
        ]);

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        $singletimelinelist = StudentTimeline::where('id', $request->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$singletimelinelist) {
            return $this->errorResponse('Timeline not found', null, 404);
        }

        return $this->successResponse(['singletimelinelist' => $singletimelinelist]);
    }

    public function edit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required',
            'timeline_title' => 'required|string|max:255',
            'timeline_date' => 'required',
        ]);

        $timeline = StudentTimeline::find($request->id);

        if (!$timeline) {
            return $this->errorResponse('Timeline not found', null, 404);
        }

        $document = $timeline->document;
        if ($request->hasFile('timeline_doc')) {
            $file = $request->file('timeline_doc');
            $document = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/student_timeline', $document, 'local');
        }

        $timeline->update([
            'title' => $request->timeline_title,
            'description' => $request->timeline_desc ?? '',
            'timeline_date' => Carbon::parse($request->timeline_date)->format('Y-m-d'),
            'document' => $document,
        ]);

        return $this->successResponse(null, 'Timeline updated successfully');
    }

    public function download($id): JsonResponse
    {
        $timelinelist = StudentTimeline::find($id);

        if (!$timelinelist) {
            return $this->errorResponse('Timeline not found', null, 404);
        }

        return $this->successResponse(['document' => $timelinelist->document]);
    }

    public function delete_timeline(Request $request): JsonResponse
    {
        $id = $request->post('id');

        $timeline = StudentTimeline::find($id);

        if ($timeline && $timeline->document) {
            $filePath = public_path('uploads/student_timeline/' . $timeline->document);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        StudentTimeline::destroy($id);

        return $this->successResponse(null, 'Timeline deleted successfully');
    }

    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
        }

        return null;
    }
}
