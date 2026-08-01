<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\StudentSession;
use App\Models\Session;
use App\Models\FeeDiscount;
use App\Models\FeeGroup;
use App\Models\FeeSessionGroup;
use App\Models\FeeGroupsFeetype;
use App\Models\FeeType;
use App\Models\StudentFeeMaster;
use App\Models\StudentFeesDeposite;
use App\Models\StudentFeesDiscount;
use App\Models\StudentTransportFee;
use App\Models\TransportFeemaster;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        if (FeeGroup::exists()) {
            return;
        }

        $session = Session::first();
        if (!$session) {
            return;
        }

        if (!Schema::hasTable('feecategory')) {
            Schema::create('feecategory', function ($table) {
                $table->bigIncrements('id');
                $table->string('category')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        $feeCategory = DB::table('feecategory')->insertGetId([
            'category' => 'الرسوم الدراسية',
            'is_active' => 1,
            'created_at' => now(),
        ]);

        $feeTypes = [
            ['feecategory_id' => $feeCategory, 'type' => 'الرسوم الدراسية', 'code' => 'tuition_fee', 'is_active' => 'yes'],
            ['feecategory_id' => $feeCategory, 'type' => 'رسوم الأنشطة', 'code' => 'activity_fee', 'is_active' => 'yes'],
            ['feecategory_id' => $feeCategory, 'type' => 'رسوم الكتب', 'code' => 'book_fee', 'is_active' => 'yes'],
        ];

        $createdFeeTypes = [];
        foreach ($feeTypes as $ft) {
            $createdFeeTypes[] = FeeType::create($ft);
        }

        $feeGroup = FeeGroup::create([
            'name' => 'الرسوم الأساسية',
            'is_system' => 0,
            'description' => 'مجموعة الرسوم الأساسية للفصل الدراسي',
            'is_active' => 'yes',
        ]);

        $feeSessionGroup = FeeSessionGroup::create([
            'fee_groups_id' => $feeGroup->id,
            'session_id' => $session->id,
            'is_active' => 'yes',
        ]);

        $feeGroupsFeetypes = [];
        foreach ($createdFeeTypes as $index => $feeType) {
            $amount = match ($index) {
                0 => 5000.00,
                1 => 500.00,
                2 => 300.00,
                default => 1000.00,
            };
            $feeGroupsFeetypes[] = FeeGroupsFeetype::create([
                'fee_session_group_id' => $feeSessionGroup->id,
                'fee_groups_id' => $feeGroup->id,
                'feetype_id' => $feeType->id,
                'session_id' => $session->id,
                'amount' => $amount,
                'fine_type' => 'percentage',
                'due_date' => now()->addMonth()->format('Y-m-d'),
                'fine_percentage' => 2.00,
                'fine_amount' => 0.00,
                'is_active' => 'yes',
            ]);
        }

        $studentSessions = StudentSession::all();
        foreach ($studentSessions as $studentSession) {
            $feeMaster = StudentFeeMaster::create([
                'is_system' => 0,
                'student_session_id' => $studentSession->id,
                'fee_session_group_id' => $feeSessionGroup->id,
                'amount' => 5800.00,
                'is_active' => 'yes',
            ]);

            $amountDetail = json_encode([[
                'amount' => 1000.00,
                'amount_discount' => 0.00,
                'amount_fine' => 0.00,
                'date' => now()->format('Y-m-d'),
                'payment_mode' => 'Online',
                'description' => '', 'inv_no' => '',
            ]]);

            StudentFeesDeposite::create([
                'student_fees_master_id' => $feeMaster->id,
                'fee_groups_feetype_id' => $feeGroupsFeetypes[0]->id,
                'amount_detail' => $amountDetail,
                'is_active' => 'yes',
            ]);
        }

        $transportFeemaster = TransportFeemaster::create([
            'session_id' => $session->id,
            'month' => '2024-09',
            'due_date' => now()->addMonth()->format('Y-m-d'),
            'fine_amount' => 50.00,
            'fine_type' => 'fixed',
            'fine_percentage' => 0.00,
        ]);

        $routeId = DB::table('transport_route')->insertGetId([
            'route_title' => 'الطريق الرئيسي',
            'no_of_vehicle' => 1,
            'note' => '',
            'is_active' => 'yes',
            'created_at' => now(),
        ]);

        $pickupPointId = DB::table('pickup_point')->insertGetId([
            'name' => 'المدرسة',
            'created_at' => now(),
        ]);

        $routePickupPointId = DB::table('route_pickup_point')->insertGetId([
            'transport_route_id' => $routeId,
            'pickup_point_id' => $pickupPointId,
            'fees' => 0.00,
            'order_number' => 1,
            'created_at' => now(),
        ]);

        foreach ($studentSessions as $studentSession) {
            StudentTransportFee::create([
                'transport_feemaster_id' => $transportFeemaster->id,
                'student_session_id' => $studentSession->id,
                'route_pickup_point_id' => $routePickupPointId,
                'generated_by' => 1,
            ]);
        }

        $feeDiscount = FeeDiscount::create([
            'session_id' => $session->id,
            'name' => 'خصم التفوق الدراسي',
            'code' => 'EXCELLENCE10',
            'type' => 'percentage',
            'percentage' => 10.00,
            'amount' => null,
            'description' => 'خصم 10% للطلاب المتفوقين',
            'is_active' => 'yes',
        ]);

        foreach ($studentSessions as $studentSession) {
            StudentFeesDiscount::create([
                'student_session_id' => $studentSession->id,
                'fees_discount_id' => $feeDiscount->id,
                'status' => 'assigned',
                'payment_id' => null,
                'description' => 'خصم تفوق',
                'is_active' => 'yes',
            ]);
        }
    }
}
