<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Enums\Gender;

class AdmissionSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:100',
            'middlename' => 'nullable|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'dob' => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'gender' => ['required', 'in:' . implode(',', Gender::values())],
            'email' => 'nullable|email|max:100',
            'mobileno' => 'nullable|string|max:20',
            'guardian_is' => 'nullable|string|max:50',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:100',
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_address' => 'nullable|string|max:500',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'father_occupation' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'mother_occupation' => 'nullable|string|max:255',
            'school_house_id' => 'nullable|integer',
            'blood_group' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'cast' => 'nullable|string|max:100',
            'current_address' => 'nullable|string|max:500',
            'permanent_address' => 'nullable|string|max:500',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'adhar_no' => 'nullable|string|max:20',
            'samagra_id' => 'nullable|string|max:50',
            'rte' => 'nullable|string|max:10',
            'note' => 'nullable|string|max:1000',
        ];
    }
}
