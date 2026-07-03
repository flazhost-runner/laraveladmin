<?php

namespace Modules\Access\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:Active,Inactive,active,inactive'],
            'desc' => ['nullable', 'string', 'max:255'],
        ];
    }
}
