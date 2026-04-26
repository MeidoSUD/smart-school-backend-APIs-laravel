<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Calendar.php
 */
class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $eventColors = ["#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b"];
        
        $totalRows = CalendarEvent::where('event_for', $user->id)->count();
        
        $perPage = 10;
        $tasklist = CalendarEvent::where('event_for', $user->id)
            ->orWhere('event_type', '!=', 'task')
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
        
        $data = [
            'event_colors' => $eventColors,
            'tasklist' => $tasklist,
            'title' => 'Event Calendar',
        ];
        
        return $this->successResponse($data);
    }

    public function getevents(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $result = CalendarEvent::where('status', 'yes')
            ->where('event_type', '!=', 'private')
            ->get();
        
        $eventdata = [];
        foreach ($result as $value) {
            $eventType = $value->event_type;
            $status = 1;
            
            if ($eventType == 'task') {
                $eventFor = $user->id;
                $status = ($eventFor == $value->event_for) ? 1 : 0;
            }
            
            if ($status == 1) {
                $eventdata[] = [
                    'title' => $value->event_title,
                    'start' => $value->start_date,
                    'end' => $value->end_date,
                    'description' => $value->event_description,
                    'id' => $value->id,
                    'backgroundColor' => $value->event_color,
                    'borderColor' => $value->event_color,
                    'event_type' => $value->event_type,
                ];
            }
        }
        
        return $this->successResponse($eventdata);
    }

    public function addtodo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_title' => 'required|string|max:255',
            'task_date' => 'required',
        ]);
        
        $user = $request->user();
        
        $eventTitle = $request->task_title;
        $eventType = 'task';
        $eventColor = '#000';
        $startDate = Carbon::parse($request->task_date)->format('Y-m-d H:i:s');
        $eventId = $request->eventid;
        
        if ($eventId) {
            CalendarEvent::where('id', $eventId)->update([
                'event_title' => $eventTitle,
                'event_description' => '',
                'start_date' => $startDate,
                'end_date' => $startDate,
                'event_type' => $eventType,
                'event_color' => $eventColor,
                'event_for' => $user->id,
            ]);
            $msg = 'Task updated successfully';
        } else {
            CalendarEvent::create([
                'event_title' => $eventTitle,
                'event_description' => '',
                'start_date' => $startDate,
                'end_date' => $startDate,
                'event_type' => $eventType,
                'event_color' => $eventColor,
                'is_active' => 'no',
                'event_for' => $user->id,
            ]);
            $msg = 'Task created successfully';
        }
        
        return $this->successResponse(null, $msg);
    }

    public function gettaskbyid($id): JsonResponse
    {
        $result = CalendarEvent::find($id);
        return $this->successResponse($result);
    }

    public function markcomplete(Request $request, $id): JsonResponse
    {
        $status = $request->input('active', 'yes');
        
        CalendarEvent::where('id', $id)->update(['is_active' => $status]);
        
        return $this->successResponse(null, 'Marked as completed successfully');
    }

    public function delete_event($id): JsonResponse
    {
        CalendarEvent::destroy($id);
        return $this->successResponse(null, 'Event deleted successfully');
    }
}