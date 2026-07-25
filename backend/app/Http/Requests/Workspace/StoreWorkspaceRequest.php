<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:workspaces,slug'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'string'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'timezone' => $this->input('timezone') ?? 'UTC',
        ]);
    }
}