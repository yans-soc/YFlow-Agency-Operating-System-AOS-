<?php

namespace App\Http\Requests\Release;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Release::class);
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:20', 'regex:/^\d+\.\d+\.\d+(-[a-z0-9]+)?$/', 'unique:releases,version'],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'released_at' => ['required', 'date', 'before_or_equal:today'],
            'is_current' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'version.regex' => 'Version must follow semantic versioning (e.g., 1.0.1 or 1.0.1-beta).',
        ];
    }
}