<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Operations\Entities\BehaviourSetting;
use Modules\Operations\Entities\StudentIncident;
use Modules\Operations\Entities\StudentIncidentComment;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student behaviour records (behaviour_records addon).
 * Converted from CodeIgniter: codeIgniter/student cotroller/behaviour/* and
 * student/User.php::profile() behaviour block.
 */
class BehaviourController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('BehaviourController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        if (! $studentId) {
            return $this->errorResponse('Student not found', null, 404);
        }

        $incidents = StudentIncident::where('student_id', $studentId)
            ->with('behaviour')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StudentIncident $incident) => [
                'id' => $incident->id,
                'incident_id' => $incident->incident_id,
                'title' => $incident->behaviour->title ?? null,
                'description' => $incident->behaviour->description ?? null,
                'point' => $incident->behaviour->point ?? null,
                'assigned_at' => $incident->created_at,
            ])
            ->values();

        $totalPoints = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->where('student_incidents.student_id', $studentId)
            ->sum('student_behaviour.point');

        $settings = BehaviourSetting::first();

        return $this->successResponse([
            'incidents' => $incidents,
            'total_points' => (int) $totalPoints,
            'behavioursetting' => $settings ? ['comment_option' => $settings->comment_option] : null,
            'role' => $user->role,
        ]);
    }

    public function addmessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_incident_id' => 'required|integer|exists:student_incidents,id',
            'comment' => 'required|string',
        ]);

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        if (! $studentId) {
            return $this->errorResponse('Student not found', null, 404);
        }

        $incident = StudentIncident::where('id', $validated['student_incident_id'])
            ->where('student_id', $studentId)
            ->first();

        if (! $incident) {
            return $this->errorResponse('Incident not found for this student', null, 404);
        }

        $comment = StudentIncidentComment::create([
            'student_incident_id' => $validated['student_incident_id'],
            'type' => $user->role === 'parent' ? 'parent' : 'student',
            'comment' => $validated['comment'],
            'student_id' => $studentId,
            'staff_id' => 0,
            'created_date' => now(),
        ]);

        return $this->successResponse(['comment' => $comment], 'Comment added successfully');
    }

    public function getmessage(Request $request): JsonResponse
    {
        $studentIncidentId = $request->input('student_incident_id');

        if (! $studentIncidentId) {
            return $this->errorResponse('student_incident_id is required');
        }

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        $incident = StudentIncident::where('id', $studentIncidentId)
            ->where('student_id', $studentId)
            ->first();

        if (! $incident) {
            return $this->errorResponse('Incident not found for this student', null, 404);
        }

        $comments = StudentIncidentComment::where('student_incident_id', $studentIncidentId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (StudentIncidentComment $comment) {
                $userName = null;

                if ($comment->type === 'staff' && $comment->staff_id) {
                    $staff = Staff::find($comment->staff_id);
                    if ($staff) {
                        $userName = trim(($staff->name ?? '') . ' ' . ($staff->surname ?? ''));
                    }
                } elseif ($comment->student_id) {
                    $student = Student::find($comment->student_id);
                    if ($student) {
                        $userName = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
                    }
                }

                return [
                    'id' => $comment->id,
                    'student_incident_id' => $comment->student_incident_id,
                    'type' => $comment->type,
                    'comment' => $comment->comment,
                    'user_name' => $userName,
                    'created_date' => $comment->created_date,
                ];
            })
            ->values();

        return $this->successResponse([
            'messagelist' => $comments,
            'student_incident_id' => (int) $studentIncidentId,
            'role' => $user->role,
        ]);
    }

    public function delete_comment(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if (! $id) {
            return $this->errorResponse('id is required');
        }

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        $comment = StudentIncidentComment::where('id', $id)
            ->where('student_id', $studentId)
            ->first();

        if (! $comment) {
            return $this->errorResponse('Comment not found', null, 404);
        }

        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully');
    }

    private function getStudentId($user): ?int
    {
        if ($user->role === 'student') {
            return isset($user->user_id) ? (int) $user->user_id : null;
        }

        if ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();

            return $student ? (int) $student->id : null;
        }

        return null;
    }
}