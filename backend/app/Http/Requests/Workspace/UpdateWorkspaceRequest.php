<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workspaceId = $this->route('workspace')->id ?? null;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', Rule::unique('workspaces', 'slug')->ignore($workspaceId)],
            'timezone' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}