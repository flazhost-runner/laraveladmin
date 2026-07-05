<?php

namespace Modules\Setting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'initial' => ['nullable', 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'file', 'image', 'max:2048'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
            'login_image' => ['nullable', 'file', 'image', 'max:4096'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
            'copyright' => ['nullable', 'string', 'max:200'],
            'theme' => ['nullable', 'string', 'max:50'],
            // 'default' (local landing v6) or an opentailwind slug (anti-SSRF).
            'fe_template' => ['nullable', 'string', 'max:200', 'regex:/^(default|([a-z]+(?:-[a-z]+)*)-(\d{3})-([a-z0-9-]+))$/'],
        ];
    }
}
