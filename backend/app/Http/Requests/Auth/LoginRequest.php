<?php

namespace App\Http\Requests\Auth;

use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    private ?Person $authenticatedPerson = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Authenticate credentials and return Person
     */
    public function authenticate(): ?Person
    {
        if ($this->authenticatedPerson) {
            return $this->authenticatedPerson;
        }

        $person = Person::where('email', $this->input('email'))->first();

        if (!$person || !Hash::check($this->input('password'), $person->password)) {
            return null;
        }

        $this->authenticatedPerson = $person;
        return $person;
    }
}
