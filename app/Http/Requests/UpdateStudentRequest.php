<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $studentId = $this->route('student');

        return [
            'admission_number' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('students', 'admission_number')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->ignore($studentId),
            ],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => [
                'sometimes', 'nullable', 'email', 'max:255',
                Rule::unique('students', 'email')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->ignore($studentId),
            ],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'gender' => ['sometimes', 'required', Rule::in(['male', 'female', 'other'])],
            'department' => ['sometimes', 'required', 'string', 'max:100'],
            'level' => ['sometimes', 'required', 'string', 'max:50'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'graduated', 'suspended'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $fields = [
                'admission_number', 'first_name', 'last_name', 'email',
                'date_of_birth', 'gender', 'department', 'level', 'status',
            ];

            if (! $this->hasAny($fields)) {
                $validator->errors()->add('student', 'Provide at least one field to update.');
            }
        });
    }
}
