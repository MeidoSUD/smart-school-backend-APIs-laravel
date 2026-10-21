<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * طلاب فصول المعلم — parity مع:
 * - CI Student::getByClassAndSection + Student_model::searchByClassSectionWithSession
 *   (الجلسة الحالية + is_active=yes + ترتيب id)
 * - CI Student::disablestudentslist + Student_model::disablestudentByClassSection
 *   (الجلسة الحالية + is_active=no + ترتيب id + dis_reason/dis_note/disable_at)
 * - نطاق المعلم: class_section ضمن myClasses وإلا 403 (يقابل فلترة role_id==2 في CI)
 */
class TeacherStudentController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherStudentController');
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $validated = $request->validate([
            'class_id' => 'required_without:search|nullable|integer',
            'section_id' => 'required_with:class_id|nullable|integer',
            'search' => 'required_without:class_id|nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $perPage = (int) ($validated['per_page'] ?? 25);

        if (! empty($validated['class_id'])) {
            $classSectionId = (int) DB::table('class_sections')
                ->where('class_id', $validated['class_id'])
                ->where('section_id', $validated['section_id'])
                ->value('id');

            if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }

            $query = DB::table('students')
                ->join('student_session', 'student_session.student_id', '=', 'students.id')
                ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
                ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
                ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
                ->where('students.is_active', 'yes')
                ->where('student_session.class_id', $validated['class_id'])
                ->where('student_session.section_id', $validated['section_id'])
                ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
                ->select(
                    'students.id', 'students.admission_no', 'students.roll_no',
                    'students.admission_date', 'students.firstname', 'students.middlename',
                    'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                    'students.dob', 'students.gender', 'students.guardian_name',
                    'students.guardian_relation', 'students.guardian_phone',
                    'student_session.id as student_session_id',
                    'classes.class as class_name', 'sections.section as section_name',
                    'categories.category as category_name'
                )
                ->orderBy('students.id');
        } else {
            $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $sessionId))->pluck('class_section_id')->all();

            $query = DB::table('students')
                ->join('student_session', 'student_session.student_id', '=', 'students.id')
                ->join('class_sections', function ($j) {
                    $j->on('class_sections.class_id', '=', 'student_session.class_id')
                        ->on('class_sections.section_id', '=', 'student_session.section_id');
                })
                ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
                ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
                ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
                ->where('students.is_active', 'yes')
                ->whereIn('class_sections.id', $csIds ?: [0])
                ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
                ->select(
                    'students.id', 'students.admission_no', 'students.roll_no',
                    'students.admission_date', 'students.firstname', 'students.middlename',
                    'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                    'students.dob', 'students.gender', 'students.guardian_name',
                    'students.guardian_relation', 'students.guardian_phone',
                    'student_session.id as student_session_id',
                    'classes.class as class_name', 'sections.section as section_name',
                    'categories.category as category_name'
                )
                ->orderBy('students.id');
        }

        if (! empty($validated['search'])) {
            $s = '%' . $validated['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('students.firstname', 'like', $s)
                    ->orWhere('students.lastname', 'like', $s)
                    ->orWhere('students.admission_no', 'like', $s)
                    ->orWhere('students.roll_no', 'like', $s);
            });
        }

        $paginator = $query->paginate($perPage);
        $items = collect($paginator->items())->map(function ($row) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));
            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function view(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student', 'can_view')) {
            return $this->errorResponse('Forbidden: missing student can_view permission', null, 403);
        }

        $sessionId = $ctx->sessionId;

        $student = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->leftJoin('hostel_rooms', 'hostel_rooms.id', '=', 'students.hostel_room_id')
            ->leftJoin('hostel', 'hostel.id', '=', 'hostel_rooms.hostel_id')
            ->leftJoin('room_types', 'room_types.id', '=', 'hostel_rooms.room_type_id')
            ->leftJoin('route_pickup_point', 'route_pickup_point.id', '=', 'student_session.route_pickup_point_id')
            ->leftJoin('pickup_point', 'route_pickup_point.pickup_point_id', '=', 'pickup_point.id')
            ->leftJoin('vehicle_routes', 'vehicle_routes.id', '=', 'student_session.vehroute_id')
            ->leftJoin('transport_route', 'vehicle_routes.route_id', '=', 'transport_route.id')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_routes.vehicle_id')
            ->leftJoin('school_houses', 'school_houses.id', '=', 'students.school_house_id')
            ->leftJoin('users', function ($join) {
                $join->on('users.user_id', '=', 'students.id')->where('users.role', '=', 'student');
            })
            ->where('students.id', $id)
            ->where('students.is_active', 'yes')
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->selectRaw('students.*, student_session.id as student_session_id, student_session.class_id, student_session.section_id, student_session.route_pickup_point_id, student_session.vehroute_id, student_session.transport_fees, student_session.fees_discount, classes.class, classes.class as class_name, sections.section, sections.section as section_name, categories.category, categories.category as category_name, hostel.id as hostel_id, hostel.hostel_name, hostel_rooms.room_no, room_types.id as room_type_id, room_types.room_type, pickup_point.name as pickup_point_name, transport_route.route_title, vehicles.vehicle_no, vehicles.driver_name, vehicles.driver_contact, vehicles.vehicle_model, vehicles.manufacture_year, vehicles.driver_licence, vehicles.vehicle_photo, school_houses.house_name, users.username, users.id as user_id')
            ->first();

        if (! $student) {
            return $this->errorResponse('Student not found', null, 404);
        }

        unset($student->password);
        $student->full_name = trim(implode(' ', array_filter([$student->firstname, $student->middlename, $student->lastname])));

        $studentSessionId = (int) $student->student_session_id;

        $dueFees = $this->getStudentDueFees($studentSessionId);
        $discountFees = $this->getStudentFeeDiscounts($studentSessionId);
        $transportFees = $this->getStudentTransportFees($studentSessionId);
        $siblings = $this->getStudentSiblings((int) ($student->parent_id ?? 0), (int) $student->id, $sessionId);
        $studentDoc = DB::table('student_doc')->where('student_id', $student->id)->get()->all();
        $timelineList = DB::table('student_timeline')->where('student_id', $student->id)->where('status', 'yes')->orderByDesc('timeline_date')->get()->all();
        $behaviour = $this->getStudentBehaviour((int) $student->id, $sessionId);
        $student->total_points = $behaviour['total_points'];
        $examResult = $this->getStudentExamResults($studentSessionId);
        $cbseExams = $this->getStudentCbseExams($studentSessionId);
        $selectedYear = $request->query('year');
        $attendance = $this->getStudentAttendance($studentSessionId, $sessionId, $selectedYear);
        $yearList = DB::table('student_attendences')->selectRaw('DISTINCT YEAR(date) as year')->get()->all();

        $gradeList = DB::table('grades')->get()->all();
        $categoryList = DB::table('categories')->get()->all();
        $attendanceTypesList = DB::table('attendence_type')->orderBy('id')->get()->all();
        $marksDivision = DB::table('mark_divisions')->get()->all();

        $schSetting = DB::table('sch_settings')->where('session_id', $sessionId)->first() ?: DB::table('sch_settings')->orderBy('id')->first();
        $sessionName = DB::table('sessions')->where('id', $sessionId)->value('session') ?? '';
        $classSection = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->where('class_sections.class_id', $student->class_id)
            ->select('class_sections.*', 'sections.section')
            ->get()
            ->all();
        $studentListBySection = DB::table('students')
            ->join('student_session', 'students.id', '=', 'student_session.student_id')
            ->where('student_session.class_id', $student->class_id)
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'yes')
            ->select('students.firstname', 'students.middlename', 'students.id', 'students.lastname', 'students.image', 'student_session.section_id', 'student_session.id as student_session_id')
            ->get()
            ->all();
        $guardianCredential = ((int) ($student->parent_id ?? 0) > 0)
            ? DB::table('users')->where('id', $student->parent_id)->select('id', 'user_id', 'username')->first()
            : null;
        $reasons = DB::table('disable_reason')->get()->all();
        $reasonData = ((int) ($student->dis_reason ?? 0) > 0)
            ? DB::table('disable_reason')->where('id', $student->dis_reason)->first()
            : null;

        $staffRole = DB::table('staff_roles')
            ->join('roles', 'staff_roles.role_id', '=', 'roles.id')
            ->where('staff_roles.staff_id', (int) $ctx->staff->id)
            ->select('roles.*')
            ->first();

        return $this->successResponse([
            'title' => 'student_details',
            'adm_auto_insert' => $schSetting->adm_auto_insert ?? null,
            'student_timeline' => $schSetting->student_timeline ?? null,
            'staffrole' => $staffRole,
            'student' => $student,
            'student_due_fee' => $dueFees,
            'student_discount_fee' => $discountFees,
            'transport_fees' => $transportFees,
            'siblings' => $siblings,
            'student_doc' => $studentDoc,
            'student_doc_id' => $student->id,
            'timeline_list' => $timelineList,
            'assignstudent' => $behaviour['assignstudent'],
            'exams' => $cbseExams,
            'exam_result' => $examResult,
            'exam_grade' => $attendance['exam_grade'],
            'countAttendance' => $attendance['countAttendance'],
            'resultlist' => $attendance['resultlist'],
            'yearlist' => $yearList,
            'selected_year' => $selectedYear,
            'attendencetypeslist' => $attendanceTypesList,
            'category_list' => $categoryList,
            'gradeList' => $gradeList,
            'marks_division' => $marksDivision,
            'sch_setting' => $schSetting,
            'class_section' => $classSection,
            'studentlistbysection' => $studentListBySection,
            'guardian_credential' => $guardianCredential,
            'reason' => $reasons,
            'reason_data' => $reasonData,
            'session' => $sessionName,
            'session_year_start' => $attendance['session_year_start'],
            'session_year_end' => $attendance['session_year_end'],
            'monthlist' => $attendance['monthlist'],
            'superadmin_visible' => 'disabled',
            'role_id' => 2,
        ]);
    }

    /**
     * طلاب معطلون (is_active = 'no') لفصول المعلم — read-only.
     * parity: CI Student::disablestudentslist (disable_student:can_view) → disablestudentByClassSection.
     */
    public function disabledStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'disable_student', 'can_view')) {
            return $this->errorResponse('Forbidden: missing disable_student can_view permission', null, 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $paginator = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'no')
            ->where('student_session.class_id', $validated['class_id'])
            ->where('student_session.section_id', $validated['section_id'])
            ->select(
                'students.id', 'students.admission_no', 'students.roll_no',
                'students.admission_date', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                'students.dob', 'students.gender', 'students.guardian_name',
                'students.guardian_phone', 'students.disable_at',
                'students.dis_reason', 'students.dis_note',
                'student_session.id as student_session_id',
                'classes.id as class_id', 'classes.class as class_name',
                'sections.id as section_id', 'sections.section as section_name',
                DB::raw("COALESCE(categories.category, '') as category_name")
            )
            ->orderBy('students.id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        $items = collect($paginator->items())->map(function ($row) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));

            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function multiClass(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'multi_class_student', 'can_view')) {
            return $this->errorResponse('Forbidden: missing multi_class_student can_view permission', null, 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->value('id');

        if (! $classSectionId) {
            return $this->errorResponse('Class section not found', null, 403);
        }

        $studentIds = DB::table('student_session')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('session_id', $sessionId)
            ->pluck('student_id');

        $paginator = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'yes')
            ->whereIn('students.id', $studentIds)
            ->where('student_session.class_id', $validated['class_id'])
            ->where('student_session.section_id', $validated['section_id'])
            ->select(
                'students.id', 'students.admission_no', 'students.roll_no',
                'students.admission_date', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                'students.dob', 'students.gender',
                'student_session.id as student_session_id',
                'classes.id as class_id', 'classes.class', 'classes.class as class_name',
                'sections.id as section_id', 'sections.section', 'sections.section as section_name',
                DB::raw("COALESCE(categories.category, '') as category_name")
            )
            ->orderBy('students.id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        $studentList = $paginator->items();
        $itemStudentIds = collect($studentList)->pluck('id')->all();

        $allSessions = DB::table('student_session')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->whereIn('student_session.student_id', $itemStudentIds ?: [0])
            ->where('student_session.session_id', $sessionId)
            ->select(
                'student_session.id',
                'student_session.student_id',
                'student_session.class_id',
                'student_session.section_id',
                'classes.class',
                'classes.class as class_name',
                'sections.section',
                'sections.section as section_name',
                'student_session.default_login'
            )
            ->get()
            ->groupBy('student_id');

        $items = collect($studentList)->map(function ($row) use ($allSessions) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));
            $row->student_sessions = $allSessions->get($row->id, collect())->values();
            return $row;
        });

        $allClassSections = DB::table('class_sections')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->select(
                'class_sections.id as class_section_id',
                'class_sections.class_id',
                'classes.class',
                'classes.class as class_name',
                'class_sections.section_id',
                'sections.section',
                'sections.section as section_name'
            )
            ->get()
            ->all();

        return $this->successResponse([
            'students' => $items,
            'classes' => $allClassSections,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student', 'can_add')) {
            return $this->errorResponse('Forbidden: missing student can_add permission', null, 403);
        }

        $v = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'firstname' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'admission_no' => 'required|string|max:100|unique:students,admission_no',
            'gender' => 'required|string|max:20',
            'dob' => 'nullable|date',
            'mobileno' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:50',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->value('id');

        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $now = now();
        $studentId = DB::transaction(function () use ($v, $ctx, $now) {
            $sid = DB::table('students')->insertGetId([
                'admission_no' => $v['admission_no'],
                // CI parity: Student::create omits parent_id on initial insert
                // (NOT NULL col, no default → MySQL non-strict stores 0),
                // then links the parent user id via a follow-up update.
                'parent_id' => 0,
                // CI parity: the remaining NOT NULL-no-default columns are also
                // omitted by CI (non-strict MySQL stores ''/0 implicitly);
                // reproduced here explicitly since the dev DB runs strict mode.
                'blood_group' => '',
                'guardian_is' => '',
                'guardian_occupation' => '',
                'father_pic' => '',
                'mother_pic' => '',
                'guardian_pic' => '',
                'height' => '',
                'weight' => '',
                'dis_reason' => 0,
                'dis_note' => '',
                'firstname' => $v['firstname'],
                'lastname' => $v['lastname'] ?? null,
                'gender' => $v['gender'],
                'dob' => $v['dob'] ?? null,
                'mobileno' => $v['mobileno'] ?? null,
                'email' => $v['email'] ?? null,
                'guardian_name' => $v['guardian_name'] ?? null,
                'guardian_phone' => $v['guardian_phone'] ?? null,
                'is_active' => 'yes',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('student_session')->insert([
                'session_id' => $ctx->sessionId,
                'student_id' => $sid,
                'class_id' => $v['class_id'],
                'section_id' => $v['section_id'],
                'is_active' => 'yes',
                // CI parity: add_student_session omits is_alumni (implicit 0).
                'is_alumni' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $sid;
        });

        return $this->successResponse(['student_id' => $studentId], 'Student admitted successfully');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student', 'can_delete')) {
            return $this->errorResponse('Forbidden: missing student can_delete permission', null, 403);
        }

        $v = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer',
        ]);

        $deleted = DB::table('students')
            ->whereIn('id', $v['student_ids'])
            ->update(['is_active' => 'no', 'updated_at' => now()]);

        return $this->successResponse(['deleted_count' => $deleted], 'Students deactivated successfully');
    }

    private function getStudentDueFees(int $studentSessionId): array
    {
        $feeMasters = DB::table('student_fees_master')
            ->join('fee_session_groups', 'student_fees_master.fee_session_group_id', '=', 'fee_session_groups.id')
            ->join('fee_groups', 'fee_groups.id', '=', 'fee_session_groups.fee_groups_id')
            ->where('student_fees_master.student_session_id', $studentSessionId)
            ->orderBy('student_fees_master.id')
            ->select('student_fees_master.*', 'fee_groups.name')
            ->get();

        foreach ($feeMasters as $fm) {
            $fees = DB::table('student_fees_master')
                ->join('fee_session_groups', 'fee_session_groups.id', '=', 'student_fees_master.fee_session_group_id')
                ->join('fee_groups_feetype', 'fee_groups_feetype.fee_session_group_id', '=', 'fee_session_groups.id')
                ->join('fee_groups', 'fee_groups.id', '=', 'fee_groups_feetype.fee_groups_id')
                ->join('feetype', 'feetype.id', '=', 'fee_groups_feetype.feetype_id')
                ->leftJoin('student_fees_deposite', function ($join) {
                    $join->on('student_fees_deposite.student_fees_master_id', '=', 'student_fees_master.id')
                        ->on('student_fees_deposite.fee_groups_feetype_id', '=', 'fee_groups_feetype.id');
                })
                ->where('student_fees_master.fee_session_group_id', $fm->fee_session_group_id)
                ->where('student_fees_master.id', $fm->id)
                ->orderBy('fee_groups_feetype.due_date', 'asc')
                ->select(
                    'student_fees_master.*',
                    'fee_groups_feetype.id as fee_groups_feetype_id',
                    'fee_groups_feetype.amount',
                    'fee_groups_feetype.due_date',
                    'fee_groups_feetype.fine_amount',
                    'fee_groups_feetype.fee_groups_id',
                    'fee_groups.name',
                    'fee_groups_feetype.feetype_id',
                    'feetype.code',
                    'feetype.type',
                    DB::raw('IFNULL(student_fees_deposite.id, 0) as student_fees_deposite_id'),
                    DB::raw('IFNULL(student_fees_deposite.amount_detail, 0) as amount_detail')
                )
                ->get();

            if ($fm->is_system != 0 && isset($fees[0])) {
                $fees[0]->amount = $fm->amount;
            }
            $fm->fees = $fees;
        }

        return $feeMasters->all();
    }

    private function getStudentFeeDiscounts(int $studentSessionId): array
    {
        return DB::table('student_fees_discounts')
            ->join('fees_discounts', 'fees_discounts.id', '=', 'student_fees_discounts.fees_discount_id')
            ->where('student_fees_discounts.student_session_id', $studentSessionId)
            ->orderBy('student_fees_discounts.id')
            ->select(
                'student_fees_discounts.id',
                'student_fees_discounts.student_session_id',
                'student_fees_discounts.status',
                'student_fees_discounts.payment_id',
                'student_fees_discounts.description as student_fees_discount_description',
                'student_fees_discounts.fees_discount_id',
                'fees_discounts.name',
                'fees_discounts.code',
                'fees_discounts.amount',
                'fees_discounts.description',
                'fees_discounts.session_id',
                'fees_discounts.type',
                'fees_discounts.percentage'
            )
            ->get()
            ->all();
    }

    private function getStudentTransportFees(int $studentSessionId): array
    {
        return DB::table('student_transport_fees')
            ->join('transport_feemaster', 'transport_feemaster.id', '=', 'student_transport_fees.transport_feemaster_id')
            ->join('route_pickup_point', 'route_pickup_point.id', '=', 'student_transport_fees.route_pickup_point_id')
            ->leftJoin('student_fees_deposite', 'student_transport_fees.id', '=', 'student_fees_deposite.student_transport_fee_id')
            ->where('student_transport_fees.student_session_id', $studentSessionId)
            ->select(
                'student_transport_fees.*',
                'transport_feemaster.month',
                'transport_feemaster.due_date',
                'route_pickup_point.fees',
                'transport_feemaster.fine_amount',
                'transport_feemaster.fine_type',
                'transport_feemaster.fine_percentage',
                DB::raw('IFNULL(student_fees_deposite.id, 0) as student_fees_deposite_id'),
                DB::raw('IFNULL(student_fees_deposite.amount_detail, 0) as amount_detail')
            )
            ->get()
            ->all();
    }

    private function getStudentSiblings(int $parentId, int $studentId, int $sessionId): array
    {
        if ($parentId <= 0) {
            return [];
        }

        return DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.parent_id', $parentId)
            ->where('students.id', '!=', $studentId)
            ->where('students.is_active', 'yes')
            ->select(
                'students.id',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.admission_no',
                'students.image',
                'students.gender',
                'classes.class as class_name',
                'sections.section as section_name'
            )
            ->get()
            ->all();
    }

    private function getStudentBehaviour(int $studentId, int $sessionId): array
    {
        $incidents = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('sessions', 'sessions.id', '=', 'student_incidents.session_id')
            ->leftJoin('staff', 'staff.id', '=', 'student_incidents.assign_by')
            ->where('student_incidents.session_id', $sessionId)
            ->where('student_incidents.student_id', $studentId)
            ->orderByDesc('student_incidents.id')
            ->select(
                'student_incidents.id',
                'student_incidents.created_at',
                'student_behaviour.title',
                'student_behaviour.point',
                'student_behaviour.description',
                'sessions.session as session_name',
                'staff.name as staff_name',
                'staff.surname as staff_surname',
                'staff.employee_id as staff_employee_id'
            )
            ->get();

        foreach ($incidents as $inc) {
            $inc->totalcomments = DB::table('student_incident_comments')
                ->where('student_incident_id', $inc->id)
                ->count();
        }

        $totalPoints = (int) DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->where('student_incidents.student_id', $studentId)
            ->where('student_incidents.session_id', $sessionId)
            ->sum('student_behaviour.point');

        return [
            'assignstudent' => $incidents->all(),
            'total_points' => $totalPoints,
        ];
    }

    private function getStudentExamResults(int $studentSessionId): array
    {
        $exams = DB::table('exam_group_class_batch_exam_students')
            ->join('exam_group_class_batch_exams', 'exam_group_class_batch_exams.id', '=', 'exam_group_class_batch_exam_students.exam_group_class_batch_exam_id')
            ->join('exam_groups', 'exam_groups.id', '=', 'exam_group_class_batch_exams.exam_group_id')
            ->where('exam_group_class_batch_exam_students.student_session_id', $studentSessionId)
            ->select(
                'exam_group_class_batch_exam_students.*',
                'exam_group_class_batch_exams.exam_group_id',
                'exam_group_class_batch_exams.exam',
                'exam_group_class_batch_exams.date_from',
                'exam_group_class_batch_exams.date_to',
                'exam_group_class_batch_exams.description',
                'exam_groups.name as exam_group_name',
                'exam_groups.exam_type',
                'exam_group_class_batch_exams.passing_percentage'
            )
            ->orderBy('exam_group_class_batch_exam_students.id')
            ->get();

        foreach ($exams as $exam) {
            $exam->exam_result = DB::table('exam_group_exam_results')
                ->join('exam_group_class_batch_exam_subjects', 'exam_group_class_batch_exam_subjects.id', '=', 'exam_group_exam_results.exam_group_class_batch_exam_subject_id')
                ->join('subjects', 'subjects.id', '=', 'exam_group_class_batch_exam_subjects.subject_id')
                ->where('exam_group_exam_results.exam_group_class_batch_exam_student_id', $exam->id)
                ->select(
                    'exam_group_exam_results.*',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                    'exam_group_class_batch_exam_subjects.max_marks',
                    'exam_group_class_batch_exam_subjects.min_marks'
                )
                ->get()
                ->all();
        }

        return $exams->all();
    }

    private function getStudentAttendance(int $studentSessionId, int $sessionId, ?string $selectedYear = null): array
    {
        $setting = DB::table('sch_settings')->where('session_id', $sessionId)->first();
        if (! $setting) {
            $setting = DB::table('sch_settings')->orderBy('id')->first();
        }

        $startMonth = (int) ($setting->start_month ?? 1);
        $sessionName = (string) ($setting->session ?? '');
        $parts = explode('-', $sessionName);
        $startYear = $parts[0] ?? date('Y');
        $nextYear = isset($parts[1]) && strlen($parts[1]) == 2
            ? substr($startYear, 0, 2) . $parts[1]
            : ($parts[1] ?? $startYear);

        if (! empty($selectedYear)) {
            if (str_contains($selectedYear, '-')) {
                $yParts = explode('-', $selectedYear);
                $startYear = $yParts[0];
                $nextYear = isset($yParts[1]) && strlen($yParts[1]) == 2
                    ? substr($startYear, 0, 2) . $yParts[1]
                    : ($yParts[1] ?? $startYear);
            } else {
                $startYear = $selectedYear;
                $nextYear = (string) (((int) $selectedYear) + 1);
            }
        }

        $endMonth = $startMonth === 1 ? 12 : $startMonth - 1;
        $sessionYearStart = date('Y-m-01', strtotime($startYear . '-' . sprintf('%02d', $startMonth) . '-01'));
        $sessionYearEnd = date('Y-m-t', strtotime($nextYear . '-' . sprintf('%02d', $endMonth) . '-01'));

        $attendances = DB::table('student_attendences')
            ->join('attendence_type', 'attendence_type.id', '=', 'student_attendences.attendence_type_id')
            ->where('student_attendences.student_session_id', $studentSessionId)
            ->whereBetween('student_attendences.date', [$sessionYearStart, $sessionYearEnd])
            ->select('student_attendences.*', 'attendence_type.type as att_type', 'attendence_type.key_value')
            ->get();

        $countAttendance = [];
        $types = DB::table('attendence_type')->get();
        foreach ($types as $t) {
            $countAttendance[$t->id] = 0;
        }
        foreach ($attendances as $att) {
            if (isset($countAttendance[$att->attendence_type_id])) {
                $countAttendance[$att->attendence_type_id]++;
            }
        }

        $attendanceByDate = $attendances->keyBy('date');
        $resultlist = [];
        $current = strtotime($sessionYearStart);
        $last = strtotime($sessionYearEnd);
        while ($current <= $last) {
            $d = date('Y-m-d', $current);
            if (isset($attendanceByDate[$d])) {
                $item = $attendanceByDate[$d];
                $resultlist[$d] = [
                    'date' => $d,
                    'attendence_type_id' => $item->attendence_type_id,
                    'type' => $item->att_type,
                    'key_value' => $item->key_value,
                    'remark' => $item->remark,
                ];
            } else {
                $resultlist[$d] = [];
            }
            $current = strtotime('+1 day', $current);
        }

        $monthlist = [];
        for ($x = $startMonth; $x < $startMonth + 12; $x++) {
            $month = date('m', mktime(0, 0, 0, $x, 10));
            $monthlist[$month] = date('F', mktime(0, 0, 0, $x, 10));
        }

        $gradeTypes = [
            'basic_system' => 'Basic System',
            'school_grade_system' => 'School Grade System',
            'coll_grade_system' => 'College Grade System',
            'gpa' => 'GPA Grading System',
            'average_passing' => 'Average Passing',
        ];
        $examGrade = [];
        foreach ($gradeTypes as $key => $label) {
            $examGrade[] = [
                'exam_key' => $key,
                'exm_type_value' => $label,
                'exam_grade_values' => DB::table('grades')->where('exam_type', $key)->get()->all(),
            ];
        }

        return [
            'countAttendance' => $countAttendance,
            'resultlist' => $resultlist,
            'session_year_start' => $sessionYearStart,
            'session_year_end' => $sessionYearEnd,
            'monthlist' => $monthlist,
            'exam_grade' => $examGrade,
        ];
    }

    private function getStudentCbseExams(int $studentSessionId): array
    {
        $examList = DB::table('cbse_exam_students')
            ->join('cbse_exams', 'cbse_exam_students.cbse_exam_id', '=', 'cbse_exams.id')
            ->where('cbse_exam_students.student_session_id', $studentSessionId)
            ->where('cbse_exams.is_publish', 1)
            ->orderByDesc('cbse_exams.created_at')
            ->select('cbse_exam_students.*', 'cbse_exams.cbse_exam_assessment_id', 'cbse_exams.cbse_term_id', 'cbse_exams.name', 'cbse_exams.use_exam_roll_no', 'cbse_exams.is_active', 'cbse_exams.is_publish', 'cbse_exams.cbse_exam_grade_id', 'cbse_exams.total_working_days')
            ->get();

        foreach ($examList as $ev) {
            $ev->subjects = DB::table('cbse_exam_timetable')
                ->join('subjects', 'subjects.id', '=', 'cbse_exam_timetable.subject_id')
                ->where('cbse_exam_timetable.cbse_exam_id', $ev->cbse_exam_id)
                ->select('cbse_exam_timetable.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
                ->get();

            $ev->results = DB::table('cbse_student_subject_marks')
                ->join('cbse_exam_timetable', 'cbse_exam_timetable.id', '=', 'cbse_student_subject_marks.cbse_exam_timetable_id')
                ->join('subjects', 'subjects.id', '=', 'cbse_exam_timetable.subject_id')
                ->where('cbse_student_subject_marks.cbse_exam_student_id', $ev->id)
                ->select('cbse_student_subject_marks.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
                ->get();
        }

        return $examList->all();
    }
}


