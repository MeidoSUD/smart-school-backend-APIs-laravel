<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Session;
use Modules\Finance\Entities\FeeMaster;
use Modules\Finance\Entities\FeeType;
use Modules\Finance\Entities\StudentFee;
use Modules\Finance\Entities\OfflinePayment;
use Modules\Finance\Entities\StudentFeesDeposite;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('fee_receipt_no')->exists()) {
            return;
        }

        $session = Session::first();
        if (!$session) {
            return;
        }

        $studentSessions = StudentSession::all();

        // ===== Fee Receipt No =====
        DB::table('fee_receipt_no')->insert([
            'payment' => 1000,
        ]);

        // ===== Fee Masters (legacy table) =====
        if (!FeeMaster::exists()) {
            $feeTypes = FeeType::all();
            $classes = Classe::all();

            foreach ($feeTypes as $feeType) {
                foreach ($classes as $class) {
                    FeeMaster::create([
                        'session_id' => $session->id,
                        'feetype_id' => $feeType->id,
                        'class_id' => $class->id,
                        'amount' => match ($feeType->code) {
                            'tuition_fee' => 5000.00,
                            'activity_fee' => 500.00,
                            'book_fee' => 300.00,
                            default => 1000.00,
                        },
                        'description' => 'رسوم ' . $feeType->type . ' للصف ' . $class->class,
                        'is_active' => 'yes',
                    ]);
                }
            }
        }

        // ===== Student Fees (legacy table) =====
        if (!StudentFee::exists() && $studentSessions->isNotEmpty()) {
            foreach ($studentSessions as $ss) {
                StudentFee::create([
                    'student_session_id' => $ss->id,
                    'feemaster_id' => FeeMaster::first()->id ?? null,
                    'amount' => 5800.00,
                    'amount_discount' => 0.00,
                    'amount_fine' => 0.00,
                    'description' => 'الرسوم الدراسية للفصل الدراسي',
                    'date' => now()->format('Y-m-d'),
                    'payment_mode' => 'Online',
                    'is_active' => 'yes',
                ]);
            }
        }

        // ===== Offline Payments =====
        if (!OfflinePayment::exists() && $studentSessions->isNotEmpty()) {
            foreach ($studentSessions as $ss) {
                OfflinePayment::create([
                    'student_session_id' => $ss->id,
                    'payment_date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'bank_from' => 'البنك الأهلي السعودي',
                    'bank_account_transferred' => 'SA1234567890',
                    'reference' => 'TXN-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT),
                    'amount' => 5000.00,
                    'submit_date' => now()->subDays(rand(1, 30)),
                    'approve_date' => now()->subDays(rand(1, 30)),
                    'approved_by' => 1,
                    'is_active' => '1',
                ]);
            }
        }

        // ===== Payment Settings =====
        $paymentGateways = [
            [
                'id' => 1,
                'payment_type' => 'PayPal',
                'api_username' => 'paypal_api_user',
                'api_secret_key' => 'paypal_secret',
                'salt' => '',
                'api_publishable_key' => 'paypal_publishable',
                'paypal_demo' => 'TRUE',
                'account_no' => 'paypal@school.com',
                'is_active' => 'no',
                'gateway_mode' => 0,
                'paytm_website' => '',
                'paytm_industrytype' => '',
            ],
            [
                'id' => 2,
                'payment_type' => 'Stripe',
                'api_username' => '',
                'api_secret_key' => 'stripe_secret',
                'salt' => '',
                'api_publishable_key' => 'stripe_publishable',
                'paypal_demo' => 'FALSE',
                'account_no' => 'stripe@school.com',
                'is_active' => 'no',
                'gateway_mode' => 0,
                'paytm_website' => '',
                'paytm_industrytype' => '',
            ],
        ];

        foreach ($paymentGateways as $gateway) {
            DB::table('payment_settings')->insert($gateway);
        }

        // ===== Gateway Ins =====
        $gatewayInsId = DB::table('gateway_ins')->insertGetId([
            'gateway_name' => 'Stripe',
            'module_type' => 'fees',
            'unique_id' => 'GWI-' . uniqid(),
            'parameter_details' => json_encode(['amount' => 1000]),
            'payment_status' => 'success',
        ]);

        // ===== Student Fees Processing =====
        $feeDeposites = StudentFeesDeposite::all();
        if ($feeDeposites->isNotEmpty()) {
            foreach ($feeDeposites as $deposite) {
                DB::table('student_fees_processing')->insert([
                    'gateway_ins_id' => $gatewayInsId,
                    'fee_category' => 'tuition',
                    'student_fees_master_id' => $deposite->student_fees_master_id,
                    'fee_groups_feetype_id' => $deposite->fee_groups_feetype_id,
                    'amount_detail' => $deposite->amount_detail,
                    'is_active' => 'yes',
                ]);
            }
        }
    }
}
