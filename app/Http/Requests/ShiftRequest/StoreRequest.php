<?php

namespace App\Http\Requests\ShiftRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на создание запроса дополнительной смены
 */
class StoreRequest extends FormRequest
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
            'shift_id' => 'required_without:date|exists:shifts,id',
            'date' => 'required_without:shift_id|date', // Можно передать дату вместо shift_id
            'duration' => 'required|integer|min:1|max:24',
            'user_id' => 'sometimes|exists:users,id', // Опционально, по умолчанию текущий пользователь
        ];
    }
}

