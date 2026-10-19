<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;

/**
 * إعدادات القبول الإلكتروني للمعلم — parity مع CI admin/Onlineadmission::admissionsetting:
 * - GET  → عرض الإعدادات الحالية وحقول النموذج (القراءة: online_admission:can_view)
 * - PUT  → تحديث الإعدادات (يتطلب can_edit؛ CI يفحص can_view هنا وهو ثغرة في المصدر، لم تُنسخ)
 *
 * ملاحظات parity:
 * - CI يحدّث الصف id=1 من sch_settings. هنا نفس الصف عبر الجلسة الحالية.
 * - المبلغ يُحوَّل بسعر العملة الأساسي (currencies.base_price) كما في convertCurrencyFormatToBaseAmount.
 * - إذا كان الدفع مفعلاً يجب أن يكون المبلغ > 0 (validate_paymentamount).
 * - رفع صورة نموذج القبول (multipart) غير منفذ هنا؛ الحقل يبقى كما هو.
 */
class TeacherAdmissionSettingController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherAdmissionSettingController');
    }

    public function show(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_view')) {
            return $this->errorResponse('Forbidden: missing online_admission can_view permission', null, 403);
        }

        $setting = DB::table('sch_settings')
            ->leftJoin('currencies', 'currencies.id', '=', 'sch_settings.currency')
            ->orderBy('sch_settings.id')
            ->select(
                'sch_settings.id',
                'sch_settings.online_admission',
                'sch_settings.online_admission_payment',
                'sch_settings.online_admission_amount',
                'sch_settings.online_admission_instruction',
                'sch_settings.online_admission_conditions',
                'sch_settings.online_admission_application_form',
                'currencies.base_price',
                'currencies.symbol as currency_symbol',
            )
            ->first();

        if (! $setting) {
            return $this->errorResponse('School settings not found', null, 404);
        }

        $fields = DB::table('online_admission_fields')->select('id', 'name', 'status')->orderBy('id')->get();

        return $this->successResponse(['settings' => $setting, 'fields' => $fields]);
    }

    public function update(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_edit')) {
            return $this->errorResponse('Forbidden: missing online_admission can_edit permission', null, 403);
        }

        $v = $request->validate([
            'online_admission' => 'required|in:0,1',
            'online_admission_payment' => 'required|in:yes,no',
            'online_admission_amount' => 'nullable|numeric',
            'online_admission_instruction' => 'nullable|string',
            'online_admission_conditions' => 'nullable|string',
        ]);

        $paymentOn = $v['online_admission_payment'] === 'yes';
        $amount = (float) ($v['online_admission_amount'] ?? 0);

        if ($paymentOn && $amount <= 0) {
            return $this->errorResponse('Invalid payment amount', ['online_admission_amount' => ['Amount must be greater than 0']], 422);
        }

        $basePrice = (float) (DB::table('sch_settings')
            ->leftJoin('currencies', 'currencies.id', '=', 'sch_settings.currency')
            ->orderBy('sch_settings.id')
            ->value('currencies.base_price') ?? 1);
        $basePrice = $basePrice > 0 ? $basePrice : 1;

        $data = [
            'online_admission' => (int) $v['online_admission'],
            'online_admission_payment' => $paymentOn ? 'yes' : 'no',
            'online_admission_amount' => $paymentOn ? round($amount / $basePrice, 2) : 0,
            'online_admission_instruction' => $v['online_admission_instruction'] ?? '',
            'online_admission_conditions' => $v['online_admission_conditions'] ?? '',
        ];

        $id = DB::table('sch_settings')->orderBy('id')->value('id');

        if (! $id) {
            return $this->errorResponse('School settings not found', null, 404);
        }

        DB::table('sch_settings')->where('id', $id)->update($data);

        return $this->successResponse(['id' => $id], 'Record updated successfully');
    }
}
