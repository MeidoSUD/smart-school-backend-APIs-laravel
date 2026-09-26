<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\HomeworkEvaluation;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\DailyAssignment;
use Modules\Academic\Entities\Student;
use Modules\Academic\Http\Requests\HomeworkRequest;
use Modules\Academic\Http\Requests\DailyAssignmentRequest;
use Modules\Core\Services\StudentSessionService;
use Modules\Core\Entities\Setting;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use DB;

class HomeworkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('HomeworkController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $studentSession->student_id;
        $studentSessionId = $studentSession->id;
        $currentSessionId = $studentSession->session_id;

        $mapHomework = function ($homework) {
            // CI: LEFT JOIN homework_evaluation on homework_id + student_session_id
            $evaluation = $homework->homeworkEvaluations->first();
            $homework->homework_evaluation_id = $evaluation ? (int) $evaluation->id : 0;
            $homework->evaluation_marks = $evaluation ? $evaluation->marks : null;
            $homework->note = $evaluation ? ($evaluation->note ?? '') : '';

            // CI Homework::index: status = 'submitted' iff submit_assignment row exists
            $homework->status = $homework->submission_status > 0 ? 'submitted' : '';

            $homework->class = $homework->class->class ?? '';
            $homework->section = $homework->section->section ?? '';
            // CI canonical subject resolution via subject_group_subject_id;
            // fall back to legacy subject_id column (seeded data keeps both).
            $sgSubject = $homework->subjectGroupSubject;
            $subjectName = $sgSubject && $sgSubject->subject ? $sgSubject->subject->name : ($homework->subject->name ?? '');
            $subjectCode = $sgSubject && $sgSubject->subject ? $sgSubject->subject->code : ($homework->subject->code ?? '');
            $homework->subject_name = $subjectName ?? '';
            $homework->subject_code = $subjectCode ?? '';
            $homework->subject_groups_id = $sgSubject ? $sgSubject->subject_group_id : null;

            // Relations would override same-named attributes on toArray(),
            // so detach them first — otherwise Flutter receives full objects.
            $homework->unsetRelation('class');
            $homework->unsetRelation('section');
            $homework->unsetRelation('subject');
            $homework->unsetRelation('subjectGroupSubject');
            $homework->makeHidden(['homeworkEvaluations']);

            return $homework;
        };

        $baseQuery = function ($submitDateOperator) use ($studentSession, $studentId, $studentSessionId, $currentSessionId) {
            return Homework::where('class_id', $studentSession->class_id)
                ->where('section_id', $studentSession->section_id)
                ->where('session_id', $currentSessionId)
                ->where('submit_date', $submitDateOperator, now()->toDateString())
                ->with(['class', 'section', 'subject', 'subjectGroupSubject.subject', 'homeworkEvaluations' => function ($q) use ($studentSessionId) {
                    $q->where('student_session_id', $studentSessionId);
                }])
                ->withCount(['submitAssignments as submission_status' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                }])
                ->orderBy('homework_date', 'desc');
        };

        $homeworklist = $baseQuery('>=')->get()->map($mapHomework);

        $closedhomeworklist = $baseQuery('<')->get()->map($mapHomework);

        $data = [
            'created_by' => '',
            'evaluated_by' => '',
            'homeworklist' => $homeworklist,
            'closedhomeworklist' => $closedhomeworklist,
        ];

        return $this->successResponse($data);
    }

    public function upload_docs(HomeworkRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $homeworkId = $request->homework_id;

        $submissionExists = SubmitAssignment::where('homework_id', $homeworkId)
            ->where('student_id', $studentId)
            ->exists();

        if (!$submissionExists && !$request->hasFile('file')) {
            return $this->errorResponse('File is required');
        }

        $data = [
            'homework_id' => $homeworkId,
            'student_id' => $studentId,
            'message' => $request->message,
        ];

        // CI Homework::upload_docs: upsert on (homework_id, student_id);
        // when no new file is uploaded the previous docs value is preserved.
        DB::transaction(function () use ($request, &$data, $homeworkId, $studentId) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('uploads/homework/assignment', $filename, 'local');
                $data['docs'] = $filename;
            }

            SubmitAssignment::updateOrCreate(
                ['homework_id' => $homeworkId, 'student_id' => $studentId],
                $data
            );
        });

        return $this->successResponse(null, 'Homework submitted successfully');
    }

    public function homework_detail($id, $status, Request $request): JsonResponse
    {
        $result = Homework::with(['class', 'section', 'subjectGroupSubject.subject', 'subjectGroupSubject.subjectGroup'])->find($id);

        if (!$result) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);
        $studentId = $this->studentSessionService->getStudentId($user);

        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;

        $classId = $result->class_id;
        $sectionId = $result->section_id;

        // CI Homework_model::getStudents: classmates + their evaluation +
        // their submitted assignment (assignmentlist per student).
        $studentlist = DB::table('student_session')
            ->select(
                'student_session.*', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.admission_no',
                DB::raw('IFNULL(homework_evaluation.id,0) as homework_evaluation_id'),
                'homework_evaluation.note', 'homework_evaluation.marks'
            )
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('homework_evaluation', function ($join) use ($id) {
                $join->on('homework_evaluation.student_session_id', '=', 'student_session.id')
                    ->where('homework_evaluation.homework_id', '=', $id);
            })
            ->where('student_session.class_id', $classId)
            ->where('student_session.section_id', $sectionId)
            ->where('student_session.session_id', $result->session_id)
            ->where('students.is_active', 'yes')
            ->orderBy('students.id', 'desc')
            ->get()
            ->map(function ($row) use ($id) {
                $row->assignmentlist = DB::table('submit_assignment')
                    ->select('id as submit_assignment_id', 'docs', 'message', 'student_id')
                    ->where('homework_id', $id)
                    ->where('student_id', $row->student_id)
                    ->get();
                return $row;
            });

        // CI Homework_model::getEvaluationReportForStudent: this student's
        // evaluation row, otherwise an Incomplete stub carrying the date.
        $reportRow = HomeworkEvaluation::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->first();
        if ($reportRow) {
            $report = $reportRow;
        } else {
            $firstEval = HomeworkEvaluation::where('homework_id', $id)->first();
            $report = ['date' => $firstEval ? $firstEval->date : null, 'status' => 'Incomplete'];
        }

        // CI get_homeworkDocByIdStdid: submission + student names.
        $homeworkdocs = DB::table('submit_assignment')
            ->select('submit_assignment.*', 'students.firstname', 'students.middlename', 'students.lastname')
            ->join('students', 'students.id', '=', 'submit_assignment.student_id')
            ->where('submit_assignment.homework_id', $id)
            ->where('submit_assignment.student_id', $studentId)
            ->get();

        $created_by = '';
        $evaluated_by = '';

        $createData = Staff::find($result->created_by);
        if ($createData && ($superadminRestriction != 'disabled' || $createData->role_id != 7)) {
            $created_by = ($createData->surname ? $createData->name . ' ' . $createData->surname : $createData->name) . ' (' . $createData->employee_id . ')';
        }

        if ($result->evaluated_by) {
            $evalData = Staff::find($result->evaluated_by);
            if ($evalData && ($superadminRestriction != 'disabled' || $evalData->role_id != 7)) {
                $evaluated_by = ($evalData->surname ? $evalData->name . ' ' . $evalData->surname : $evalData->name) . ' (' . $evalData->employee_id . ')';
            }
        }

        $checkstatus = SubmitAssignment::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->count();
        $homeworkStatus = $checkstatus > 0 ? 'submitted' : '';

        // CI Homework_model::getRecord fields used by homework_detail.php:
        // class / section / subject name+code shown in the summary panel.
        // unsetRelation first: loaded relations would otherwise override
        // these same-named scalar attributes during serialization.
        $sgSubject = $result->subjectGroupSubject;
        $className = $result->class->class ?? '';
        $sectionName = $result->section->section ?? '';
        $subjectName = ($sgSubject && $sgSubject->subject) ? $sgSubject->subject->name : ($result->subject->name ?? '');
        $subjectCode = ($sgSubject && $sgSubject->subject) ? $sgSubject->subject->code : ($result->subject->code ?? '');
        $result->unsetRelation('class');
        $result->unsetRelation('section');
        $result->unsetRelation('subject');
        $result->unsetRelation('subjectGroupSubject');
        $result->setAttribute('class', $className);
        $result->setAttribute('section', $sectionName);
        $result->setAttribute('name', $subjectName);
        $result->setAttribute('code', $subjectCode);

        $data = [
            'homework_status' => (int) $status,
            'homework_id' => (int) $id,
            'title' => 'Homework Evaluation',
            'result' => $result,
            'studentlist' => $studentlist,
            'report' => $report,
            'homeworkdocs' => $homeworkdocs,
            'created_by' => $created_by,
            'evaluated_by' => $evaluated_by,
            'status' => $homeworkStatus,
        ];

        return $this->successResponse($data);
    }

    public function download(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $homework = Homework::find($id);

        if (! $homework) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        return $this->sendStoredFile($homework->document, 'uploads/homework/document', 'uploads/homework');
    }

    public function assigmnetDownload(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);

        // CI Homework::assigmnetDownload($id): $id is the HOMEWORK id; the
        // student's own submission (homework_id + student_id) is downloaded.
        // Accept a submit_assignment id as fallback (with ownership check).
        $assignment = SubmitAssignment::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->first()
            ?? SubmitAssignment::find($id);

        if (! $assignment || empty($assignment->docs)) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        if (! $studentId || (int) $assignment->student_id !== (int) $studentId) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        return $this->sendStoredFile($assignment->docs, 'uploads/homework/assignment');
    }

    public function dailyassignment(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $this->studentSessionService->getStudentId($user);

        // CI Homework_model::getdailyassignment: rows for this student_session
        // OR any row whose student_session belongs to this student.
        // The OR must be grouped, otherwise the student_session filter leaks.
        $dailyassignmentlist = DB::table('daily_assignment')
            ->select('daily_assignment.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->leftJoin('student_session', 'student_session.id', '=', 'daily_assignment.student_session_id')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'daily_assignment.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->where(function ($q) use ($studentSession, $studentId) {
                $q->where('daily_assignment.student_session_id', $studentSession->id)
                    ->orWhere('student_session.student_id', $studentId);
            })
            // No groupBy: all joins are to-one, and MySQL strict
            // ONLY_FULL_GROUP_BY rejects `select *, joined.col group by id`.
            ->orderBy('daily_assignment.id', 'desc')
            ->get();

        $data = [
            'dailyassignmentlist' => $dailyassignmentlist,
            // Alias for older Flutter parsing key.
            'dailyassignment' => $dailyassignmentlist,
            // CI dailyassignment(): subject dropdown via
            // Subjectgroup_model::getAllsubjectByClassSection.
            'subjectlist' => DB::table('subject_group_class_sections')
                ->select(
                    'subject_group_subjects.id as subject_group_subject_id',
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code'
                )
                ->join('class_sections', 'class_sections.id', '=', 'subject_group_class_sections.class_section_id')
                ->join('subject_groups', 'subject_groups.id', '=', 'subject_group_class_sections.subject_group_id')
                ->join('subject_group_subjects', 'subject_group_subjects.subject_group_id', '=', 'subject_groups.id')
                ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
                ->where('class_sections.class_id', $studentSession->class_id)
                ->where('class_sections.section_id', $studentSession->section_id)
                ->where('subject_group_class_sections.session_id', $studentSession->session_id)
                ->get(),
        ];

        return $this->successResponse($data);
    }

    public function getsingledailyassignment($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $this->studentSessionService->getStudentId($user);

        $assignment = DB::table('daily_assignment')
            ->select('daily_assignment.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->leftJoin('student_session', 'student_session.id', '=', 'daily_assignment.student_session_id')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'daily_assignment.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->where('daily_assignment.id', $id)
            ->where(function ($q) use ($studentSession, $studentId) {
                $q->where('daily_assignment.student_session_id', $studentSession->id)
                    ->orWhere('student_session.student_id', $studentId);
            })
            ->first();

        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        return $this->successResponse(['dailyassignment' => $assignment, 'singleassignmentlist' => $assignment]);
    }

    public function createdailyassignment(DailyAssignmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $data = [
            'title' => $request->title,
            'student_session_id' => $studentSession->id,
            'description' => $request->description,
            'subject_group_subject_id' => $request->subject,
            'date' => date('Y-m-d'),
            'evaluated_by' => null,
            'attachment' => null,
            'remark' => ''
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/homework/daily_assignment', $filename, 'local');
            $data['attachment'] = $filename;
        }

        DailyAssignment::create($data);

        return $this->successResponse(null, 'Record Saved Successfully');
    }

    public function updatedailyassignment(DailyAssignmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $assignment = DailyAssignment::find($request->assigment_id);
        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        $data = [
            'title' => $request->title,
            'student_session_id' => $studentSession->id,
            'description' => $request->description,
            'subject_group_subject_id' => $request->subject,
            'date' => date('Y-m-d'),
        ];

        if ($request->hasFile('file')) {
            if ($assignment->attachment) {
                Storage::disk('local')->delete('uploads/homework/daily_assignment/' . $assignment->attachment);
            }
            $file = $request->file('file');
            $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/homework/daily_assignment', $filename, 'local');
            $data['attachment'] = $filename;
        }

        $assignment->update($data);

        return $this->successResponse(null, 'Record Updated Successfully');
    }

    public function deletedailyassignment($id): JsonResponse
    {
        $assignment = DailyAssignment::find($id);

        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        if ($assignment->attachment) {
            Storage::disk('local')->delete('uploads/homework/daily_assignment/' . $assignment->attachment);
        }

        $assignment->delete();

        return $this->successResponse(null, 'Record Deleted Successfully');
    }

    public function dailyassigmnetdownload($id)
    {
        $assignment = DailyAssignment::find($id);

        if (!$assignment || !$assignment->attachment) {
            return $this->errorResponse('File not found', null, 404);
        }

        $path = storage_path('app/uploads/homework/daily_assignment/' . $assignment->attachment);
        if (!file_exists($path)) {
            return $this->errorResponse('File not found', null, 404);
        }

        return response()->download($path);
    }
}
