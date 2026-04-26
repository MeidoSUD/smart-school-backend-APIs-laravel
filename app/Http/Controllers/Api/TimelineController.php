<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Timeline.php
 */
class TimelineController extends Controller
{
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'timeline_title' => 'required|string|max:255',
            'timeline_date' => 'required',
            'student_id' => 'required',
        ]);
        
        $document = null;
        if ($request->hasFile('timeline_doc')) {
            $file = $request->file('timeline_doc');
            $document = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_timeline'), $document);
        }
        
        $timeline = StudentTimeline::create([
            'title' => $request->timeline_title,
            'description' => $request->timeline_desc ?? '',
            'timeline_date' => Carbon::parse($request->timeline_date)->format('Y-m-d'),
            'status' => 'yes',
            'date' => now()->format('Y-m-d'),
            'student_id' => $request->student_id,
            'document' => $document,
        ]);
        
        return $this->successResponse(['id' => $timeline->id], 'Timeline added successfully');
    }

    public function getstudentsingletimeline(Request $request): JsonResponse
    {
        $id = $request->post('id');
        
        $singletimelinelist = StudentTimeline::find($id);
        
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
            $document = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_timeline'), $document);
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
}