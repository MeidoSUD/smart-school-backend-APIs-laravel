<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Visitor;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Visitors.php
 */
class VisitorController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('VisitorController');
        }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        // CI source: visitors_model->visitorbystudentid($student_session_id)
        // SELECT visitors_book.* WHERE student_session_id = ? ORDER BY id DESC
        $visitorList = Visitor::where('student_session_id', $studentSession->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn (Visitor $v) => $this->toArray($v))
            ->values();

        $data = ['visitor_list' => $visitorList];

        return $this->successResponse($data);
    }



    public function download(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $visitorlist = Visitor::find($id);

        if (!$visitorlist) {
            return $this->errorResponse('Visitor not found', null, 404);
        }

        // Hardening vs CI (CI user/Visitors::download has no ownership check):
        // a student must only download their own visitor attachments.
        $studentSession = $this->getStudentSession($request->user());
        if (!$studentSession || (int) $visitorlist->student_session_id !== (int) $studentSession->id) {
            return $this->errorResponse('Forbidden', null, 403);
        }

        return $this->sendStoredFile($visitorlist->image, 'uploads/front_office/visitors');
    }

    /**
     * Serialize exactly the CI visitors_book.* columns so the
     * Flutter client sees the same contract as api/user/Visitors.
     */
    private function toArray(Visitor $v): array
    {
        $date = $v->getAttribute('date');
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        } elseif ($date !== null) {
            try {
                $date = Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable) {
                $date = (string) $date;
            }
        }

        return [
            'id' => (int) $v->id,
            'staff_id' => $v->staff_id !== null ? (int) $v->staff_id : null,
            'student_session_id' => $v->student_session_id !== null ? (int) $v->student_session_id : null,
            'source' => $v->source,
            'purpose' => $v->purpose,
            'name' => $v->name,
            'email' => $v->email,
            'contact' => $v->contact,
            'id_proof' => $v->id_proof,
            'no_of_people' => $v->no_of_people !== null ? (int) $v->no_of_people : null,
            'date' => $date,
            'in_time' => $v->in_time,
            'out_time' => $v->out_time,
            'note' => $v->note,
            'image' => $v->image,
            'meeting_with' => $v->meeting_with,
            'created_at' => $v->created_at ? (string) $v->created_at : null,
        ];
    }



    private function getStudentSession($user)
    {
        $studentId = null;
        
        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
            }


        
        if (!$studentId) {
            return null;
            }


        
        $setting = Setting::where('is_active', 'yes')->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->session_id))
            ->first();
        }


    }
