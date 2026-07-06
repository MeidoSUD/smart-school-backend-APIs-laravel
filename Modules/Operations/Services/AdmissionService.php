<?php

namespace Modules\Operations\Services;

use Modules\Operations\Entities\OnlineStudent;
use Modules\Core\Entities\Setting;
use Modules\Core\Entities\Classe;
use Modules\Core\Entities\Section;
use Modules\Core\Entities\Category;
use Modules\Core\Entities\BloodGroup;
use Modules\Core\Entities\House;
use Modules\Core\Entities\CustomField;
use Modules\Academic\Entities\ClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdmissionService
{
    public function isEnabled(): bool
    {
        return (bool) (Setting::value('online_admission') ?? false);
    }

    public function getFormConfig(): array
    {
        $classlist = Classe::where('is_active', 'yes')->get();
        $category = Category::where('is_active', 'yes')->get();
        $bloodgroup = BloodGroup::where('is_active', 'yes')->get();
        $houses = House::where('is_active', 'yes')->get();
        $custom_fields = CustomField::where('belong_to', 'students')
            ->where('is_active', 'yes')
            ->get();

        return [
            'gender_list' => ['Male', 'Female', 'Other'],
            'class_list' => $classlist,
            'category_list' => $category,
            'blood_group_list' => $bloodgroup,
            'house_list' => $houses,
            'custom_fields' => $custom_fields,
        ];
    }

    public function getActiveClasses()
    {
        return Classe::where('is_active', 'yes')->get();
    }

    public function getSectionsForClass(int $classId)
    {
        return Section::whereHas('classSections', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })->where('is_active', 'yes')->get();
    }

    public function submitAdmission(Request $request): OnlineStudent
    {
        $classSection = ClassSection::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->firstOrFail();

        return DB::transaction(function () use ($request, $classSection) {
            $data = [
                'firstname' => $request->firstname,
                'class_section_id' => $classSection->id,
                'dob' => Carbon::parse($request->dob)->format('Y-m-d'),
                'gender' => $request->gender,
            ];

            $optionalFields = [
                'middlename', 'lastname', 'category', 'religion', 'cast',
                'mobileno', 'email', 'current_address', 'permanent_address',
                'bank_account_no', 'bank_name', 'ifsc_code', 'adhar_no',
                'samagra_id', 'rte', 'note',
            ];

            foreach ($optionalFields as $field) {
                if ($request->filled($field)) {
                    $data[$field] = $request->$field;
                }
            }

            if ($request->filled('guardian_is')) {
                foreach (['guardian_is', 'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_occupation', 'guardian_email', 'guardian_address'] as $field) {
                    if ($request->has($field)) {
                        $data[$field] = $request->$field;
                    }
                }
            }

            foreach (['father_name', 'father_phone', 'father_occupation'] as $field) {
                if ($request->filled($field)) {
                    $data[$field] = $request->$field;
                }
            }

            foreach (['mother_name', 'mother_phone', 'mother_occupation'] as $field) {
                if ($request->filled($field)) {
                    $data[$field] = $request->$field;
                }
            }

            if ($request->has('school_house_id')) {
                $data['school_house_id'] = $request->school_house_id;
            }

            if ($request->has('blood_group')) {
                $data['blood_group'] = $request->blood_group;
            }

            $data['reference_no'] = $this->generateUniqueReferenceNo();

            return OnlineStudent::create($data);
        });
    }

    public function getAdmissionStatus(string $referenceNo): ?OnlineStudent
    {
        return OnlineStudent::where('reference_no', $referenceNo)->first();
    }

    private function generateUniqueReferenceNo(): int
    {
        do {
            $referenceNo = mt_rand(100000, 999999);
        } while (OnlineStudent::where('reference_no', $referenceNo)->exists());

        return $referenceNo;
    }
}
