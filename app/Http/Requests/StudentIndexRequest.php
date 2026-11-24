<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:100'],
            'department' => ['sometimes', 'string', 'max:100'],
            'level' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'graduated', 'suspended'])],
        ];
    }
}
