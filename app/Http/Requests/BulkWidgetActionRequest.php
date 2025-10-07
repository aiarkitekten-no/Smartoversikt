<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkWidgetActionRequest extends FormRequest
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
            'widget_ids' => 'required|array|min:1',
            'widget_ids.*' => 'required|integer|exists:widgets,id',
            'action' => ['required', 'string', Rule::in(['enable', 'disable', 'refresh'])],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'widget_ids' => 'widget ID-er',
            'action' => 'handling',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'widget_ids.required' => 'Du må velge minst én widget.',
            'widget_ids.*.exists' => 'En eller flere valgte widgets finnes ikke.',
            'action.in' => 'Ugyldig handling. Må være enable, disable eller refresh.',
        ];
    }
}
