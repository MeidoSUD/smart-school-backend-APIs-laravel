<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username is required',
            'username.max' => 'Username must not exceed 50 characters',
            'password.required' => 'Password is required',
            'password.max' => 'Password must not exceed 50 characters',
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
