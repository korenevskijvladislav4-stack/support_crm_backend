<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TransferGroupRequest extends FormRequest
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
            'new_group_id' => 'required|integer|exists:groups,id',
            'transfer_date' => 'required|date|after_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_group_id.required' => 'Необходимо указать новую группу',
            'new_group_id.exists' => 'Указанная группа не существует',
            'transfer_date.required' => 'Необходимо указать дату перевода',
            'transfer_date.date' => 'Дата перевода должна быть валидной датой',
            'transfer_date.after_or_equal' => 'Дата перевода не может быть в прошлом',
        ];
    }
}

