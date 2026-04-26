<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_pass' => 'required|string',
            'new_pass' => 'required|string|min:6|confirmed',
            'new_pass_confirmation' => 'required_with:new_pass|same:new_pass',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_pass.required' => 'Current password is required',
            'new_pass.required' => 'New password is required',
            'new_pass.min' => 'New password must be at least 6 characters',
            'new_pass.confirmed' => 'New password confirmation does not match',
            'new_pass_confirmation.required_with' => 'Password confirmation is required',
            'new_pass_confirmation.same' => 'Password confirmation does not match new password',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}