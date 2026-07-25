<?php

namespace App\Http\Requests\Release;

use App\Models\Release;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('release'));
    }

    public function rules(): array
    {
        $releaseId = $this->route('release')->id;

        return [
            'version' => ['sometimes', 'string', 'max:20', 'regex:/^\d+\.\d+\.\d+(-[a-z0-9]+)?$/', Rule::unique('releases')->ignore($releaseId)],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'released_at' => ['sometimes', 'date', 'before_or_equal:today'],
            'is_current' => ['boolean'],
        ];
    }
}