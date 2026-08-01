<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\LibraryMember;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('books')->exists()) {
            return;
        }

        $books = [
            [
                'book_title' => 'مبادئ الرياضيات',
                'book_no' => 'B001',
                'isbn_no' => '978-603-01-0001-5',
                'subject' => 'الرياضيات',
                'rack_no' => 'R1-A',
                'publish' => 'دار النشر السعودية',
                'author' => 'د. سعود الأحمد',
                'qty' => 10,
                'perunitcost' => 45.00,
                'postdate' => '2024-01-15',
                'description' => 'كتاب مبادئ الرياضيات للمرحلة المتوسطة',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'العلوم الطبيعية',
                'book_no' => 'B002',
                'isbn_no' => '978-603-01-0002-2',
                'subject' => 'العلوم',
                'rack_no' => 'R1-B',
                'publish' => 'مكتبة العلوم',
                'author' => 'أ. سارة القحطاني',
                'qty' => 8,
                'perunitcost' => 52.00,
                'postdate' => '2024-02-01',
                'description' => 'كتاب العلوم الطبيعية للصف السابع',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'قواعد اللغة العربية',
                'book_no' => 'B003',
                'isbn_no' => '978-603-01-0003-9',
                'subject' => 'اللغة العربية',
                'rack_no' => 'R2-A',
                'publish' => 'دار الفصحى',
                'author' => 'د. محمد العنزي',
                'qty' => 15,
                'perunitcost' => 38.00,
                'postdate' => '2024-01-20',
                'description' => 'قواعد اللغة العربية للمبتدئين',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'English Grammar',
                'book_no' => 'B004',
                'isbn_no' => '978-603-01-0004-6',
                'subject' => 'اللغة الإنجليزية',
                'rack_no' => 'R2-B',
                'publish' => 'Oxford University Press',
                'author' => 'John Smith',
                'qty' => 12,
                'perunitcost' => 65.00,
                'postdate' => '2024-03-01',
                'description' => 'English grammar for intermediate level',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'الفقه الإسلامي',
                'book_no' => 'B005',
                'isbn_no' => '978-603-01-0005-3',
                'subject' => 'الدراسات الإسلامية',
                'rack_no' => 'R3-A',
                'publish' => 'مكتبة الإيمان',
                'author' => 'الشيخ عبد الله التميمي',
                'qty' => 10,
                'perunitcost' => 42.00,
                'postdate' => '2024-01-10',
                'description' => 'الفقه الإسلامي للمرحلة المتوسطة',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'أساسيات الحاسب الآلي',
                'book_no' => 'B006',
                'isbn_no' => '978-603-01-0006-0',
                'subject' => 'الحاسب الآلي',
                'rack_no' => 'R3-B',
                'publish' => 'مكتبة التقنية',
                'author' => 'م. ناصر الدوسري',
                'qty' => 7,
                'perunitcost' => 58.00,
                'postdate' => '2024-02-15',
                'description' => 'مقدمة في علوم الحاسب وتقنية المعلومات',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'تاريخ المملكة',
                'book_no' => 'B007',
                'isbn_no' => '978-603-01-0007-7',
                'subject' => 'الدراسات الاجتماعية',
                'rack_no' => 'R4-A',
                'publish' => 'دار الوطن',
                'author' => 'د. فهد المالكي',
                'qty' => 9,
                'perunitcost' => 35.00,
                'postdate' => '2024-01-25',
                'description' => 'تاريخ المملكة العربية السعودية',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'التربية البدنية والصحة',
                'book_no' => 'B008',
                'isbn_no' => '978-603-01-0008-4',
                'subject' => 'التربية البدنية',
                'rack_no' => 'R4-B',
                'publish' => 'مكتبة الرياضة',
                'author' => 'كابتن فيصل الشهراني',
                'qty' => 10,
                'perunitcost' => 30.00,
                'postdate' => '2024-02-20',
                'description' => 'دليل التربية البدنية والصحة',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'قاموس عربي إنجليزي',
                'book_no' => 'B009',
                'isbn_no' => '978-603-01-0009-1',
                'subject' => 'قواميس',
                'rack_no' => 'R5-A',
                'publish' => 'دار المورد',
                'author' => 'منير البعلبكي',
                'qty' => 5,
                'perunitcost' => 85.00,
                'postdate' => '2024-03-15',
                'description' => 'قاموس عربي إنجليزي شامل',
                'available' => 'yes',
                'is_active' => 'yes',
            ],
            [
                'book_title' => 'موسوعة العلوم',
                'book_no' => 'B010',
                'isbn_no' => '978-603-01-0010-7',
                'subject' => 'العلوم',
                'rack_no' => 'R1-B',
                'publish' => 'مكتبة المعرفة',
                'author' => 'د. أحمد البسام',
                'qty' => 3,
                'perunitcost' => 120.00,
                'postdate' => '2024-04-01',
                'description' => 'موسوعة العلوم المصورة',
                'available' => 'no',
                'is_active' => 'yes',
            ],
        ];

        DB::table('books')->insert($books);

        // Create library member for the student
        $student = Student::first();

        if ($student && !LibraryMember::where('member_id', $student->id)->exists()) {
            LibraryMember::create([
                'library_card_no' => 'LIB-CARD-001',
                'member_type' => 'student',
                'member_id' => $student->id,
                'is_active' => 'yes',
            ]);
        }
    }
}
