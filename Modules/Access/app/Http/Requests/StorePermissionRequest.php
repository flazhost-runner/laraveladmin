<?php

namespace Modules\Access\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'method' => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'guard_name' => ['required', 'in:web,api'],
            'status' => ['required', 'in:Active,Inactive,active,inactive'],
            'desc' => ['nullable', 'string', 'max:255'],
        ];
    }
}
