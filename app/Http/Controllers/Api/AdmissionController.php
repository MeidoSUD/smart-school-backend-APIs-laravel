<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineStudent;
use App\Models\Setting;
use App\Models\Classe;
use App\Models\Section;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\StudentSession;
use App\Models\Category;
use App\Models\House;
use App\Models\BloodGroup;
use App\Models\CustomField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Converted from CodeIgniter: codelgiterControllers/Admission.php
 */
class AdmissionController extends Controller
{
    public function index(): JsonResponse
    {
        $setting = Setting::first();
        
        $data = [
            'enabled' => (bool) ($setting->online_admission ?? false),
            'instructions' => $setting->online_admission_instruction ?? '',
            'conditions' => $setting->online_admission_conditions ?? '',
            'amount' => $setting->online_admission_amount ?? 0,
            'payment_enabled' => ($setting->online_admission_payment ?? 'no') === 'yes',
        ];
        
        return $this->successResponse($data);
    }

    public function form_config(): JsonResponse
    {
        $classlist = Classe::where('is_active', 'yes')->get();
        $category = Category::where('is_active', 'yes')->get();
        $bloodgroup = BloodGroup::where('is_active', 'yes')->get();
        $houses = House::where('is_active', 'yes')->get();
        $custom_fields = CustomField::where('belong_to', 'students')->where('is_active', 1)->get();
        
        $genderList = ['Male', 'Female', 'Other'];
        
        $data = [
            'gender_list' => $genderList,
            'class_list' => $classlist,
            'category_list' => $category,
            'blood_group_list' => $bloodgroup,
            'house_list' => $houses,
            'custom_fields' => $custom_fields,
        ];
        
        return $this->successResponse($data);
    }

    public function classes(): JsonResponse
    {
        $classlist = Classe::where('is_active', 'yes')->get();
        return $this->successResponse($classlist);
    }

    public function sections(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        
        if (!$classId) {
            return $this->errorResponse('class_id is required');
        }
        
        $sections = Section::whereHas('classSections', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })->where('is_active', 'yes')->get();
        
        return $this->successResponse($sections);
    }

    public function submit(Request $request): JsonResponse
    {
        $setting = Setting::first();
        
        if (!($setting->online_admission ?? false)) {
            return $this->errorResponse('Online admission is currently disabled');
        }
        
        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'dob' => 'required',
            'class_id' => 'required',
            'section_id' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'email' => 'nullable|email',
            'guardian_is' => 'nullable|string',
            'guardian_name' => 'nullable|string',
            'guardian_relation' => 'nullable|string',
        ]);
        
        // Find class_section_id from class_id and section_id
        $classSection = ClassSection::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->first();
        
        if (!$classSection) {
            return $this->errorResponse('Invalid class or section');
        }
        
        $data = [
            'firstname' => $request->firstname,
            'class_section_id' => $classSection->id,
            'dob' => Carbon::parse($request->dob)->format('Y-m-d'),
            'gender' => $request->gender,
        ];
        
        // Optional fields
        $optionalFields = [
            'middlename', 'lastname', 'category', 'religion', 'cast',
            'mobileno', 'email', 'current_address', 'permanent_address',
            'bank_account_no', 'bank_name', 'ifsc_code', 'adhar_no',
            'samagra_id', 'rte', 'note'
        ];
        
        foreach ($optionalFields as $field) {
            if ($request->has($field) && $request->$field) {
                $data[$field] = $request->$field;
            }
        }
        
        // Guardian fields
        if ($request->has('guardian_is') && $request->guardian_is) {
            $guardianFields = ['guardian_is', 'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_occupation', 'guardian_email', 'guardian_address'];
            foreach ($guardianFields as $field) {
                if ($request->has($field)) {
                    $data[$field] = $request->$field;
                }
            }
        }
        
        // Father fields
        $fatherFields = ['father_name', 'father_phone', 'father_occupation'];
        foreach ($fatherFields as $field) {
            if ($request->has($field) && $request->$field) {
                $data[$field] = $request->$field;
            }
        }
        
        // Mother fields
        $motherFields = ['mother_name', 'mother_phone', 'mother_occupation'];
        foreach ($motherFields as $field) {
            if ($request->has($field) && $request->$field) {
                $data[$field] = $request->$field;
            }
        }
        
        if ($request->has('school_house_id')) {
            $data['school_house_id'] = $request->school_house_id;
        }
        
        if ($request->has('blood_group')) {
            $data['blood_group'] = $request->blood_group;
        }
        
        // Generate reference number
        do {
            $reference_no = mt_rand(100000, 999999);
            $exists = OnlineStudent::where('reference_no', $reference_no)->exists();
        } while ($exists);
        
        $data['reference_no'] = $reference_no;
        
        $onlineStudent = OnlineStudent::create($data);
        
        $response = [
            'admission_id' => $onlineStudent->id,
            'reference_no' => $reference_no,
            'message' => 'Registration successful. Please note your reference number for further communication.',
        ];
        
        return $this->successResponse($response, 'Admission form submitted successfully');
    }

    public function status(Request $request): JsonResponse
    {
        $referenceNo = $request->get('reference_no');
        
        if (!$referenceNo) {
            return $this->errorResponse('reference_no is required');
        }
        
        $admission = OnlineStudent::where('reference_no', $referenceNo)->first();
        
        if (!$admission) {
            return $this->errorResponse('No admission found with this reference number', null, 404);
        }
        
        $data = [
            'reference_no' => $admission->reference_no,
            'firstname' => $admission->firstname,
            'lastname' => $admission->lastname,
            'form_status' => $admission->form_status,
            'paid_status' => $admission->paid_status,
            'submitted_date' => $admission->created_at,
        ];
        
        return $this->successResponse($data);
    }
}