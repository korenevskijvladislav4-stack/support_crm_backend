<?php

namespace App\Http\Requests\ShiftRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на редактирование смены
 */
class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'duration' => 'required|integer|min:1|max:24',
        ];
    }
}

