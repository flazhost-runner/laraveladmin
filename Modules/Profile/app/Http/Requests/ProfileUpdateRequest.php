<?php

namespace Modules\Profile\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Paritas form profil NodeAdmin: code/name/phone/email/timezone/status +
    // password inline (opsional) + picture sebagai FILE upload (bukan URL).
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'required', 'email', 'max:200',
                Rule::unique('users', 'email')->ignore(session('user_id')),
            ],
            'timezone' => ['nullable', 'timezone'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'picture' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password & confirm password not match.',
        ];
    }
}
