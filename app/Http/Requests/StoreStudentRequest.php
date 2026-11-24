<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'admission_number' => [
                'required', 'string', 'max:50',
                Rule::unique('students', 'admission_number')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('students', 'email')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'department' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:50'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'graduated', 'suspended'])],
        ];
    }
}
