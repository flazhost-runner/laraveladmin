<?php

namespace Modules\Access\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['sometimes'],
            'phone' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:Active,Inactive'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'picture' => ['nullable', 'string', 'max:500'],
            'blocked' => ['nullable', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:500'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ];
    }
}
