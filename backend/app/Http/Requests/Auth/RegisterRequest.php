<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'workspace_id' => ['nullable', 'string', 'exists:workspaces,id'],
            'workspace_name' => ['required_without:workspace_id', 'string', 'min:2', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
            'workspace_name.required_without' => 'Workspace name is required when creating a new workspace.',
        ];
    }

    /**
     * Validate email uniqueness within workspace context
     */
    protected function passedValidation(): void
    {
        $workspaceId = $this->input('workspace_id');
        $email = $this->input('email');

        if ($workspaceId) {
            $exists = \App\Models\Person::where('email', $email)
                ->where('workspace_id', $workspaceId)
                ->exists();
        } else {
            $exists = \App\Models\Person::where('email', $email)->exists();
        }

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered.',
            ]);
        }
    }
}
