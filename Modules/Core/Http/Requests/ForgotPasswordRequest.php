<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'user_type' => is_string($this->input('user_type')) ? trim($this->input('user_type')) : $this->input('user_type'),
        ]);
    }

    public function rules(): array
    {
        return [
            // CI parity: Site::ufpassword() validates username (email) + user[] as required.
            'email' => 'required|email|max:100',
            'user_type' => 'required|in:student,parent',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email address',
            'user_type.required' => 'User type is required',
            'user_type.in' => 'Invalid email or user type',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
