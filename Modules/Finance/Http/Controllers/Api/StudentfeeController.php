<?php

namespace Modules\Finance\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Modules\Core\Services\StudentSessionService;
use Modules\Finance\Entities\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only port of CodeIgniter api/user/Studentfee.php.
 * Cash/admin entry methods (addfee, add_Ajaxfee, deleteFee, ...) are
 * intentionally out of scope for the student app (T-4.6).
 */
class StudentfeeController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('StudentfeeController');
    }

    public function view($id, Request $request): JsonResponse
    {
        // CI source: api/user/Studentfee.php view($id) keys: title, studentfee.
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentfee = StudentFee::find($id);

        if (!$studentfee) {
            return $this->errorResponse('Student fee not found', null, 404);
        }

        if ((int) $studentfee->student_session_id !== (int) $studentSession->id) {
            return $this->errorResponse('Forbidden', null, 403);
        }

        return $this->successResponse([
            'title' => 'studentfee List',
            'studentfee' => $studentfee,
        ]);
    }

    public function searchpayment(Request $request): JsonResponse
    {
        // CI source: api/user/Studentfee.php searchpayment(): GET returns the
        // title only; POST search=search_filter + paymentid returns
        // expenseList (transport) + feeList (invoice).
        $data = ['title' => 'Search With Payment ID'];

        if ($request->isMethod('post')) {
            if ($request->post('search') !== 'search_filter') {
                return $this->successResponse($data);
            }

            $user = $request->user();
            $studentSession = $this->studentSessionService->getStudentSession($user);

            if (!$studentSession) {
                return $this->errorResponse('Student session not found');
            }

            $paymentId = $request->post('paymentid');
            $setting = Setting::where('is_active', 'yes')->first();

            $data['exp_title'] = 'Transaction';
            $data['expenseList'] = $this->getTransportFeeByPayment($paymentId, $studentSession->id, $setting?->session_id);
            $data['feeList'] = $this->getFeeByInvoice($paymentId, $setting?->session_id);
        }

        return $this->successResponse($data);
    }

    private function getFeeByInvoice(mixed $paymentId, mixed $sessionId): array
    {
        // Mirrors Studentfee_model::getFeeByInvoice().
        return DB::table('student_fees')
            ->join('student_session', 'student_session.id', '=', 'student_fees.student_session_id')
            ->join('feemasters', 'feemasters.id', '=', 'student_fees.feemaster_id')
            ->join('feetype', 'feetype.id', '=', 'feemasters.feetype_id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('feecategory', 'feetype.feecategory_id', '=', 'feecategory.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->where('student_fees.id', $paymentId)
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->select(
                'feecategory.category',
                'student_fees.date',
                'student_fees.payment_mode',
                'student_fees.id as student_fee_id',
                'student_fees.amount',
                'student_fees.amount_discount',
                'student_fees.amount_fine',
                'student_fees.created_at',
                'classes.class',
                'sections.section',
                'feetype.type',
                'students.id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname'
            )
            ->orderBy('student_fees.id')
            ->get()
            ->all();
    }

    private function getTransportFeeByPayment(mixed $paymentId, int $studentSessionId, mixed $sessionId): array
    {
        // Transport counterpart of the invoice lookup, scoped to the
        // authenticated student's session (CI has no equivalent method on the
        // model — reported, ownership enforced here instead of guessing).
        return DB::table('student_transport_fees')
            ->join('transport_feemaster', 'transport_feemaster.id', '=', 'student_transport_fees.transport_feemaster_id')
            ->join('student_session', 'student_session.id', '=', 'student_transport_fees.student_session_id')
            ->join('route_pickup_point', 'route_pickup_point.id', '=', 'student_transport_fees.route_pickup_point_id')
            ->where('student_transport_fees.id', $paymentId)
            ->where('student_transport_fees.student_session_id', $studentSessionId)
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->select(
                'student_transport_fees.*',
                'transport_feemaster.month',
                'transport_feemaster.due_date',
                'route_pickup_point.fees'
            )
            ->orderBy('student_transport_fees.id')
            ->get()
            ->all();
    }
}
