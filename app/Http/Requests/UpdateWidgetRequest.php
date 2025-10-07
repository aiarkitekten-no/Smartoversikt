<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWidgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'sometimes|nullable|string|max:500',
            'category' => 'sometimes|string|max:50',
            'default_refresh_interval' => 'sometimes|integer|min:60|max:86400',
            'default_settings' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'navn',
            'description' => 'beskrivelse',
            'category' => 'kategori',
            'default_refresh_interval' => 'oppdateringsinterval',
            'default_settings' => 'innstillinger',
            'is_active' => 'aktiv status',
        ];
    }
}
