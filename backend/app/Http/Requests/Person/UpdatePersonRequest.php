<?php

namespace App\Http\Requests\Person;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'position_id' => ['nullable', 'uuid', 'exists:positions,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:people,email,' . $this->route('id')],
            'role' => ['nullable', 'in:admin,manager,member,guest'],
            'status' => ['nullable', 'in:active,inactive,on_leave'],
            'avatar' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['uuid', 'exists:skills,id'],
        ];
    }
}