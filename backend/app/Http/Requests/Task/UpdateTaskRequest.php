<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['sometimes', 'string', 'exists:workflow_stages,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['string', 'exists:people,id'],
        ];
    }
}