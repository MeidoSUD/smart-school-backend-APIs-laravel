<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\Setting;

class StudentFeeService
{
    public function getStudentFees(int $studentSessionId): array
    {
        $fees = DB::table('student_fees_master')
            ->select(
                'student_fees_master.*',
                'fee_groups.name as fee_group_name',
                'fee_groups.is_system',
                'fee_groups_feetype.id as fee_groups_feetype_id',
                'fee_groups_feetype.amount as fee_amount',
                'fee_groups_feetype.due_date',
                'fee_groups_feetype.fine_type',
                'fee_groups_feetype.fine_amount',
                'fee_groups_feetype.fine_percentage',
                'feetype.type as fee_type',
                'feetype.code as fee_code'
            )
            ->join('fee_session_groups', 'fee_session_groups.id', '=', 'student_fees_master.fee_session_group_id')
            ->join('fee_groups', 'fee_groups.id', '=', 'fee_session_groups.fee_groups_id')
            ->join('fee_groups_feetype', 'fee_groups_feetype.fee_session_group_id', '=', 'fee_session_groups.id')
            ->join('feetype', 'feetype.id', '=', 'fee_groups_feetype.feetype_id')
            ->where('student_fees_master.student_session_id', $studentSessionId)
            ->where('student_fees_master.is_active', 'yes')
            ->get();

        return $fees->map(function ($fee) {
            $deposits = DB::table('student_fees_deposite')
                ->where('student_fees_master_id', $fee->id)
                ->where('is_active', 'yes')
                ->get();

            $fee->amount_detail = $deposits->isNotEmpty() ? $deposits->toJson() : null;
            $fee->fees = $deposits->values()->all();

            if ((int) $fee->is_system === 0 && isset($fee->fee_amount)) {
                $fee->amount = $fee->fee_amount;
            }

            return $fee;
        })->values()->all();
    }

    public function getStudentFeesDiscount(int $studentSessionId): array
    {
        return DB::table('student_fees_discounts')
            ->select(
                'student_fees_discounts.*',
                'fees_discounts.name',
                'fees_discounts.code',
                'fees_discounts.type',
                'fees_discounts.percentage',
                'fees_discounts.amount as discount_amount'
            )
            ->join('fees_discounts', 'fees_discounts.id', '=', 'student_fees_discounts.fees_discount_id')
            ->where('student_fees_discounts.student_session_id', $studentSessionId)
            ->where('student_fees_discounts.is_active', 'yes')
            ->get()
            ->values()
            ->all();
    }

    public function getStudentTransportFees(int $studentSessionId, ?int $routePickupPointId = null): array
    {
        $query = DB::table('student_transport_fees')
            ->select(
                'student_transport_fees.*',
                'transport_feemaster.month',
                'transport_feemaster.due_date',
                'transport_feemaster.fine_amount',
                'transport_feemaster.fine_type',
                'transport_feemaster.fine_percentage'
            )
            ->join('transport_feemaster', 'transport_feemaster.id', '=', 'student_transport_fees.transport_feemaster_id')
            ->where('student_transport_fees.student_session_id', $studentSessionId);

        if ($routePickupPointId) {
            $query->where('student_transport_fees.route_pickup_point_id', $routePickupPointId);
        }

        return $query->get()->map(function ($fee) {
            $deposits = DB::table('student_fees_deposite')
                ->where('student_transport_fee_id', $fee->id)
                ->where('is_active', 'yes')
                ->get();

            $fee->fees = $deposits->values()->all();

            return $fee;
        })->values()->all();
    }

    public function isPaymentMethodAvailable(): bool
    {
        $setting = Setting::first();

        if (!$setting) {
            return false;
        }

        return (int) ($setting->is_offline_fee_payment ?? 0) === 1
            || DB::table('payment_settings')->where('is_active', 'yes')->exists();
    }

    public function getDueFeesByStudent(int $studentSessionId, string $asOfDate): array
    {
        $fees = collect($this->getStudentFees($studentSessionId));

        return $fees->filter(function ($fee) use ($asOfDate) {
            if (empty($fee->due_date)) {
                return true;
            }

            return $fee->due_date <= $asOfDate;
        })->values()->all();
    }

    public function feeSummary(int $studentSessionId): array
    {
        $fees = $this->getStudentFees($studentSessionId);

        return [
            'total_fees' => collect($fees)->sum(fn ($f) => (float) ($f->amount ?? 0)),
            'fees_list' => $fees,
        ];
    }
}
